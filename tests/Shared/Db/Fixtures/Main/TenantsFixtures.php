<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Main;

use App\Tests\Shared\Db\Fixtures\BaseFixtures;

/**
 * @codeCoverageIgnore
 */
final class TenantsFixtures extends BaseFixtures
{
    protected function getFileName(): string
    {
        return 'tenants.csv';
    }

    protected function getTableName(): string
    {
        return 'tenants';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldsMapping(): array
    {
        return [
            'id' => ['type' => 'uuid'],
            'company_id' => ['type' => 'uuid'],
            'db_config' => ['symfony_parameters' => true],
            'deleted_at' => ['nullable' => true],
            'apps' => ['nullable' => true],
            'configured_at' => ['nullable' => true],
        ];
    }
}
