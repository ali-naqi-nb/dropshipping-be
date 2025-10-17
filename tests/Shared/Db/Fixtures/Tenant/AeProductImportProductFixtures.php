<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Tenant;

use App\Tests\Shared\Db\Fixtures\BaseFixtures;

/**
 * @codeCoverageIgnore
 */
final class AeProductImportProductFixtures extends BaseFixtures
{
    protected function getFileName(): string
    {
        return 'ae_product_import_products.csv';
    }

    protected function getTableName(): string
    {
        return 'ae_product_import_products';
    }

    protected function getFieldsMapping(): array
    {
        return [
            'ae_sku_code' => ['nullable' => true],
            'nb_product_id' => ['type' => 'uuid', 'nullable' => true],
            'ae_product_description' => ['nullable' => true],
            'ae_product_category_name' => ['nullable' => true],
            'ae_product_barcode' => ['nullable' => true],
            'ae_product_weight' => ['nullable' => true],
            'ae_product_length' => ['nullable' => true],
            'ae_product_width' => ['nullable' => true],
            'ae_product_height' => ['nullable' => true],
            'ae_sku_price' => ['nullable' => true],
            'ae_sku_currency_code' => ['nullable' => true],
            'ae_freight_code' => ['nullable' => true],
            'ae_shipping_fee' => ['nullable' => true],
            'ae_shipping_fee_currency' => ['nullable' => true],
        ];
    }

    public static function getGroups(): array
    {
        return ['test-tenant'];
    }
}
