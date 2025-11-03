<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251014121448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add offer sale price and offer bulk sale price to ae_product_import_products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ae_product_import_products ADD ae_offer_sale_price BIGINT DEFAULT NULL, ADD ae_offer_bulk_sale_price BIGINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ae_product_import_product DROP ae_offer_sale_price, DROP ae_offer_bulk_sale_price');
    }
}
