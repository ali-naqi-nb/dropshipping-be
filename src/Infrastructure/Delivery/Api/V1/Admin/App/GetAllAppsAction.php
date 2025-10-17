<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Application\Query\App\GetAll\GetAllAppsQuery;
use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetAllAppsAction
{
    public function __construct(
        private readonly QueryBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    #[OA\Get(
        path: '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps',
        summary: 'Get All Apps',
        security: [['bearerAuth' => []]],
        tags: ['Admin Apps'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantIdInPath'),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/AppListResponse', response: Response::HTTP_OK),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route(path: '/tenants/{tenantId}/apps', name: 'get_all_apps', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var GetAllAppsQuery $query */
        $query = $this->requestMapper->fromAttributes($request, GetAllAppsQuery::class);

        return $this->responseMapper->serializeResponse($this->bus->ask($query));
    }
}
