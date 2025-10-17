<?php

declare(strict_types=1);

namespace MainMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240813051000 extends AbstractMigration
{
    private const TABLE_NAME = 'tenants';
    private const COLUMN_NAME = 'configured_at';

    public function getDescription(): string
    {
        return sprintf('Added %s to %s table', self::COLUMN_NAME, self::TABLE_NAME);
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME) && !$schema->getTable(self::TABLE_NAME)->hasColumn(self::COLUMN_NAME)) {
            $this->addSql(sprintf('ALTER TABLE %s ADD %s DATETIME DEFAULT NULL AFTER is_available', self::TABLE_NAME, self::COLUMN_NAME));
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME) && $schema->getTable(self::TABLE_NAME)->hasColumn(self::COLUMN_NAME)) {
            $this->addSql(sprintf('ALTER TABLE %s DROP %s', self::TABLE_NAME, self::COLUMN_NAME));
        }
    }
}
