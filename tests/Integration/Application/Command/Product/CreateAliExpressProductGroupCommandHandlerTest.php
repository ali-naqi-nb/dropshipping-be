<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\Product;

use App\Application\Command\Product\AliExpressProductImport\CreateAliExpressProductGroupCommand;
use App\Application\Command\Product\AliExpressProductImport\CreateAliExpressProductGroupCommandHandler;
use App\Application\Service\ProductServiceInterface;
use App\Application\Shared\Product\AeProductGroupResponse;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\CreateAliexpressProductGroupValidatorInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\ProductFactory;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\MockObject\MockObject;

final class CreateAliExpressProductGroupCommandHandlerTest extends IntegrationTestCase
{
    private ProductServiceInterface&MockObject $productService;
    private AeProductImportRepositoryInterface $productImportRepository;
    private CreateAliExpressProductGroupCommandHandler $handler;
    private DoctrineTenantConnection $connection;

    /**
     * @throws DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        $this->productService = $this->createMock(ProductServiceInterface::class);

        /** @var AeProductImportRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        /** @var CreateAliexpressProductGroupValidatorInterface $validator */
        $validator = self::getContainer()->get(CreateAliexpressProductGroupValidatorInterface::class);

        $handler = new CreateAliExpressProductGroupCommandHandler($this->productService, $this->productImportRepository, $validator);
        $this->handler = $handler;
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

    public function testInvokeReturnsSuccess(): void
    {
        $productArr = [
            'aeProductId' => AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            'aeSkuId' => AeProductImportProductFactory::AE_SKU_ID,
            'name' => AeProductImportProductFactory::NEW_AE_PRODUCT_NAME,
            'description' => AeProductImportProductFactory::NEW_AE_PRODUCT_DESCRIPTION,
            'sku' => AeProductImportProductFactory::AE_IMPORT_SKU_CODE,
            'price' => AeProductImportProductFactory::AE_SKU_PRICE,
            'mainCategoryId' => ProductFactory::CATEGORY_ID_FIRST,
            'additionalCategories' => [ProductFactory::CATEGORY_ID_SECOND],
            'stock' => AeProductImportProductFactory::AE_PRODUCT_STOCK,
            'barcode' => AeProductImportProductFactory::NEW_AE_PRODUCT_BARCODE,
            'weight' => AeProductImportProductFactory::AE_PRODUCT_WEIGHT,
            'length' => AeProductImportProductFactory::AE_PRODUCT_LENGTH,
            'width' => AeProductImportProductFactory::AE_IMPORT_PACKAGE_WIDTH,
            'height' => AeProductImportProductFactory::AE_PRODUCT_HEIGHT,
            'costPerItem' => AeProductImportProductFactory::AE_SKU_PRICE,
            'productTypeName' => AeProductImportProductFactory::PRODUCT_TYPE_NAME,
            'attributes' => AeProductImportFactory::ATTRIBUTES,
            'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
        ];

        $command = new CreateAliExpressProductGroupCommand(products: [$productArr]);

        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(AeProductGroupResponse::class, $response);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::NEW_AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
    }

    public function testInvokeWithExistingImportReturnsSuccess(): void
    {
        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);

        $productArr = [
            'aeProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'aeSkuId' => AeProductImportProductFactory::AE_SKU_ID,
            'name' => AeProductImportProductFactory::NEW_AE_PRODUCT_NAME,
            'description' => AeProductImportProductFactory::NEW_AE_PRODUCT_DESCRIPTION,
            'sku' => AeProductImportProductFactory::AE_IMPORT_SKU_CODE,
            'price' => AeProductImportProductFactory::AE_SKU_PRICE,
            'mainCategoryId' => ProductFactory::CATEGORY_ID_FIRST,
            'additionalCategories' => [ProductFactory::CATEGORY_ID_SECOND],
            'stock' => AeProductImportProductFactory::AE_PRODUCT_STOCK,
            'barcode' => AeProductImportProductFactory::NEW_AE_PRODUCT_BARCODE,
            'weight' => AeProductImportProductFactory::AE_PRODUCT_WEIGHT,
            'length' => AeProductImportProductFactory::AE_PRODUCT_LENGTH,
            'width' => AeProductImportProductFactory::AE_IMPORT_PACKAGE_WIDTH,
            'height' => AeProductImportProductFactory::AE_PRODUCT_HEIGHT,
            'costPerItem' => AeProductImportProductFactory::AE_SKU_PRICE,
            'productTypeName' => AeProductImportProductFactory::PRODUCT_TYPE_NAME,
            'attributes' => AeProductImportFactory::ATTRIBUTES,
            'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
        ];

        $command = new CreateAliExpressProductGroupCommand(products: [$productArr]);

        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(AeProductGroupResponse::class, $response);
    }
}
