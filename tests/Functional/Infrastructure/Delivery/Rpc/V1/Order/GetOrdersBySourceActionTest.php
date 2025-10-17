<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Order;

use App\Application\Query\Order\GetBySource\GetOrdersBySourceQuery;
use App\Domain\Model\Order\DsProvider;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\RpcFunctionalTestCase;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Shared\Factory\TenantFactory;
use DateTime;

final class GetOrdersBySourceActionTest extends RpcFunctionalTestCase
{
    protected const COMMAND = RpcCommandFactory::GET_ORDERS_BY_SOURCE;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDoctrineTenantConnection();
    }

    public function testGetOrdersBySourceReturnSuccess(): void
    {
        $this->prepareMockRpcResponse();

        $query = new GetOrdersBySourceQuery(
            tenantId: TenantFactory::TENANT_ID,
            source: DsProvider::AliExpress->value
        );

        $response = $this->call([$query]);

        $this->assertSame(RpcResultStatus::SUCCESS, $response->getStatus());

        /** @var array $result */
        $result = $response->getResult();

        $this->assertNotEmpty($result);
        $this->assertSame(DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID, $result[0]['nbOrderId']);
        $this->assertSame(DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID, $result[0]['dsOrderId']);
        $this->assertSame(DsProvider::AliExpress->value, $result[0]['dsProvider']);
    }

    public function testGetOrdersBySourceReturnErrorForInvalidTenant(): void
    {
        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) {
                if ('dropshipping.getOrdersBySource' !== $rpcCommand->getCommand()) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(
                status: RpcResultStatus::ERROR,
                result: ['message' => 'Tenant not found']
            ),
        );

        $query = new GetOrdersBySourceQuery(
            tenantId: TenantFactory::NON_EXISTING_TENANT_ID,
            source: DsProvider::AliExpress->value
        );

        $response = $this->call([$query]);

        $this->assertSame(RpcResultStatus::ERROR, $response->getStatus());

        /** @var array $result */
        $result = $response->getResult();

        $this->assertSame('Tenant not found', $result['message']);
    }

    public function testGetOrdersBySourceReturnErrorForInvalidProvider(): void
    {
        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) {
                if ('dropshipping.getOrdersBySource' !== $rpcCommand->getCommand()) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(
                status: RpcResultStatus::ERROR,
                result: ['message' => 'Provider not found']
            ),
        );

        $query = new GetOrdersBySourceQuery(
            tenantId: TenantFactory::TENANT_ID,
            source: 'invalid-provider'
        );

        $response = $this->call([$query]);

        $this->assertSame(RpcResultStatus::ERROR, $response->getStatus());

        /** @var array $result */
        $result = $response->getResult();

        $this->assertSame('Provider not found', $result['message']);
    }

    public function prepareMockRpcResponse(): void
    {
        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) {
                if ('dropshipping.getOrdersBySource' !== $rpcCommand->getCommand()) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(
                result: [
                    [
                        'id' => DsOrderMappingFactory::NEW_ORDER_ID,
                        'nbOrderId' => DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID,
                        'dsOrderId' => DsOrderMappingFactory::NEW_ORDER_DS_ORDER_ID,
                        'dsProvider' => DsProvider::AliExpress->value,
                        'dsStatus' => null,
                        'createdAt' => new DateTime(),
                        'updatedAt' => new DateTime(),
                    ],
                ],
            ),
        );
    }
}
