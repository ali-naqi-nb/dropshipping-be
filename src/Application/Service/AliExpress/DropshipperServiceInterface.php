<?php

declare(strict_types=1);

namespace App\Application\Service\AliExpress;

interface DropshipperServiceInterface
{
    public const AE_INDICATOR_VALUE_NULL = '_Null';
    public const AE_IMAGES_DELIMITER = ';';

    /**
     * @return ?array<string, mixed>
     */
    public function getProduct(
        string $shipToCountry,
        int|string $productId,
        string $targetCurrency,
        string $targetLanguage,
    ): ?array;

    /**
     * @return ?array<array<string, mixed>>
     */
    public function getCategory(int $categoryId, string $language): ?array;

    /**
     * @return ?array<array<string, mixed>>
     */
    public function queryFreight(
        int $quantity,
        string $shipToCountry,
        int $productId,
        string $language,
        string $source,
        string $locale,
        string $selectedSkuId,
        string $currency,
    ): ?array;

    public function createOrder(array $payload): ?array;
}
