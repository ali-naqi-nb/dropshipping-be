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
}
