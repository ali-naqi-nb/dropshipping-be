<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(name: 'app:tests:run', description: 'Run tests.', hidden: false)]
final class RunTestsCommand extends Command
{
    private const DEFAULT_THRESHOLD = 95;

    private const SECTION_SEPARATOR = '--------------------';

    private const RUN_TEST_COMMAND_MESSAGE = 'Run tests command finished in %d seconds.';
    private const PHPUNIT_TIMEOUT_SECONDS = 120;

    private SymfonyStyle $output;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Connection $connection,
        private readonly string $codeCoverageXmlReportDir,
        private readonly string $appServiceName,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter tests');
        $this->addOption('testsuite', null, InputOption::VALUE_OPTIONAL, 'Run tests only in provided testsuite');
        $this->addOption('skipDbSetup', null, InputOption::VALUE_NONE, 'Skip database config');
        $this->addOption('testsuite', null, InputOption::VALUE_OPTIONAL, 'Run tests only in provided testsuite');
        $this->addOption('threshold', null, InputOption::VALUE_OPTIONAL, 'Minimum test coverage percentage');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = new SymfonyStyle($input, $output);
        $startedAt = microtime(true);
        $this->output->writeln(['Run tests command started.', self::SECTION_SEPARATOR]);

        if (!(bool) $input->getOption('skipDbSetup')) {
            if (Command::SUCCESS !== $this->runMainMigrations()) {
                $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

                return Command::FAILURE;
            }

            if (Command::SUCCESS !== $this->loadMainFixtures()) {
                $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

                return Command::FAILURE;
            }

            if (Command::SUCCESS !== $this->setupTenantUsersAndDatabases()) {
                $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

                return Command::FAILURE;
            }

            if (Command::SUCCESS !== $this->runTenantMigrations()) {
                $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

                return Command::FAILURE;
            }

            if (Command::SUCCESS !== $this->loadTenantFixtures()) {
                $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

                return Command::FAILURE;
            }
        }

        $filter = (string) $input->getOption('filter');
        $testsuite = (string) $input->getOption('testsuite');
        if (Command::SUCCESS !== $this->runTests($filter, $testsuite)) {
            $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

            return Command::FAILURE;
        }

        $threshold = $input->getOption('threshold');
        $threshold = null !== $threshold ? (int) $threshold : self::DEFAULT_THRESHOLD;
        if (Command::SUCCESS !== $this->checkTestCoverageThreshold($threshold)) {
            $this->output->error(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

            return Command::FAILURE;
        }

        $this->output->success(sprintf(self::RUN_TEST_COMMAND_MESSAGE, (int) (microtime(true) - $startedAt)));

        return Command::SUCCESS;
    }

    private function runMainMigrations(): int
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--configuration' => 'config/packages/migrations/main.yaml',
            '--env' => 'test',
            '--no-interaction' => true,
            '--allow-no-migration' => true,
        ]);

        $applicationOutput = new BufferedOutput();
        $statusCode = $application->run($input, $applicationOutput);
        $this->output->writeln([
            'Run migrations in main db',
            self::SECTION_SEPARATOR,
            $applicationOutput->fetch(),
            self::SECTION_SEPARATOR,
        ]);

        return $statusCode;
    }

    private function loadMainFixtures(): int
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--env' => 'test',
            '--group' => ['test-main'],
            '--no-interaction' => true,
        ]);

        $applicationOutput = new BufferedOutput();
        $statusCode = $application->run($input, $applicationOutput);
        $this->output->writeln([
            'Load fixtures in main db',
            self::SECTION_SEPARATOR,
            $applicationOutput->fetch(),
            self::SECTION_SEPARATOR,
        ]);

        return $statusCode;
    }

    private function setupTenantUsersAndDatabases(): int
    {
        $this->output->writeln(['Create tenant users and databases', self::SECTION_SEPARATOR]);

        try {
            $schemaManager = $this->connection->createSchemaManager();
            $availableDatabases = $schemaManager->listDatabases();
            foreach ($this->getTenants() as $tenant) {
                $dbConfig = $tenant['dbConfig'];
                if (in_array($dbConfig['db'], $availableDatabases, true)) {
                    $schemaManager->dropDatabase($dbConfig['db']);
                }
                $schemaManager->createDatabase($dbConfig['db']);

                $this->deleteUser($dbConfig['user']);
                $this->createUser($dbConfig['user'], $dbConfig['password'], $dbConfig['db']);
                $this->output->writeln([
                    sprintf('DB and user for tenant %s was successfully created', $tenant['id']),
                ]);
            }
        } catch (Exception $e) {
            $this->output->writeln(['Error', $e->getMessage(), $e->getTraceAsString()]);

            return Command::FAILURE;
        }

        $this->output->writeln(self::SECTION_SEPARATOR);

        return Command::SUCCESS;
    }

    private function runTenantMigrations(): int
    {
        $this->output->writeln(['Run migrations in tenant databases', self::SECTION_SEPARATOR]);

        foreach ($this->getTenants() as $tenant) {
            if ($tenant['createOnlyDb']) {
                continue;
            }

            $command = [
                'bin/console',
                'doctrine:migrations:migrate',
                '--configuration=config/packages/migrations/tenant.yaml',
                '--env=test',
                '--tenant='.$tenant['id'],
                '--no-interaction',
                '--allow-no-migration',
            ];
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->output->writeln([
                    sprintf('Migrations for tenant %s were failed.', $tenant['id']),
                    $process->getErrorOutput(),
                ]);

                return Command::FAILURE;
            }

            $this->output->writeln([
                sprintf('Migrations for tenant %s were successfully executed.', $tenant['id']),
                $process->getOutput(),
            ]);
        }

        $this->output->writeln(self::SECTION_SEPARATOR);

        return Command::SUCCESS;
    }

    private function loadTenantFixtures(): int
    {
        $this->output->writeln(['Load fixtures in tenants databases', self::SECTION_SEPARATOR]);

        foreach ($this->getTenants() as $tenant) {
            if ($tenant['createOnlyDb']) {
                continue;
            }

            $command = [
                'bin/console',
                'doctrine:fixtures:load',
                '--env=test',
                '--tenant='.$tenant['id'],
                '--group=test-tenant',
                '--no-interaction',
            ];

            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->output->writeln([
                    sprintf('Fixtures for tenant %s were failed.', $tenant['id']),
                    $process->getErrorOutput(),
                ]);

                return Command::FAILURE;
            }

            $this->output->writeln([
                sprintf('Fixtures for tenant %s were successfully loaded.', $tenant['id']),
                $process->getOutput(),
            ]);
        }

        $this->output->writeln(self::SECTION_SEPARATOR);

        return Command::SUCCESS;
    }

    private function runTests(string $filter, string $testsuite): int
    {
        $this->output->writeln(['Run tests', self::SECTION_SEPARATOR]);

        $command = ['bin/phpunit'];
        if ('' !== $filter) {
            $command[] = '--filter='.$filter;
        }
        if ('' !== $testsuite) {
            $command[] = '--testsuite='.$testsuite;
        }

        $process = new Process($command, timeout: self::PHPUNIT_TIMEOUT_SECONDS);
        $process->run(fn ($type, $data) => print($data));

        if (!$process->isSuccessful()) {
            $this->output->error($process->getOutput());
            $this->output->writeln(self::SECTION_SEPARATOR);

            return Command::FAILURE;
        }

        $this->output->success($process->getOutput());
        $this->output->writeln(self::SECTION_SEPARATOR);

        return Command::SUCCESS;
    }

    private function checkTestCoverageThreshold(int $threshold): int
    {
        /** @var SimpleXMLElement $coverage */
        $coverage = simplexml_load_string((string) file_get_contents($this->codeCoverageXmlReportDir.'/index.xml'));
        $linesCoveredByTests = (float) $coverage->project->directory->totals->lines['percent'];

        $status = $linesCoveredByTests >= $threshold ? Command::SUCCESS : Command::FAILURE;

        $outputMethod = Command::SUCCESS === $status ? 'success' : 'error';
        $this->output->$outputMethod(
            sprintf('Line coverage: %.2f%% (Threshold: %.2f%%)', $linesCoveredByTests, $threshold)
        );
        $this->output->writeln(self::SECTION_SEPARATOR);

        return $status;
    }

    private function deleteUser(string $user): void
    {
        $this->connection
            ->prepare('DROP USER IF EXISTS :user@"%"')
            ->executeStatement(['user' => $user]);
    }

    private function createUser(string $user, string $password, string $database): void
    {
        $this->connection
            ->prepare('CREATE USER :user@"%" IDENTIFIED BY :password')
            ->executeStatement(['user' => $user, 'password' => $password]);

        // Can't bind object names in prepared statements
        $this->connection->prepare(
            'GRANT CREATE, ALTER, DROP, INDEX, REFERENCES, SELECT, INSERT, UPDATE, DELETE ON `'.$database.'`.* to :user@"%"'
        )
            ->executeStatement(['user' => $user]);
    }

    private function getTenants(): array
    {
        return [
            [
                'id' => 'ad4f3865-5061-4b45-906c-562d37ac0830',
                'createOnlyDb' => false,
                'dbConfig' => [
                    'user' => sprintf('%s165307955289cc1129', $this->appServiceName),
                    'password' => '691b2af4810d48af98e554dda0964d08',
                    'db' => sprintf('%s_ad4f3865_5061_4b45_906c_562d37ac0830', $this->appServiceName),
                ],
            ],
            [
                'id' => 'ad4f3865-5061-4b45-906c-562d37ac0831',
                'createOnlyDb' => true,
                'dbConfig' => [
                    'user' => sprintf('%s165307955289cd1228', $this->appServiceName),
                    'password' => '691b2af4810d48af98e554dda0164d05',
                    'db' => sprintf('%s_9d9383f3b2eb46a9b336166cbb4fb000', $this->appServiceName),
                ],
            ],
        ];
    }
}
