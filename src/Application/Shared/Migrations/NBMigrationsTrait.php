<?php

declare(strict_types=1);

namespace App\Application\Shared\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * @codeCoverageIgnore
 */
trait NBMigrationsTrait
{
    /**
     * @throws SchemaException
     */
    private function verifyTableHasAllColumns(Schema $schema, string $tableName, array $columnNames): bool
    {
        if (!$schema->hasTable($tableName)) {
            return false;
        }

        $table = $schema->getTable($tableName);

        foreach ($columnNames as $column) {
            if (!$table->hasColumn($column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws SchemaException
     */
    private function verifyTableHasColumn(Schema $schema, string $tableName, string $columnName): bool
    {
        return $schema->hasTable($tableName) && $schema->getTable($tableName)->hasColumn($columnName);
    }

    /**
     * @throws SchemaException
     */
    private function verifyTableHasForeignKey(Schema $schema, string $tableName, string $foreignKeyName): bool
    {
        return !($schema->hasTable($tableName) && !$schema->getTable($tableName)->hasForeignKey($foreignKeyName));
    }

    /**
     * @throws SchemaException
     */
    private function verifyAllColumnsNotInTable(Schema $schema, string $tableName, array $columnNames): bool
    {
        if (!$schema->hasTable($tableName)) {
            return false;
        }

        $table = $schema->getTable($tableName);

        foreach ($columnNames as $column) {
            if ($table->hasColumn($column)) {
                return false;
            }
        }

        return true;
    }
}
