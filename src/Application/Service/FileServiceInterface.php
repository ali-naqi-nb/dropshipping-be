<?php

namespace App\Application\Service;

interface FileServiceInterface
{
    /**
     * @param array<int, array{dsVariantId: string, images: string[]}> $products
     */
    public function sendDsProductImagesImport(string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array;
}
