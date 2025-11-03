<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240816141535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ae_product_import_product_attributes table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('ae_product_import_product_attributes')) {
            return;
        }

        $sql = <<< sql
            CREATE TABLE ae_product_import_product_attributes (
                id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', 
                 ae_product_id BIGINT NOT NULL, 
                 ae_sku_id BIGINT NOT NULL, 
                 ae_attribute_type VARCHAR(12) NOT NULL, 
                 ae_attribute_name VARCHAR(255) NOT NULL, 
                 ae_attribute_value VARCHAR(255) NOT NULL, 
                 INDEX IDX_EB41497321A8798F4632C377 (ae_product_id, ae_sku_id), 
                 PRIMARY KEY(id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` 
            ENGINE = InnoDB
        sql;

        $this->addSql($sql);

        if (!$schema->hasTable('ae_product_import_products')) {
            return;
        }

        $sql = <<< sql
            ALTER TABLE ae_product_import_product_attributes 
                ADD CONSTRAINT FK_EB41497321A8798F4632C377 FOREIGN KEY (ae_product_id, ae_sku_id) 
                    REFERENCES ae_product_import_products (ae_product_id, ae_sku_id) ON DELETE CASCADE
        sql;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('ae_product_import_products')) {
            return;
        }

        if (
            $schema->getTable('ae_product_import_product_attributes')
                ->hasForeignKey('FK_EB41497321A8798F4632C377')
        ) {
            $this->addSql('ALTER TABLE ae_product_import_product_attributes DROP FOREIGN KEY FK_EB41497321A8798F4632C377');
        }

        $this->addSql('DROP TABLE ae_product_import_product_attributes');
    }
}
