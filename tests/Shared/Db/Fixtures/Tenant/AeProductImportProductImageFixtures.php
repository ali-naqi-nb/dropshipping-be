<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Tenant;

use App\Tests\Shared\Db\Fixtures\BaseFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * @codeCoverageIgnore
 */
final class AeProductImportProductImageFixtures extends BaseFixtures implements DependentFixtureInterface
{
    protected function getFileName(): string
    {
        return 'ae_product_import_product_images.csv';
    }

    protected function getTableName(): string
    {
        return 'ae_product_import_product_images';
    }

    protected function getFieldsMapping(): array
    {
        return [
            'id' => ['type' => 'uuid'],
        ];
    }

    public static function getGroups(): array
    {
        return ['test-tenant'];
    }

    public function getDependencies(): array
    {
        return [
            AeProductImportProductFixtures::class,
        ];
    }
}
