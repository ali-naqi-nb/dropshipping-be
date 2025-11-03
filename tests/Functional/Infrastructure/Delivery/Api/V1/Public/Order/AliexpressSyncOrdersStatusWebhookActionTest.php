<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Public\Order;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class AliexpressSyncOrdersStatusWebhookActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/v1/{_locale}/aliexpress/orders/webhook';
    protected const METHOD = 'POST';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSyncOrderStatusWebhookWithNonExistSellerReturns200(): void
    {
        $data = [
            'seller_id' => 'null',
            'message_type' => 53,
            'data' => [
                'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
                'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
                'orderStatus' => 'OrderCreated',
                'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            ],
            'timestamp' => 1730455911,
            'site' => 'ae_global',
        ];

        $this->makePostRequest(
            method: self::METHOD,
            data: $data
        );

        self::assertResponseStatusCodeSame(200);
    }

    public function testSyncOrderStatusWebhookWithNonExistSellerReturns422(): void
    {
        $data = [
            'seller_id' => 'null',
            'message_type' => 53,
            'data' => [
                'buyerId' => TenantFactory::SECOND_DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
                'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
                'orderStatus' => 'OrderCreated',
                'sellerId' => TenantFactory::SECOND_DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            ],
            'timestamp' => 1730455911,
            'site' => 'ae_global',
        ];

        $this->makePostRequest(
            method: self::METHOD,
            data: $data
        );

        self::assertResponseErrors(['common' => 'Service is unavailable.'], 422);
    }

    public function testSyncOrderStatusWebhookWithNonExistSellerReturns400(): void
    {
        $data = [
            'seller_id' => 'null',
            'message_type' => 53,
            'data' => [
                'buyerId' => TenantFactory::NON_EXIST_ALIEXPRESS_SELLER_ID,
                'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
                'orderStatus' => 'OrderCreated',
                'sellerId' => TenantFactory::NON_EXIST_ALIEXPRESS_SELLER_ID,
            ],
            'timestamp' => 1730455911,
            'site' => 'ae_global',
        ];

        $this->makePostRequest(
            method: self::METHOD,
            data: $data
        );

        self::assertResponseNotFound('Seller does not exist on platform.');
    }

    public function testSyncOrderStatusWebhookWithNonExistOrderReturns400(): void
    {
        $data = [
            'seller_id' => 'null',
            'message_type' => 53,
            'data' => [
                'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
                'orderId' => DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID,
                'orderStatus' => 'OrderCreated',
                'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            ],
            'timestamp' => 1730455911,
            'site' => 'ae_global',
        ];

        $this->makePostRequest(
            method: self::METHOD,
            data: $data
        );

        self::assertResponseNotFound('DSOrder not found.');
    }
}
