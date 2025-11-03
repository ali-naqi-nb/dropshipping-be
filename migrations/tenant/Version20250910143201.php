<?php

declare(strict_types=1);

namespace TenantMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250910143201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updated ae_product_import_product_attributes to text';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ae_product_import_product_attributes CHANGE ae_attribute_value ae_attribute_value LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ae_product_import_product_attributes CHANGE ae_attribute_value ae_attribute_value VARCHAR(255) NOT NULL');
    }
}
