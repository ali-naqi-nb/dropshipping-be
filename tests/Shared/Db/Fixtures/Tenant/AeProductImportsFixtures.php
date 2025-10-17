<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Tenant;

use App\Tests\Shared\Db\Fixtures\BaseFixtures;

/**
 * @codeCoverageIgnore
 */
final class AeProductImportsFixtures extends BaseFixtures
{
    protected function getFileName(): string
    {
        return 'ae_product_imports.csv';
    }

    protected function getTableName(): string
    {
        return 'ae_product_imports';
    }

    protected function getFieldsMapping(): array
    {
        return [
            'id' => ['type' => 'uuid'],
            'ae_product_id' => ['type' => 'int', 'nullable' => false],
            'completed_steps' => ['type' => 'int', 'nullable' => false],
            'total_steps' => ['type' => 'int', 'nullable' => false],
            'shipping_options' => ['nullable' => true],
            'group_data' => ['type' => 'array', 'nullable' => false],
        ];
    }

    public static function getGroups(): array
    {
        return ['test-tenant'];
    }
}
