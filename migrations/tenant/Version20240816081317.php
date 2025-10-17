<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240816081317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ae_product_import_products table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('ae_product_import_products')) {
            return;
        }

        $sql = <<<sql
            CREATE TABLE ae_product_import_products (
                ae_product_id BIGINT NOT NULL, 
                ae_sku_id BIGINT NOT NULL, 
                ae_sku_attr VARCHAR(255) NOT NULL, 
                ae_sku_code VARCHAR(255) DEFAULT NULL, 
                nb_product_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', 
                ae_product_name VARCHAR(512) NOT NULL, 
                ae_product_description LONGTEXT DEFAULT NULL, 
                ae_product_category_name VARCHAR(255) DEFAULT NULL, 
                ae_product_barcode VARCHAR(255) DEFAULT NULL, 
                ae_product_weight INT DEFAULT NULL, 
                ae_product_length INT DEFAULT NULL, 
                ae_product_width INT DEFAULT NULL, 
                ae_product_height INT DEFAULT NULL, 
                ae_product_stock INT DEFAULT 0 NOT NULL, 
                ae_sku_price BIGINT DEFAULT NULL, 
                ae_sku_currency_code VARCHAR(3) DEFAULT NULL, 
                ae_freight_code VARCHAR(255) DEFAULT NULL, 
                ae_shipping_fee BIGINT DEFAULT NULL, 
                ae_shipping_fee_currency VARCHAR(3) DEFAULT NULL, 
                created_at DATETIME NOT NULL, 
                updated_at DATETIME NOT NULL, 
                PRIMARY KEY(ae_product_id, ae_sku_id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` 
            ENGINE = InnoDB
        sql;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('ae_product_import_products')) {
            return;
        }

        $this->addSql('DROP TABLE ae_product_import_products');
    }
}
