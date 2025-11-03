<?php

declare(strict_types=1);

namespace MainMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220908111653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tenants table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('tenants')) {
            return;
        }

        $sql = <<< sql
            CREATE TABLE tenants (
                id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
                company_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
                domain VARCHAR(255) NOT NULL,
                db_config VARCHAR(1024) NOT NULL,
                default_language VARCHAR(5) NOT NULL,
                default_currency VARCHAR(3) NOT NULL,
                status VARCHAR(255) NOT NULL,
                is_available TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)
             )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        sql;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS tenants');
    }
}
