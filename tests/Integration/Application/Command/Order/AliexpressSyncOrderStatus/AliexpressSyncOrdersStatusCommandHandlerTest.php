<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\Order\AliexpressSyncOrderStatus;

use App\Application\Command\Order\AliexpressSyncOrderStatus\AliexpressSyncOrdersStatusCommand;
use App\Application\Command\Order\AliexpressSyncOrderStatus\AliexpressSyncOrdersStatusCommandHandler;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsOrderStatusUpdated;
use App\Domain\Model\Order\ProcessingStatus;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Random\Generator;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Uid\Uuid;

final class AliexpressSyncOrdersStatusCommandHandlerTest extends IntegrationTestCase
{
    private AliexpressSyncOrdersStatusCommandHandler $handler;
    private DsOrderMappingRepositoryInterface $dsOrderMappingRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AliexpressSyncOrdersStatusCommandHandler $handler */
        $handler = self::getContainer()->get(AliexpressSyncOrdersStatusCommandHandler::class);
        $this->handler = $handler;

        /** @var DsOrderMappingRepositoryInterface $dsOrderMappingRepository */
        $dsOrderMappingRepository = self::getContainer()->get(DsOrderMappingRepositoryInterface::class);
        $this->dsOrderMappingRepository = $dsOrderMappingRepository;
    }

    public function testInvokeWithInvalidSellerReturnsErrorResponse(): void
    {
        $data = [
            'buyerId' => TenantFactory::NON_EXIST_ALIEXPRESS_SELLER_ID,
            'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
            'orderStatus' => 'OrderCreated',
            'sellerId' => TenantFactory::NON_EXIST_ALIEXPRESS_SELLER_ID,
        ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
        $this->assertSame('Seller does not exist on platform.', $response->getErrors()['message']);
    }

    public function testInvokeWithUnavailableTenantReturnsErrorResponse(): void
    {
        $data = [
            'buyerId' => TenantFactory::SECOND_DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
            'orderStatus' => 'OrderCreated',
            'sellerId' => TenantFactory::SECOND_DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
        ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(['common' => 'Service is unavailable.'], $response->getErrors());
    }

    public function testInvokeWithInvalidDsOrderIdReturnsErrorResponse(): void
    {
        $data = [
            'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            'orderId' => DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID,
            'orderStatus' => 'OrderCreated',
            'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
        ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
        $this->assertSame('DSOrder not found.', $response->getErrors()['message']);
    }

    /**
     * @dataProvider provideAliexpressStatus
     */
    public function testInvokeWorksAndDispatchDsOrderStatusUpdatedEventStatus(string $nbOrderStatus, ProcessingStatus $processingStatus): void
    {
        $data = [
            'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
            'orderStatus' => $nbOrderStatus,
            'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
        ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertNull($response);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_ds_order_status_updated');
        $this->assertCount(1, $transport->getSent());
        $message = $transport->getSent()[0]->getMessage();
        $this->assertInstanceOf(DsOrderStatusUpdated::class, $message);
        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_NB_ORDER_ID, $message->getNbOrderId());
        $this->assertSame($processingStatus->value, $message->getNbOrderStatus()->value);
    }

    /**
     * @dataProvider provideAliexpressStatusCombinations
     */
    public function testInvokeWithExistingCombinationsWorksAndDispatchDsOrderStatusUpdatedEventStatus(
        string $currentDsStatusUpdate,
        array $availableStatuses,
        ProcessingStatus $expectedProcessingStatus
    ): void {
        self::createRedisTenantConnection(tenantId: TenantFactory::DS_AUTHORISED_TENANT_ID);
        $connection = self::createDoctrineTenantConnection();
        $this->createDsOrderMappings($availableStatuses);

        $data = [
            'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            'orderId' => DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID,
            'orderStatus' => $currentDsStatusUpdate,
            'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
        ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertNull($response);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_ds_order_status_updated');
        $this->assertCount(1, $transport->getSent());
        $message = $transport->getSent()[0]->getMessage();
        $this->assertInstanceOf(DsOrderStatusUpdated::class, $message);
        $this->assertSame(DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID, $message->getNbOrderId());
        $this->assertSame($expectedProcessingStatus->value, $message->getNbOrderStatus()->value);
    }

    public function testInvokeWorksAndDoesnotDispatchDsOrderStatusUpdatedEvent(): void
    {
        $data = [
                'buyerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
                'orderId' => DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
                'orderStatus' => 'OrderConsumed',
                'sellerId' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            ];

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: 53,
            seller_id: null,
            site: 'ae_global',
            timestamp: 1730455911,
        );

        $response = $this->handler->__invoke($command);
        $this->assertNull($response);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_ds_order_status_updated');
        $this->assertCount(0, $transport->getSent());
    }

    public function provideAliexpressStatus(): array
    {
        return [
            'paymentFailedEvent' => ['paymentFailedEvent', ProcessingStatus::New],
            'OrderCreated' => ['OrderCreated', ProcessingStatus::New],
            'OrderClosed' => ['OrderClosed', ProcessingStatus::Canceled],
            'PaymentAuthorized' => ['PaymentAuthorized', ProcessingStatus::Processing],
            'OrderShipped' => ['OrderShipped', ProcessingStatus::Shipped],
            'OrderConfirmed' => ['OrderConfirmed', ProcessingStatus::Delivered],
        ];
    }

    public function provideAliexpressStatusCombinations(): array
    {
        return [
            'new1' => [
                'paymentFailedEvent',
                ['new', 'new', 'new', 'processing', 'canceled'],
                ProcessingStatus::New,
            ],
            'new2' => [
                'paymentFailedEvent',
                ['delivered', 'delivered', 'delivered', 'delivered', 'delivered'],
                ProcessingStatus::New,
            ],
            'processing1' => [
                'PaymentAuthorized',
                ['delivered', 'delivered', 'canceled', 'delivered', 'delivered'],
                ProcessingStatus::Processing,
            ],
            'processing2' => [
                'PaymentAuthorized',
                ['delivered', 'canceled', 'delivered', 'shipped', 'delivered'],
                ProcessingStatus::Processing,
            ],
            'shipped1' => [
                'OrderShipped',
                ['shipped', 'delivered', 'delivered', 'delivered', 'delivered'],
                ProcessingStatus::Shipped,
            ],
            'shipped2' => [
                'OrderShipped',
                ['shipped', 'delivered', 'delivered', 'delivered', 'delivered'],
                ProcessingStatus::Shipped,
            ],
            'delivered' => [
                'OrderConfirmed',
                ['delivered', 'delivered', 'delivered', 'delivered', 'canceled'],
                ProcessingStatus::Delivered,
            ],
            'canceled' => [
                'OrderClosed',
                ['canceled', 'canceled', 'canceled', 'canceled', 'canceled'],
                ProcessingStatus::Canceled,
            ],
        ];
    }

    private function createDsOrderMappings(array $statuses): void
    {
        foreach ($statuses as $status) {
            $dsOrderMapping = new DsOrderMapping(
                id: Uuid::v4()->toRfc4122(),
                nbOrderId: DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID,
                dsOrderId: Generator::string(),
                dsProvider: DsOrderMappingFactory::FIRST_ORDER_DS_PROVIDER,
                dsStatus: $status
            );
            $this->dsOrderMappingRepository->save($dsOrderMapping);
        }

        $dsOrderMapping = new DsOrderMapping(
            id: Uuid::v4()->toRfc4122(),
            nbOrderId: DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID,
            dsOrderId: DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID,
            dsProvider: DsOrderMappingFactory::FIRST_ORDER_DS_PROVIDER,
            dsStatus: null
        );
        $this->dsOrderMappingRepository->save($dsOrderMapping);
    }
}
