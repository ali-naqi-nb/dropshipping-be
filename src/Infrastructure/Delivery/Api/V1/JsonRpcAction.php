<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1;

use App\Infrastructure\Delivery\Api\V1\Documentation\Schema\JsonRpc\JsonRpcRequestSchema;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Client\RpcCommandClient;
use App\Infrastructure\Rpc\Exception\RpcException;
use App\Infrastructure\Rpc\JsonRpcErrorResponse;
use App\Infrastructure\Rpc\JsonRpcRequest;
use App\Infrastructure\Rpc\JsonRpcSuccessResponse;
use OpenApi\Attributes as OA;
use ReflectionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

final class JsonRpcAction
{
    public const RPC_VERSION = '2.0';
    public const RESPONSE_FORMAT = 'json';

    public function __construct(
        private readonly string $appServiceName,
        private readonly RequestMapper $requestMapper,
        private readonly RpcCommandClient $commandClient,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    #[OA\Post(
        path: '/dropshipping/v1/jsonrpc',
        summary: 'JSON RPC',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'JSON payload',
            required: true,
            content: new OA\JsonContent(
                examples: [
                    'dsProductTypeImported' => new OA\Examples(
                        example: 'dsProductTypeImported',
                        summary: 'DS product type imported',
                        value: JsonRpcRequestSchema::EXAMPLE_DS_PRODUCT_TYPE_IMPORTED,
                    ),
                    'dsProductImagesImported' => new OA\Examples(
                        example: 'dsProductImagesImported',
                        summary: 'DS product images imported',
                        value: JsonRpcRequestSchema::EXAMPLE_DS_PRODUCT_IMAGES_IMPORTED,
                    ),
                    'dsProductGroupImported' => new OA\Examples(
                        example: 'dsProductGroupImported',
                        summary: 'DS Product Group Imported',
                        value: JsonRpcRequestSchema::EXAMPLE_DS_PRODUCT_GROUP_IMPORTED,
                    ),
                    'DsAttributesImported' => new OA\Examples(
                        example: 'DsAttributesImported',
                        summary: 'DS Attributes Import',
                        value: JsonRpcRequestSchema::EXAMPLE_DS_ATTRIBUTES_IMPORTED,
                    ),
                    'dsProductImagesUpdated' => new OA\Examples(
                        example: 'dsProductImagesUpdated',
                        summary: 'DS Product Images Updated',
                        value: JsonRpcRequestSchema::EXAMPLE_DS_PRODUCT_IMAGES_UPDATED,
                    ),
                ],
                ref: '#/components/schemas/JsonRpcRequestSchema',
            ),
        ),
        tags: ['JSON RPC'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/tenantId'),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/JsonRpcResponse', response: Response::HTTP_OK),
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Notification request response'),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route(path: '/jsonrpc', name: 'jsonrpc', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var JsonRpcRequest $rpcRequest */
        $rpcRequest = $this->requestMapper->fromRequest($request, JsonRpcRequest::class);

        if (self::RPC_VERSION !== $rpcRequest->getJsonrpc()) {
            throw new BadRequestHttpException('Unsupported JSON RPC version');
        }

        $response = null;

        try {
            $rpcResult = $this->commandClient->call(
                $this->appServiceName,
                $rpcRequest->getMethod(),
                $rpcRequest->getParams() ?? [],
            );

            if (null !== $rpcRequest->getId()) {
                $response = JsonRpcSuccessResponse::fromRpcResult($rpcRequest->getId(), $rpcResult);
            }
        } catch (RpcException $exception) {
            if (null !== $rpcRequest->getId()) {
                $response = JsonRpcErrorResponse::fromRpcException($rpcRequest->getId(), $exception);
            }
        }

        return JsonResponse::fromJsonString(
            data: $this->serializer->serialize(
                data: $response,
                format: self::RESPONSE_FORMAT,
                context: [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
            status: null === $response ? Response::HTTP_NO_CONTENT : Response::HTTP_OK
        );
    }
}
