<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Tenant;

use App\Tests\Shared\Db\Fixtures\BaseFixtures;

/**
 * @codeCoverageIgnore
 */
final class DsOrderMappingFixtures extends BaseFixtures
{
    protected function getFileName(): string
    {
        return 'ds_order_mappings.csv';
    }

    protected function getTableName(): string
    {
        return 'ds_order_mapping';
    }

    protected function getFieldsMapping(): array
    {
        return [
            'id' => ['type' => 'uuid'],
            'nb_order_id' => ['type' => 'uuid'],
            'ds_order_id' => ['nullable' => false],
            'ds_provider' => ['nullable' => false],
            'ds_status' => ['nullable' => true],
        ];
    }

    public static function getGroups(): array
    {
        return ['test-tenant'];
    }
}
