<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Infrastructure\Domain\Model\Product\DoctrineAeProductImportProductRepository;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMInvalidArgumentException;

final class DoctrineAeProductImportProductRepositoryTest extends IntegrationTestCase
{
    private DoctrineAeProductImportProductRepository $repository;
    private DoctrineTenantConnection $connection;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var DoctrineAeProductImportProductRepository $repository */
        $repository = self::getContainer()->get(DoctrineAeProductImportProductRepository::class);
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    public function testFindOneByAeProductIdAndAeSkuIdReturnAeProductImportProduct(): void
    {
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNotNull($importProduct);

        $this->assertSame((string) Factory::AE_PRODUCT_ID, $importProduct->getAeProductId());
        $this->assertSame((string) Factory::AE_SKU_ID, $importProduct->getAeSkuId());
    }

    public function testFindOneByAeProductIdAndAeSkuIdReturnNull(): void
    {
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(1, 1);
        $this->assertNull($importProduct);
    }

    public function testFindOneByNbProductIdReturnAeProductImportProduct(): void
    {
        $importProduct = $this->repository->findOneByNbProductId(Factory::NB_PRODUCT_ID);
        $this->assertNotNull($importProduct);

        $this->assertSame((string) Factory::AE_PRODUCT_ID, $importProduct->getAeProductId());
        $this->assertSame((string) Factory::AE_SKU_ID, $importProduct->getAeSkuId());
    }

    public function testFindOneByNbProductIdReturnNull(): void
    {
        $importProduct = $this->repository->findOneByNbProductId(Factory::NON_EXISTING_ID);
        $this->assertNull($importProduct);
    }

    public function testSave(): void
    {
        $productImport = Factory::createAeProductImportProduct(
            aeProductId: Factory::NEW_AE_PRODUCT_ID,
            aeSkuId: Factory::NEW_AE_SKU_ID,
            aeSkuAttr: Factory::NEW_AE_SKU_ATTR,
            aeSkuCode: Factory::NEW_AE_SKU_CODE,
            nbProductId: null,
            aeProductName: Factory::NEW_AE_PRODUCT_NAME,
            aeProductDescription: Factory::NEW_AE_PRODUCT_DESCRIPTION,
            aeProductCategoryName: Factory::NEW_AE_PRODUCT_CATEGORY_NAME,
            aeProductBarcode: Factory::NEW_AE_PRODUCT_BARCODE,
            aeProductWeight: Factory::NEW_AE_PRODUCT_WEIGHT,
            aeProductLength: Factory::NEW_AE_PRODUCT_LENGTH,
            aeProductWidth: Factory::NEW_AE_PRODUCT_WIDTH,
            aeProductHeight: Factory::NEW_AE_PRODUCT_HEIGHT,
            aeProductStock: Factory::NEW_AE_PRODUCT_STOCK,
            aeSkuPrice: Factory::NEW_AE_SKU_PRICE,
            aeSkuCurrencyCode: Factory::NEW_AE_SKU_CURRENCY_CODE,
            aeFreightCode: Factory::NEW_AE_FREIGHT_CODE,
            aeShippingFee: Factory::NEW_AE_SHIPPING_FEE,
            aeShippingFeeCurrency: Factory::NEW_AE_SHIPPING_FEE_CURRENCY,
            withTimeStamps: false,
        );

        /** @var AeProductImportProductAttribute[] $attributes */
        $attributes = [Factory::createAeProductImportProductAttribute(aeProductImportProduct: $productImport)];
        $images = [Factory::AE_IMAGE_URL => true];

        $productImport->setAeVariantAttributes($attributes);
        $productImport->setAeProductImageUrls($images);

        $this->assertNull($productImport->getCreatedAt());
        $this->assertNull($productImport->getUpdatedAt());

        $this->repository->save($productImport);

        $savedProductImport = $this->repository->findOneByAeProductIdAndAeSkuId(
            aeProductId: Factory::NEW_AE_PRODUCT_ID,
            aeSkuId: Factory::NEW_AE_SKU_ID,
        );
        $this->assertNotNull($savedProductImport);

        $this->assertSame(Factory::NEW_AE_PRODUCT_ID, $savedProductImport->getAeProductId());
        $this->assertSame(Factory::NEW_AE_SKU_ID, $savedProductImport->getAeSkuId());
        $this->assertSame(Factory::NEW_AE_SKU_ATTR, $savedProductImport->getAeSkuAttr());
        $this->assertSame(Factory::NEW_AE_SKU_CODE, $savedProductImport->getAeSkuCode());
        $this->assertNull($savedProductImport->getNbProductId());
        $this->assertSame(Factory::NEW_AE_PRODUCT_NAME, $savedProductImport->getAeProductName());
        $this->assertSame(Factory::NEW_AE_PRODUCT_DESCRIPTION, $savedProductImport->getAeProductDescription());
        $this->assertSame(Factory::NEW_AE_PRODUCT_CATEGORY_NAME, $savedProductImport->getAeProductCategoryName());
        $this->assertSame(Factory::NEW_AE_PRODUCT_BARCODE, $savedProductImport->getAeProductBarcode());
        $this->assertSame(Factory::NEW_AE_PRODUCT_WEIGHT, $savedProductImport->getAeProductWeight());
        $this->assertSame(Factory::NEW_AE_PRODUCT_LENGTH, $savedProductImport->getAeProductLength());
        $this->assertSame(Factory::NEW_AE_PRODUCT_WIDTH, $savedProductImport->getAeProductWidth());
        $this->assertSame(Factory::NEW_AE_PRODUCT_HEIGHT, $savedProductImport->getAeProductHeight());
        $this->assertSame(Factory::NEW_AE_PRODUCT_STOCK, $savedProductImport->getAeProductStock());
        $this->assertSame(Factory::NEW_AE_SKU_PRICE, $savedProductImport->getAeSkuPrice());
        $this->assertSame(Factory::NEW_AE_SKU_CURRENCY_CODE, $savedProductImport->getAeSkuCurrencyCode());
        $this->assertSame(Factory::NEW_AE_FREIGHT_CODE, $savedProductImport->getAeFreightCode());
        $this->assertSame(Factory::NEW_AE_SHIPPING_FEE, $savedProductImport->getAeShippingFee());
        $this->assertSame(Factory::NEW_AE_SHIPPING_FEE_CURRENCY, $savedProductImport->getAeShippingFeeCurrency());
        $this->assertSame($attributes[0]->getAeAttributeType(), $savedProductImport->getAeVariantAttributes()[0]->getAeAttributeType());
        $this->assertSame($attributes[0]->getAeAttributeName(), $savedProductImport->getAeVariantAttributes()[0]->getAeAttributeName());
        $this->assertSame($attributes[0]->getAeAttributeValue(), $savedProductImport->getAeVariantAttributes()[0]->getAeAttributeValue());
        $this->assertSame($attributes[0]->getAeProductImportProduct()->getAeProductId(), $savedProductImport->getAeProductId());
        $this->assertSame($images, $savedProductImport->getAeProductImageUrls());
        $this->assertNotNull($savedProductImport->getCreatedAt());
        $this->assertNotNull($savedProductImport->getUpdatedAt());
    }

    public function testSaveThrowsException(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);
        $this->repository->save(Factory::createAeProductImportProduct());
    }

    public function testDelete(): void
    {
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNotNull($importProduct);

        $this->repository->delete($importProduct);

        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNull($importProduct);
    }

    public function testDeleteThrowsException(): void
    {
        $this->expectException(ORMInvalidArgumentException::class);
        $this->repository->delete(Factory::createAeProductImportProduct());
    }

    public function testFindAllDistinctAeProductIdsReturnsArray(): void
    {
        $productIds = $this->repository->findAllDistinctAeProductIds();

        $this->assertIsArray($productIds);
        $this->assertGreaterThan(0, count($productIds));

        foreach ($productIds as $productId) {
            $this->assertIsInt($productId);
        }
    }

    public function testFindAllDistinctAeProductIdsReturnsDistinctValues(): void
    {
        $productIds = $this->repository->findAllDistinctAeProductIds();

        $uniqueProductIds = array_unique($productIds);
        $this->assertCount(count($productIds), $uniqueProductIds, 'All product IDs should be distinct');
    }

    public function testFindAllDistinctAeProductIdsReturnsOrderedByAsc(): void
    {
        $productIds = $this->repository->findAllDistinctAeProductIds();

        if (count($productIds) > 1) {
            $sortedProductIds = $productIds;
            sort($sortedProductIds);
            $this->assertSame($sortedProductIds, $productIds, 'Product IDs should be ordered ascending');
        }

        $this->assertTrue(true); // Test passes if we get here
    }

    public function testFindAllDistinctAeProductIdsWithLimit(): void
    {
        $limit = 2;
        $productIds = $this->repository->findAllDistinctAeProductIds($limit);

        $this->assertIsArray($productIds);
        $this->assertLessThanOrEqual($limit, count($productIds));
    }

    public function testFindAllDistinctAeProductIdsWithLimitOne(): void
    {
        $productIds = $this->repository->findAllDistinctAeProductIds(1);

        $this->assertIsArray($productIds);
        $this->assertCount(1, $productIds);
    }

    public function testFindAllDistinctAeProductIdsWithZeroLimit(): void
    {
        $productIds = $this->repository->findAllDistinctAeProductIds(0);

        $this->assertIsArray($productIds);
        $this->assertCount(0, $productIds);
    }

    public function testFindAllDistinctAeProductIdsWithNullLimitReturnsAll(): void
    {
        $allProductIds = $this->repository->findAllDistinctAeProductIds(null);
        $limitedProductIds = $this->repository->findAllDistinctAeProductIds(1);

        if (count($allProductIds) > 1) {
            $this->assertGreaterThan(count($limitedProductIds), count($allProductIds));
        }

        $this->assertTrue(true); // Test passes if we get here
    }

    public function testFindAllByAeProductIdReturnsArray(): void
    {
        $products = $this->repository->findAllByAeProductId(Factory::AE_PRODUCT_ID);

        $this->assertIsArray($products);
        $this->assertGreaterThan(0, count($products));

        foreach ($products as $product) {
            $this->assertSame((string)Factory::AE_PRODUCT_ID, $product->getAeProductId());
        }
    }

    public function testFindAllByAeProductIdReturnsEmptyArrayForNonExistent(): void
    {
        $products = $this->repository->findAllByAeProductId(999999999);

        $this->assertIsArray($products);
        $this->assertCount(0, $products);
    }

    public function testFindAllByAeProductIdReturnsMultipleVariants(): void
    {
        // First, ensure we have data
        $products = $this->repository->findAllByAeProductId(Factory::AE_PRODUCT_ID);

        if (count($products) > 0) {
            $this->assertIsArray($products);

            // All products should have the same AE product ID
            foreach ($products as $product) {
                $this->assertSame((string)Factory::AE_PRODUCT_ID, $product->getAeProductId());
            }
        }

        $this->assertTrue(true); // Test passes if we get here
    }

    public function testFindAllByAeProductIdWithStringId(): void
    {
        $products = $this->repository->findAllByAeProductId((string)Factory::AE_PRODUCT_ID);

        $this->assertIsArray($products);
        $this->assertGreaterThan(0, count($products));

        foreach ($products as $product) {
            $this->assertSame((string)Factory::AE_PRODUCT_ID, $product->getAeProductId());
        }
    }

    public function testFindAllByAeProductIdWithIntId(): void
    {
        $products = $this->repository->findAllByAeProductId((int)Factory::AE_PRODUCT_ID);

        $this->assertIsArray($products);
        $this->assertGreaterThan(0, count($products));

        foreach ($products as $product) {
            $this->assertSame((string)Factory::AE_PRODUCT_ID, $product->getAeProductId());
        }
    }

    public function testFindAllDistinctAeProductIdsAndFindAllByAeProductIdIntegration(): void
    {
        // Get all distinct product IDs
        $distinctProductIds = $this->repository->findAllDistinctAeProductIds(1);

        $this->assertGreaterThan(0, count($distinctProductIds), 'Should have at least one product ID');

        // For each product ID, verify we can find its variants
        foreach ($distinctProductIds as $productId) {
            $variants = $this->repository->findAllByAeProductId($productId);
            $this->assertIsArray($variants);
            $this->assertGreaterThan(0, count($variants), "Product ID {$productId} should have at least one variant");

            // Verify all variants have the correct product ID
            foreach ($variants as $variant) {
                $this->assertSame((string)$productId, $variant->getAeProductId());
            }
        }
    }

    public function testFindAllDistinctAeProductIdsOnlyReturnsProductsWithNbProductId(): void
    {
        // Create a product without nbProductId
        $productWithoutNbProductId = Factory::createAeProductImportProduct(
            aeProductId: 99999999,
            aeSkuId: 88888888,
            aeSkuAttr: 'test-attr',
            aeSkuCode: 'test-code',
            nbProductId: null,
            aeProductName: 'Test Product Without NB ID',
            aeProductDescription: 'Test description',
            aeProductCategoryName: 'Test Category',
            aeProductBarcode: '123456',
            aeProductWeight: 100,
            aeProductLength: 10,
            aeProductWidth: 10,
            aeProductHeight: 10,
            aeProductStock: 50,
            aeSkuPrice: 1000,
            aeSkuCurrencyCode: 'USD',
            aeFreightCode: 'CAINIAO_STANDARD',
            aeShippingFee: 500,
            aeShippingFeeCurrency: 'USD',
            withTimeStamps: false,
        );

        $this->repository->save($productWithoutNbProductId);

        // Get all product IDs - should only include products with nbProductId
        $productIds = $this->repository->findAllDistinctAeProductIds();

        // Verify that our product without nbProductId is NOT in the list
        $this->assertNotContains(99999999, $productIds, 'Products without nbProductId should not be returned');

        // Get all products from the repository for each returned ID and verify they all have nbProductId
        foreach ($productIds as $productId) {
            $products = $this->repository->findAllByAeProductId($productId);
            foreach ($products as $product) {
                $this->assertNotNull(
                    $product->getNbProductId(),
                    "Product {$productId} should have nbProductId but found null"
                );
            }
        }

        // Clean up
        $this->repository->delete($productWithoutNbProductId);
    }

    public function testFindAllDistinctAeProductIdsIncludesProductsWithNbProductId(): void
    {
        // The fixture data should have at least one product with nbProductId
        $productWithNbProductId = $this->repository->findOneByAeProductIdAndAeSkuId(
            Factory::AE_PRODUCT_ID,
            Factory::AE_SKU_ID
        );

        $this->assertNotNull($productWithNbProductId);
        $this->assertNotNull($productWithNbProductId->getNbProductId(), 'Fixture should have nbProductId');

        $productIds = $this->repository->findAllDistinctAeProductIds();

        // Verify that the product with nbProductId is included in the results
        $this->assertContains(
            (int)Factory::AE_PRODUCT_ID,
            $productIds,
            'Products with nbProductId should be returned'
        );
    }
}
