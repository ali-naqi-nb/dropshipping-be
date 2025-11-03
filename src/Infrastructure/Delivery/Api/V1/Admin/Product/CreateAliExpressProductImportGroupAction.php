<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Application\Command\Product\AliExpressProductImport\CreateAliExpressProductGroupCommand;
use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreateAliExpressProductImportGroupAction
{
    public function __construct(
        private readonly CommandBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    #[OA\Post(
        path: '/dropshipping/admin/v1/{_locale}/aliexpress-product-group',
        summary: 'Create AliExpress Product Group',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'JSON payload',
            required: true,
            content: new OA\JsonContent(
                required: ['products'],
                properties: [
                    new OA\Property(
                        property: 'products',
                        type: 'array',
                        items: new OA\Items(
                            description: 'Product data',
                            required: ['aeProductId', 'aeSkuId', 'name', 'description', 'sku', 'price', 'mainCategoryId', 'additionalCategories', 'stock', 'barcode', 'weight', 'length', 'width', 'height', 'costPerItem', 'productTypeName', 'attributes', 'images'],
                            properties: [
                                new OA\Property(property: 'aeProductId', type: 'int'),
                                new OA\Property(property: 'aeSkuId', type: 'int'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'sku', type: 'string'),
                                new OA\Property(property: 'price', type: 'int'),
                                new OA\Property(property: 'stock', type: 'int'),
                                new OA\Property(property: 'mainCategoryId', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'additionalCategories', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                                new OA\Property(property: 'barcode', type: 'string'),
                                new OA\Property(property: 'weight', type: 'int'),
                                new OA\Property(property: 'length', type: 'int'),
                                new OA\Property(property: 'height', type: 'int'),
                                new OA\Property(property: 'width', type: 'int'),
                                new OA\Property(property: 'costPerItem', type: 'int'),
                                new OA\Property(property: 'productTypeName', type: 'string'),
                                new OA\Property(property: 'attributes', type: 'array', items: new OA\Items(
                                    required: ['name', 'type', 'value'],
                                    properties: [
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'type', type: 'string'),
                                        new OA\Property(property: 'value', type: 'string'),
                                    ]
                                )),
                                new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')),
                            ]
                        )
                    ),
                ],
            )
        ),
        tags: ['Admin Product'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantId'),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/AeProductGroupResponse', response: Response::HTTP_CREATED),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route('/aliexpress-product-group', name: 'create_aliexpress_product_group', methods: ['POST', 'OPTIONS'])]
    public function __invoke(Request $request): Response
    {
        /** @var CreateAliExpressProductGroupCommand $command */
        $command = $this->requestMapper->fromBody($request, CreateAliExpressProductGroupCommand::class);

        return $this->responseMapper->serializeResponse($this->bus->dispatch($command), Response::HTTP_CREATED);
    }
}
