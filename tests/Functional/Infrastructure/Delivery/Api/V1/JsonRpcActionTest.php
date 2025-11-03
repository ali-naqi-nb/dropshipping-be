<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\RpcTestBootTrait;
use App\Tests\Shared\Trait\UsersHeadersTrait;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

final class JsonRpcActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;
    use RpcTestBootTrait;

    protected const ROUTE = '/dropshipping/v1/jsonrpc';
    protected const METHOD = 'POST';

    protected const SERVICE = 'dropshipping';

    protected function setUp(): void
    {
        parent::setUp();
        parent::createDoctrineTenantConnection();

        $this->setUserHeaders();
    }

    /**
     * @dataProvider provideSuccessData
     *
     * @throws ReflectionException
     */
    public function testJsonRpcReturnSuccess(array $data, ?array $expectedResponse, int $statusCode = Response::HTTP_OK): void
    {
        $this->emulateRpcCommand(self::SERVICE, $data['method'], $data['params'] ?? []);

        $this->makeTenantRequest(method: self::METHOD, data: $data);

        $this->assertResponseStatusCodeSame($statusCode);

        if (null === $expectedResponse) {
            $this->assertNull($this->getDecodedJsonResponse());
        } else {
            $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
        }
    }

    /**
     * @dataProvider provideErrorData
     *
     * @throws ReflectionException
     */
    public function testJsonRpcReturnError(array $data, array $expectedResponse): void
    {
        $this->emulateRpcCommand(self::SERVICE, $data['method'], $data['params'] ?? []);

        $this->makeTenantRequest(method: self::METHOD, data: $data);
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
    }

    public function testJsonRpcReturnBadRequest(): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: ['jsonrpc' => '1.0', 'method' => 'getSingleApp']);
        $this->assertResponseErrors(['message' => 'Bad Request']);
    }

    public function provideSuccessData(): array
    {
        return [
            'testNotification' => [
                [
                    'jsonrpc' => '2.0',
                    'method' => 'notificationCommand',
                ],
                null,
                Response::HTTP_NO_CONTENT,
            ],
            'getOrdersBySource' => [
                [
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                    'jsonrpc' => '2.0',
                    'method' => 'getOrdersBySource',
                    'tenantId' => TenantFactory::TENANT_ID,
                    'params' => [
                        [
                            'tenantId' => TenantFactory::TENANT_ID,
                            'source' => DsOrderMappingFactory::FIRST_ORDER_DS_PROVIDER,
                        ],
                    ],
                ],
                [
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                    'jsonrpc' => '2.0',
                    'result' => [DsOrderMappingFactory::DS_ORDER_RESPONSE_PATTERN],
                ],
                Response::HTTP_OK,
            ],
        ];
    }

    public function provideErrorData(): array
    {
        return [
            'commandNotFound' => [
                [
                    'jsonrpc' => '2.0',
                    'method' => 'invalidCommand',
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32601,
                        'message' => 'Command not found',
                    ],
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
            ],
            'invalidParameters' => [
                [
                    'jsonrpc' => '2.0',
                    'method' => 'testInvalidParams',
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32602,
                        'message' => 'Missing required parameters: param',
                    ],
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
            ],
            'invalidRequestException' => [
                [
                    'jsonrpc' => '2.0',
                    'method' => 'testInvalidRequest',
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
                [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32600,
                        'message' => 'Invalid request',
                        'data' => [],
                    ],
                    'id' => 'cff92407-d772-447f-af6b-d53722361948',
                ],
            ],
        ];
    }
}
