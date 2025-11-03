<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240816132943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ae_product_import_product_images table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('ae_product_import_product_images')) {
            return;
        }

        $sql = <<< sql
            CREATE TABLE ae_product_import_product_images (
                id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', 
                ae_product_id BIGINT NOT NULL, 
                ae_sku_id BIGINT NOT NULL, 
                ae_image_url VARCHAR(255) NOT NULL, 
                INDEX IDX_5A55774221A8798F4632C377 (ae_product_id, ae_sku_id), 
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
            ALTER TABLE ae_product_import_product_images 
                ADD CONSTRAINT FK_5A55774221A8798F4632C377 FOREIGN KEY (ae_product_id, ae_sku_id) 
                    REFERENCES ae_product_import_products (ae_product_id, ae_sku_id) ON DELETE CASCADE
        sql;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('ae_product_import_product_images')) {
            return;
        }

        if (
            $schema->getTable('ae_product_import_product_images')
                ->hasForeignKey('FK_5A55774221A8798F4632C377')
        ) {
            $this->addSql('ALTER TABLE ae_product_import_product_images DROP FOREIGN KEY FK_5A55774221A8798F4632C377');
        }

        $this->addSql('DROP TABLE ae_product_import_product_images');
    }
}
