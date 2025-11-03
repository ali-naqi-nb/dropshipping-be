<?php

declare(strict_types=1);

namespace MainMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240731145008 extends AbstractMigration
{
    private const TABLE_NAME = 'tenants';
    private const COLUMN_NAME = 'apps';

    public function getDescription(): string
    {
        return 'Add column apps to tenants table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME) && !$schema->getTable(self::TABLE_NAME)->hasColumn(self::COLUMN_NAME)) {
            $this->addSql('ALTER TABLE '.self::TABLE_NAME.' ADD '.self::COLUMN_NAME.' JSON DEFAULT NULL AFTER default_currency');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME) && $schema->getTable(self::TABLE_NAME)->hasColumn(self::COLUMN_NAME)) {
            $this->addSql('ALTER TABLE '.self::TABLE_NAME.' DROP '.self::COLUMN_NAME);
        }
    }
}
