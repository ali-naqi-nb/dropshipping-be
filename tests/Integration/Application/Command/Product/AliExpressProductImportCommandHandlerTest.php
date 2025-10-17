<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\Product;

use App\Application\Command\Product\AliExpressProductImport\AliExpressProductImportCommand;
use App\Application\Command\Product\AliExpressProductImport\AliExpressProductImportCommandHandler;
use App\Application\Service\AliExpress\AeUtil;
use App\Application\Shared\Error\ErrorResponse;
use App\Application\Shared\Product\AeProductImportResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Product\AeAttributeType;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use Doctrine\DBAL\Exception as DBALException;

final class AliExpressProductImportCommandHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private AeProductImportProductRepositoryInterface $repository;
    private AliExpressProductImportCommandHandler $commandHandler;

    /**
     * @throws DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var AeProductImportProductRepositoryInterface $repository */
        $repository = self::getContainer()->get(AeProductImportProductRepositoryInterface::class);
        $this->repository = $repository;

        /** @var AliExpressProductImportCommandHandler $commandHandler */
        $commandHandler = self::getContainer()->get(AliExpressProductImportCommandHandler::class);
        $this->commandHandler = $commandHandler;

        $this->setAeAccessToken();
    }

    /**
     * @throws DBALException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    private function clearTestFixture(): void
    {
        /** @var AeProductImportProduct $importProduct */
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->repository->delete($importProduct);
    }

    public function testAliExpressProductImportSaveWithNonExisting(): void
    {
        $this->clearTestFixture();

        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNull($importProduct);

        $command = new AliExpressProductImportCommand(
            aeProductUrl: Factory::AE_PRODUCT_URL,
            aeProductShipsTo: Factory::AE_PRODUCT_SHIPS_TO
        );

        $aeProductId = AeUtil::getProductId(Factory::AE_PRODUCT_URL);

        /** @var AeProductImportResponse $response */
        $response = $this->commandHandler->__invoke($command);
        $this->assertInstanceOf(AeProductImportResponse::class, $response);
        $this->assertCount(18, $response->getItems());

        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNotNull($importProduct);

        $this->assertSame($aeProductId, $response->getItems()[0]->getAeProductId());
        $this->assertSame(Factory::AE_IMPORT_SKU_ID, $response->getItems()[0]->getAeSkuId());
        $this->assertSame(Factory::AE_IMPORT_SUBJECT, $response->getItems()[0]->getAeProductName());
        $this->assertSame(Factory::AE_IMPORT_CATEGORY_NAME, $response->getItems()[0]->getAeProductCategoryName());
        $this->assertSame(Factory::AE_IMPORT_SKU_AVAILABLE_STOCK, $response->getItems()[0]->getAeSkuStock());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_SKU_PRICE), $response->getItems()[0]->getAeSkuPrice());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_OFFER_SALE_PRICE), $response->getItems()[0]->getAeOfferSalePrice());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_OFFER_BULK_SALE_PRICE), $response->getItems()[0]->getAeOfferBulkSalePrice());
        $this->assertSame(Factory::AE_IMPORT_CURRENCY_CODE, $response->getItems()[0]->getAeSkuPriceCurrency());
        $this->assertSame(Factory::AE_IMPORT_SKU_IMAGE_0, $response->getItems()[0]->getAeProductImageUrls()[0]);
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_NAME_0, $response->getItems()[0]->getAeVariantAttributes()[0]->getAeVariantAttributeName());
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_VALUE_0, $response->getItems()[0]->getAeVariantAttributes()[0]->getAeVariantAttributeValue());
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_NAME_1, $response->getItems()[0]->getAeVariantAttributes()[1]->getAeVariantAttributeName());
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_VALUE_1, $response->getItems()[0]->getAeVariantAttributes()[1]->getAeVariantAttributeValue());
        $this->assertSame(Factory::AE_DELIVERY_CODE, $response->getItems()[0]->getAeProductShippingOptions()[0]->getCode());
        $this->assertSame(Factory::AE_DELIVERY_SHIPPING_FEE, $response->getItems()[0]->getAeProductShippingOptions()[0]->getShippingFeePrice());
        $this->assertSame(Factory::AE_DELIVERY_SHIPPING_FEE_CURRENCY, $response->getItems()[0]->getAeProductShippingOptions()[0]->getShippingFeeCurrency());

        /** @var AeProductImportProduct $importProduct */
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, $response->getItems()[0]->getAeSkuId());
        $this->assertSame(Factory::AE_PRODUCT_ID, $importProduct->getAeProductId());
        $this->assertSame(Factory::AE_IMPORT_SKU_ID, $importProduct->getAeSkuId());
        $this->assertSame(Factory::AE_IMPORT_SKU_ATTR, $importProduct->getAeSkuAttr());
        $this->assertSame(Factory::AE_IMPORT_SKU_CODE, $importProduct->getAeSkuCode());
        $this->assertNull($importProduct->getNbProductId());
        $this->assertSame(Factory::AE_IMPORT_SUBJECT, $importProduct->getAeProductName());
        $this->assertSame(Factory::AE_IMPORT_DETAIL, $importProduct->getAeProductDescription());
        $this->assertSame(Factory::AE_IMPORT_CATEGORY_NAME, $importProduct->getAeProductCategoryName());
        $this->assertNull($importProduct->getAeProductBarcode());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_GROSS_WEIGHT), $importProduct->getAeProductWeight());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_PACKAGE_LENGTH.''), $importProduct->getAeProductLength());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_PACKAGE_WIDTH.''), $importProduct->getAeProductWidth());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_PACKAGE_HEIGHT.''), $importProduct->getAeProductHeight());
        $this->assertSame(Factory::AE_IMPORT_SKU_AVAILABLE_STOCK, $importProduct->getAeProductStock());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_SKU_PRICE), $importProduct->getAeSkuPrice());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_OFFER_SALE_PRICE), $importProduct->getAeOfferSalePrice());
        $this->assertSame(AeUtil::toBase100(Factory::AE_IMPORT_OFFER_BULK_SALE_PRICE), $importProduct->getAeOfferBulkSalePrice());
        $this->assertSame(Factory::AE_IMPORT_CURRENCY_CODE, $importProduct->getAeSkuCurrencyCode());

        $this->assertSame(AeAttributeType::SkuProperty->value, $importProduct->getAeVariantAttributes()[0]->getAeAttributeType()->value);
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_NAME_0, $importProduct->getAeVariantAttributes()[0]->getAeAttributeName());
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_VALUE_0, $importProduct->getAeVariantAttributes()[0]->getAeAttributeValue());
        $this->assertSame(AeAttributeType::Attribute->value, $importProduct->getAeVariantAttributes()[2]->getAeAttributeType()->value);
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_NAME_2, $importProduct->getAeVariantAttributes()[2]->getAeAttributeName());
        $this->assertSame(Factory::AE_IMPORT_SKU_PROPERTY_VALUE_2, $importProduct->getAeVariantAttributes()[2]->getAeAttributeValue());
        $this->assertSame(Factory::AE_IMPORT_SKU_IMAGE_0, array_key_first($importProduct->getAeProductImageUrls()));
        $this->assertNotNull($importProduct->getCreatedAt());
        $this->assertNotNull($importProduct->getUpdatedAt());
    }

    public function testAliExpressProductImportSaveWithExisting(): void
    {
        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNotNull($importProduct);

        $command = new AliExpressProductImportCommand(
            aeProductUrl: Factory::AE_PRODUCT_URL,
            aeProductShipsTo: Factory::AE_PRODUCT_SHIPS_TO
        );

        /** @var AeProductImportResponse $response */
        $response = $this->commandHandler->__invoke($command);
        $this->assertInstanceOf(AeProductImportResponse::class, $response);
        $this->assertCount(18, $response->getItems());

        $importProduct = $this->repository->findOneByAeProductIdAndAeSkuId(Factory::AE_PRODUCT_ID, Factory::AE_SKU_ID);
        $this->assertNotNull($importProduct);
    }

    public function testAliExpressProductImportFailedFetching(): void
    {
        $command = new AliExpressProductImportCommand(
            aeProductUrl: Factory::AE_PRODUCT_URL_TEST_ERROR,
            aeProductShipsTo: Factory::AE_PRODUCT_SHIPS_TO
        );

        /** @var ErrorResponse $response */
        $response = $this->commandHandler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->assertSame(ErrorType::Error->value, $response->getType()->value);
        $this->assertSame(['common' => 'Failed to get product information from AliExpress'], $response->getErrors());
    }
}
