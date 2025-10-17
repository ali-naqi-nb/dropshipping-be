<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Service\AliExpress;

use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use App\Infrastructure\Service\AliExpress\DropshipperService;
use App\Infrastructure\Service\App\Aliexpress\AliexpressAccessTokenManager;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeOrderFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Shared\Factory\CurrencyFactory;
use App\Tests\Shared\Factory\LocaleFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DropshipperServiceTest extends IntegrationTestCase
{
    private DropshipperService $dropshipperService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DropshipperService $dropshipperService */
        $dropshipperService = self::getContainer()->get(DropshipperService::class);
        $this->dropshipperService = $dropshipperService;

        $this->setAeAccessToken();
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testGetProductReturnResult(): void
    {
        $aeProduct = $this->dropshipperService->getProduct(
            shipToCountry: Factory::AE_PRODUCT_SHIPS_TO,
            productId: Factory::AE_PRODUCT_ID,
            targetCurrency: CurrencyFactory::USD,
            targetLanguage: LocaleFactory::EN,
        );

        $this->assertNotNull($aeProduct);
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function testGetProductReturnNull(): void
    {
        $aeProduct = $this->dropshipperService->getProduct(
            shipToCountry: Factory::AE_PRODUCT_SHIPS_TO,
            productId: Factory::AE_PRODUCT_404_ERROR,
            targetCurrency: CurrencyFactory::USD,
            targetLanguage: LocaleFactory::EN,
        );

        $this->assertNull($aeProduct);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testGetCategoryReturnResult(): void
    {
        $aeCategory = $this->dropshipperService->getCategory(
            categoryId: Factory::AE_CATEGORY_ID,
            language: LocaleFactory::EN,
        );

        $this->assertNotNull($aeCategory);
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function testGetCategoryReturnNull(): void
    {
        $aeCategory = $this->dropshipperService->getCategory(
            categoryId: Factory::AE_CATEGORY_TEST_ERROR,
            language: LocaleFactory::EN,
        );

        $this->assertNull($aeCategory);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testQueryFreightReturnResult(): void
    {
        $skuId = Factory::AE_SKU_ID;
        $attributeOptions = $this->dropshipperService->queryFreight(
            quantity: 1,
            shipToCountry: Factory::AE_PRODUCT_SHIPS_TO,
            productId: Factory::AE_PRODUCT_ID,
            language: LocaleFactory::EN,
            source: Factory::AE_PRODUCT_SOURCE,
            locale: LocaleFactory::EN,
            selectedSkuId: "$skuId",
            currency: CurrencyFactory::USD,
        );

        $this->assertNotNull($attributeOptions);
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function testQueryFreightReturnNull(): void
    {
        $skuId = Factory::AE_SKU_TEST_ERROR;
        $attributeOptions = $this->dropshipperService->queryFreight(
            quantity: 1,
            shipToCountry: Factory::AE_PRODUCT_SHIPS_TO,
            productId: Factory::AE_PRODUCT_ID,
            language: LocaleFactory::EN,
            source: Factory::AE_PRODUCT_SOURCE,
            locale: LocaleFactory::EN,
            selectedSkuId: "$skuId",
            currency: CurrencyFactory::USD,
        );

        $this->assertNull($attributeOptions);
    }

    public function testCreateOrderSuccess(): void
    {
        $payload = AeOrderFactory::getPlaceOrderPayload();

        // Mock response from makeRequest
        $response = [
            'aliexpress_trade_buy_placeorder_response' => [
                'result' => [
                    'order_list' => ['number' => ['order_1', 'order_2']],
                ],
            ],
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $tenantStorageMock = $this->createMock(TenantStorageInterface::class);
        $tokenManagerMock = $this->createMock(AliexpressAccessTokenManager::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);

        $httpClientMock->method('request')->willReturn($this->createMockHttpResponse(200, $response));
        $tenantStorageMock->method('getId')->willReturn('valid_tenant_id');
        $tokenManagerMock->method('getAccessToken')->willReturn('valid_access_token');

        $dropshipperServiceMock = new DropshipperService(
            'app_key',
            'app_secret',
            $tenantStorageMock,
            $tokenManagerMock,
            $httpClientMock,
            $loggerMock
        );

        // Simulate createOrder method
        $result = $dropshipperServiceMock->createOrder($payload);

        // Assert the result matches the order_list
        $this->assertSame($response['aliexpress_trade_buy_placeorder_response']['result']['order_list']['number'], $result);
    }

    public function testCreateOrderFailure(): void
    {
        $payload = AeOrderFactory::getPlaceOrderPayload();
        $response = ['result' => null];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $tenantStorageMock = $this->createMock(TenantStorageInterface::class);
        $tokenManagerMock = $this->createMock(AliexpressAccessTokenManager::class);
        $httpClientMock = $this->createMock(HttpClientInterface::class);

        $httpClientMock->method('request')->willReturn($this->createMockHttpResponse(200, $response));
        $tenantStorageMock->method('getId')->willReturn('valid_tenant_id');
        $tokenManagerMock->method('getAccessToken')->willReturn('valid_access_token');

        $dropshipperServiceMock = new DropshipperService(
            'app_key',
            'app_secret',
            $tenantStorageMock,
            $tokenManagerMock,
            $httpClientMock,
            $loggerMock
        );

        $loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $dropshipperServiceMock->createOrder($payload);

        $this->assertNull($result);
    }
}
