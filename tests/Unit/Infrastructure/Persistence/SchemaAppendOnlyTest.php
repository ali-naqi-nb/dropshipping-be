<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence;

use App\Tests\Unit\UnitTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\MigrationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

final class SchemaAppendOnlyTest extends UnitTestCase
{
    private const BASE_DIRECTORY = __DIR__.'/../../../../';
    private const CONFIG_DIRECTORY = 'config/packages/migrations';
    private const PATTERN_DIRECTORY = '*.yaml';
    private const TEST_MIGRATIONS_SINCE = '-1 month';
    private const SQL_CHANGED_FIELD_REGEX = '/\s+CHANGE\s+(COLUMN\s+)?`?(\w+)`?\s+`?(\w+)`?\s+/i';
    private const SQL_ALTER_TABLE_REGEX = '/(ALTER|CREATE)\s+TABLE\s+`?(\w+)`?/i';
    private const SQL_DROPPED_FIELD_REGEX = '/\s+DROP\s+(?!FOREIGN\s+KEY\s+)(?!INDEX\s+)(COLUMN\s+)?`?(\w+)`?+/i';
    private const SQL_ADDED_FIELD_REGEX = '/\s+ADD\s+(?!CONSTRAINT\s+)(COLUMN\s+)?`?(\w+)`?+/i';
    private const SQL_CREATED_FIELD_REGEX = '/([(,])\s*`?(\w+)`?\s+(BINARY|VARCHAR|DATETIME|INT|INTEGER|TINYINT|SMALLINT|MEDIUMINT|BIGINT|DECIMAL|NUMERIC|FLOAT|DOUBLE|DATE|TIMESTAMP|TIME|YEAR|CHAR|TEXT|TINYTEXT|MEDIUMTEXT|LONGTEXT|ENUM|SET|JSON)/i';

    private LoggerInterface $logger;
    private Connection $connection;
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->schema = $this->createMock(Schema::class);
    }

    /**
     * @throws MigrationException
     * @throws Exception
     */
    public function testMigrationsDoNotChangeFieldNames(): void
    {
        $migrationPaths = $this->getMigrationConfigs();
        $this->assertNotEmpty($migrationPaths, 'The migration configs should be defined.');
        foreach ($migrationPaths as $namespace => $path) {
            $migrationsFinder = new Finder();
            $migrationsFinder->files()
                ->in($path)->name('*.php')
                ->sort(function (SplFileInfo $a, SplFileInfo $b) {
                    return strcmp($a->getFilename(), $b->getFilename());
                });
            foreach ($migrationsFinder as $migrationFile) {
                $statements = $this->getMigrationStatements($migrationFile, $namespace);
                foreach ($statements as $statement) {
                    $this->assertTrue(
                        $this->isFieldNameChanged($statement),
                        sprintf(
                            'SQL statement changed the field name in %s: %s',
                            $migrationFile->getFilenameWithoutExtension(),
                            $statement
                        )
                    );
                }
            }
        }
    }

    public function testMigrationsDoNotDeleteFieldsTooEarly(): void
    {
        $migrationPaths = $this->getMigrationConfigs();
        $this->assertNotEmpty($migrationPaths, 'The migration configs should be defined.');
        foreach ($migrationPaths as $namespace => $path) {
            $migrationsFinder = new Finder();
            $migrationsFinder->files()
                ->in($path)->name('*.php')
                ->sort(function (SplFileInfo $a, SplFileInfo $b) {
                    return strcmp($a->getFilename(), $b->getFilename());
                });
            $statements = [];
            foreach ($migrationsFinder as $migrationFile) {
                $statements = array_merge($statements, $this->getMigrationStatements($migrationFile, $namespace));
                $deleted = $this->getFieldAddedAndDeleted($statements);
                $this->assertEmpty(
                    $deleted,
                    sprintf(
                        'SQL statement deleted the fields in %s: %s',
                        $migrationFile->getFilenameWithoutExtension(),
                        implode(', ', $deleted)
                    )
                );
            }
        }
    }

    public function getFieldAddedAndDeleted(array $statements): array
    {
        $added = [];
        foreach ($statements as $statement) {
            $added = array_merge(
                $added,
                $this->getFields($statement, self::SQL_CREATED_FIELD_REGEX),
                $this->getFields($statement, self::SQL_ADDED_FIELD_REGEX)
            );
            $deleted = $this->getFields($statement, self::SQL_DROPPED_FIELD_REGEX);
            if (!empty(array_intersect($deleted, $added))) {
                return $deleted;
            }
        }

        return [];
    }

    /**
     * @dataProvider provideGetFieldAddedAndDeletedStatements
     */
    public function testGetFieldDeleted(array $statements, array $expected): void
    {
        $this->assertEquals($expected, $this->getFieldAddedAndDeleted($statements));
    }

    public function provideGetFieldAddedAndDeletedStatements(): array
    {
        return [
            [
                [
                    'ALTER TABLE products ADD COLUMN public_id_temp BINARY(6)',
                    'ALTER TABLE products DROP public_id',
                ],
                [],
            ],
            [
                [
                    'ALTER TABLE products ADD COLUMN public_id_temp BINARY(6)',
                    'ALTER TABLE products DROP public_id_temp',
                ],
                [],
            ],
            [
                [
                    'ALTER TABLE products ADD COLUMN public_id BINARY(6)',
                    'ALTER TABLE products DROP public_id',
                ],
                ['products.public_id'],
            ],
        ];
    }

    public function isFieldNameChanged(string $sql): bool
    {
        preg_match_all(self::SQL_CHANGED_FIELD_REGEX, $sql, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[2] !== $match[3]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @dataProvider provideIsFieldNameChangedStatements
     */
    public function testIsFieldNameChanged(string $statement): void
    {
        $this->assertFalse($this->isFieldNameChanged($statement));
    }

    /**
     * @dataProvider provideIsNotFieldNameChangedStatements
     */
    public function testIsNotFieldNameChanged(string $statement): void
    {
        $this->assertTrue($this->isFieldNameChanged($statement));
    }

    public function provideIsNotFieldNameChangedStatements(): array
    {
        return [
            ['ALTER TABLE tenants ADD vat INT NOT NULL AFTER products_out_of_stock_visibility'],
            ['ALTER TABLE products ADD COLUMN public_id BINARY(6) NOT NULL AFTER id'],
            ['ALTER TABLE tenants CHANGE vat vat VARCHAR(5) NOT NULL, CHANGE vat vat VARCHAR(3) NOT NULL'],
            ['ALTER TABLE tenants CHANGE vat `vat` INT NOT NULL'],
            ['ALTER TABLE tenants CHANGE vat `vat` INT NOT NULL'],
            ['alter table tenants change `vat` `vat` int not null'],
            ['ALTER TABLE products ADD COLUMN public_id_temp BINARY(6)'],
        ];
    }

    public function provideIsFieldNameChangedStatements(): array
    {
        return [
            ['ALTER TABLE carts CHANGE COLUMN user_id customer_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\''],
            ['ALTER TABLE companies CHANGE vat vat_number VARCHAR(255) DEFAULT NULL;'],
        ];
    }

    public function getFields(string $statement, string $regex): array
    {
        $fields = [];

        if (preg_match(self::SQL_ALTER_TABLE_REGEX, $statement, $matches)) {
            $tableName = strtolower($matches[2]);
            preg_match_all($regex, $statement, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (!str_ends_with(strtolower($match[2]), '_temp')) {
                    $fields[] = $tableName.'.'.$match[2];
                }
            }
        }

        return $fields;
    }

    /**
     * @dataProvider provideGetFieldsDroppedStatements
     */
    public function testGetFieldsDropped(string $statement, array $expected): void
    {
        $this->assertEquals($expected, $this->getFields($statement, self::SQL_DROPPED_FIELD_REGEX));
    }

    public function provideGetFieldsDroppedStatements(): array
    {
        return [
            ['DROP TABLE tenants', []],
            ['ALTER TABLE redirects DROP INDEX redirects_uri_idx, ADD UNIQUE INDEX UNIQ_B7713AD5841CB121 (uri)', []],
            ['ALTER TABLE carts_products DROP FOREIGN KEY FK_12E5DBFB1AD5CDBF', []],
            ['ALTER TABLE tenants DROP checkout_settings', ['tenants.checkout_settings']],
            ['ALTER TABLE tenants DROP default_language, DROP default_currency', ['tenants.default_language', 'tenants.default_currency']],
        ];
    }

    /**
     * @dataProvider provideGetFieldsAddedStatements
     */
    public function testGetFieldsAdded(string $statement, array $expected): void
    {
        $this->assertEquals($expected, $this->getFields($statement, self::SQL_ADDED_FIELD_REGEX));
    }

    public function provideGetFieldsAddedStatements(): array
    {
        return [
            ['ALTER TABLE carts_products ADD CONSTRAINT FK_12E5DBFB4584665A', []],
            ['ALTER TABLE tenants ADD checkout_settings json NOT NULL', ['tenants.checkout_settings']],
            ["ALTER TABLE tenants \nADD default_language VARCHAR(5), \nADD default_currency VARCHAR(3);\n", ['tenants.default_language', 'tenants.default_currency']],
        ];
    }

    /**
     * @dataProvider provideGetFieldsCreatedStatements
     */
    public function testGetFieldsCreated(string $statement, array $expected): void
    {
        $this->assertEquals($expected, $this->getFields($statement, self::SQL_CREATED_FIELD_REGEX));
    }

    public function provideGetFieldsCreatedStatements(): array
    {
        return [
            ['CREATE TABLE tenants (id BINARY(16) NOT NULL, `updated_at` DATETIME NOT NULL, PRIMARY KEY(id))', ['tenants.id', 'tenants.updated_at']],
        ];
    }

    private function getMigrationConfigs(): array
    {
        $configFinder = new Finder();
        $configFinder->files()->in(self::BASE_DIRECTORY.self::CONFIG_DIRECTORY)->name(self::PATTERN_DIRECTORY);

        $configs = [];
        foreach ($configFinder as $configFile) {
            $yamlContents = Yaml::parseFile($configFile->getRealPath());
            if (isset($yamlContents['doctrine_migrations']['migrations_paths'])) {
                foreach ($yamlContents['doctrine_migrations']['migrations_paths'] as $namespace => $path) {
                    $configs[$namespace] = str_replace('%kernel.project_dir%', self::BASE_DIRECTORY, $path);
                }
            } elseif (isset($yamlContents['migrations_paths'])) {
                foreach ($yamlContents['migrations_paths'] as $namespace => $path) {
                    $configs[$namespace] = self::BASE_DIRECTORY.$path;
                }
            } else {
                $this->fail('The migrations_paths key should be defined.');
            }
        }

        return $configs;
    }

    /**
     * @throws MigrationException
     * @throws Exception
     */
    private function getMigrationStatements(SplFileInfo $migrationFile, string $namespace): array
    {
        $statements = [];

        $migrationClassName = $migrationFile->getFilenameWithoutExtension();
        $migrationTimestamp = \DateTimeImmutable::createFromFormat('YmdHis', str_replace('Version', '', $migrationClassName));
        if (new \DateTimeImmutable(self::TEST_MIGRATIONS_SINCE) < $migrationTimestamp) {
            require_once $migrationFile->getRealPath();
            $migrationName = '\\'.$namespace.'\\'.$migrationClassName;
            /** @var AbstractMigration $migrationInstance */
            $migrationInstance = new $migrationName($this->connection, $this->logger);
            $migrationInstance->up($this->schema);
            foreach ($migrationInstance->getSql() as $query) {
                $statements[] = $query->getStatement();
            }
        }

        return $statements;
    }
}
