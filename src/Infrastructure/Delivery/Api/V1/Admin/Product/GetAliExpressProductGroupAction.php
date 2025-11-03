<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Application\Query\Product\Get\GetAliExpressProductGroupQuery;
use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetAliExpressProductGroupAction
{
    public function __construct(
        private readonly QueryBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    #[OA\Get(
        path: '/dropshipping/admin/v1/{_locale}/aliexpress-product-group/{id}',
        summary: 'Get AliExpress Product Group Progress',
        security: [['bearerAuth' => []]],
        tags: ['Admin Product'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantId'),
            new OA\Parameter(ref: '#/components/parameters/id', required: true),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/AeProductGroupResponse', response: Response::HTTP_OK),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route('/aliexpress-product-group/{id}', name: 'get_aliexpress_product_group', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = $this->requestMapper->fromAttributes($request, GetAliExpressProductGroupQuery::class);

        return $this->responseMapper->serializeResponse($this->bus->ask($query));
    }
}
