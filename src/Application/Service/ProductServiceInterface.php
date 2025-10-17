<?php

declare(strict_types=1);

namespace App\Application\Service;

interface ProductServiceInterface
{
    public function sendDsProductTypeImport(string $productTypeName, string $dsProductId, string $dsProvider = 'AliExpress'): array;

    public function sendDsAttributeImport(string $productTypeId, string $dsProductId, array $attributes, string $dsProvider = 'AliExpress'): array;

    public function sendDsProductGroupImport(string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array;

    public function sendDsProductImagesUpdate(int|string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array;
}
