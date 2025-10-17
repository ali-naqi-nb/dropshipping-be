<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911101343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ae_product_imports table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('ae_product_imports')) {
            return;
        }
        $sql = <<<SQL
            CREATE TABLE ae_product_imports (
                id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', 
                ae_product_id BIGINT DEFAULT NULL, 
                completed_step INT NOT NULL,
                total_steps INT NOT NULL, 
                shipping_options JSON DEFAULT NULL, 
                PRIMARY KEY(id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` 
            ENGINE = InnoDB
        SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('ae_product_imports')) {
            $this->addSql('DROP TABLE ae_product_imports');
        }
    }
}
