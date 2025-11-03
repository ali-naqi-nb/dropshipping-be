<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240816071638 extends AbstractMigration
{
    private const TABLE_NAME = 'ds_order_mapping';
    private const COLUMN_ID = 'id';
    private const COLUMN_NB_ORDER_ID = 'nb_order_id';
    private const COLUMN_DS_ORDER_ID = 'ds_order_id';
    private const COLUMN_DS_PROVIDER = 'ds_provider';
    private const COLUMN_DS_STATUS = 'ds_status';
    private const COLUMN_CREATED_AT = 'created_at';
    private const COLUMN_UPDATED_AT = 'updated_at';

    public function getDescription(): string
    {
        return sprintf('Create %s table', self::TABLE_NAME);
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable(self::TABLE_NAME)) {
            $this->addSql(sprintf(
                'CREATE TABLE %s (
                    %s BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                    %s BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                    %s VARCHAR(36) NOT NULL, 
                    %s VARCHAR(24) NOT NULL, 
                    %s VARCHAR(255) DEFAULT NULL, 
                    %s DATETIME NOT NULL, 
                    %s DATETIME NOT NULL, 
                    PRIMARY KEY(%s)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB',
                self::TABLE_NAME,
                self::COLUMN_ID,
                self::COLUMN_NB_ORDER_ID,
                self::COLUMN_DS_ORDER_ID,
                self::COLUMN_DS_PROVIDER,
                self::COLUMN_DS_STATUS,
                self::COLUMN_CREATED_AT,
                self::COLUMN_UPDATED_AT,
                self::COLUMN_ID
            ));
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME)) {
            $this->addSql(sprintf('DROP TABLE %s', self::TABLE_NAME));
        }
    }
}
