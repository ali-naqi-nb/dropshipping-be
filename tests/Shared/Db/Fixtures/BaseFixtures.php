<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @codeCoverageIgnore
 */
abstract class BaseFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private Connection $connection,
        private string $resourceDir,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->insertFromCsvToDb($this->getFileName(), $this->getTableName());
    }

    public static function getGroups(): array
    {
        return ['test-main'];
    }

    protected function getSeparator(): string
    {
        return ',';
    }

    abstract protected function getFileName(): string;

    abstract protected function getTableName(): string;

    /**
     * Available properties are type and nullable.
     *
     * @return array<string,array<string, mixed>>
     */
    abstract protected function getFieldsMapping(): array;

    private function insertFromCsvToDb(string $fileName, string $tableName): void
    {
        $separator = $this->getSeparator();
        $sqlPlaceholderPattern = ':{columnName}{rowNumber}';
        $sqlInsertPatterns = [];
        $insertValues = [];
        $file = file($this->resourceDir.$fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if (false !== $file) {
            $firstRow = array_shift($file);
            if (null !== $firstRow) {
                // GET HEADERS
                $headers = str_getcsv($firstRow, $separator);
                // GET DATA
                foreach ($file as $rowNumber => $rowValue) {
                    $data = str_getcsv($rowValue, $separator);
                    $values = [];
                    foreach ($data as $key => $value) {
                        /** @var string $column */
                        $column = $headers[$key];
                        $placeholderKey = strtr($sqlPlaceholderPattern, [
                            '{columnName}' => $column,
                            '{rowNumber}' => $rowNumber,
                        ]);
                        $values[$placeholderKey] = $this->mapCsvValueToDbValue($column, $value);
                    }
                    $sqlInsertPatterns[] = '('.implode(', ', array_keys($values)).')';
                    $insertValues = array_merge($insertValues, $values);
                }
                $implodeSqlHeaders = implode(', ', $headers);
                $implodedSqlPattern = implode(', ', $sqlInsertPatterns);

                $this->connection
                    ->prepare("INSERT INTO $tableName ($implodeSqlHeaders) VALUES $implodedSqlPattern")
                    ->executeQuery($insertValues);
            }
        }
    }

    private function mapCsvValueToDbValue(string $columnName, mixed $value): mixed
    {
        $fieldMapping = $this->getFieldMapping($columnName);
        if ('' === $value && $fieldMapping['nullable']) {
            return null;
        }
        if ('' !== $value && 'uuid' === $fieldMapping['type']) {
            return Uuid::fromString($value)->toBinary();
        }

        if (true === $fieldMapping['symfony_parameters']) {
            $parameterMatches = [];

            preg_match_all('/%[^%]+%/', $value, $parameterMatches);

            $parameters = $parameterMatches[0];

            foreach ($parameters as $parameter) {
                /** @var string $parameterValue */
                $parameterValue = $this->parameterBag->get(trim($parameter, '%'));

                $value = str_replace($parameter, $parameterValue, $value);
            }
        }

        return $value;
    }

    /**
     * @return array{type: ?string, nullable: bool, symfony_parameters: bool}
     */
    private function getFieldMapping(string $field): array
    {
        $defaultMapping = ['type' => null, 'nullable' => false, 'symfony_parameters' => false];
        $fieldMapping = $this->getFieldsMapping()[$field] ?? [];

        /** @var array{type: ?string, nullable: bool, symfony_parameters: bool} $fieldMapping */
        $fieldMapping = array_merge($defaultMapping, $fieldMapping);

        return $fieldMapping;
    }
}
