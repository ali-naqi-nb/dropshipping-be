<?php

declare(strict_types=1);

namespace TenantMigrations;

use App\Application\Shared\Migrations\NBMigrationsTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240918102557 extends AbstractMigration
{
    use NBMigrationsTrait;

    private const TABLE_NAME = 'ae_product_imports';
    private const COLUMN_NAME = 'group_data';

    public function getDescription(): string
    {
        return 'Add '.self::COLUMN_NAME.' column to '.self::TABLE_NAME;
    }

    public function up(Schema $schema): void
    {
        if (!$this->verifyTableHasColumn($schema, self::TABLE_NAME, self::COLUMN_NAME)) {
            $this->addSql('ALTER TABLE '.self::TABLE_NAME.' ADD '.self::COLUMN_NAME.' JSON DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->verifyTableHasColumn($schema, self::TABLE_NAME, self::COLUMN_NAME)) {
            $this->addSql('ALTER TABLE '.self::TABLE_NAME.' DROP COLUMN '.self::COLUMN_NAME);
        }
    }
}
