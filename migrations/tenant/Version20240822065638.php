<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240822065638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_main to ae_product_import_product_images table';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('ae_product_import_product_images')) {
            return;
        }

        if ($schema->getTable('ae_product_import_product_images')->hasColumn('is_main')) {
            return;
        }

        $this->addSql('ALTER TABLE ae_product_import_product_images ADD is_main TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('ae_product_import_product_images')) {
            return;
        }

        if (!$schema->getTable('ae_product_import_product_images')->hasColumn('is_main')) {
            return;
        }

        $this->addSql('ALTER TABLE ae_product_import_product_images DROP is_main');
    }
}
