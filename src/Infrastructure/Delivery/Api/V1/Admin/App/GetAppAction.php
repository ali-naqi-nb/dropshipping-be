<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Application\Query\App\Get\GetAppQuery;
use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetAppAction
{
    public function __construct(
        private readonly QueryBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    #[OA\Get(
        path: '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}',
        summary: 'Get App',
        security: [['bearerAuth' => []]],
        tags: ['Admin Apps'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantIdInPath'),
            new OA\Parameter(ref: '#/components/parameters/appId'),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/SingleAppResponse', response: Response::HTTP_OK),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'List of 422 errors.',
                content: new OA\JsonContent(
                    examples: [
                        'notSupportedApp' => new OA\Examples(
                            example: 'notSupportedApp',
                            summary: 'App is not supported.',
                            value: ['errors' => ['appId' => 'App "not-supported-app-name" is not supported.']]
                        ),
                    ],
                    ref: '#/components/schemas/ErrorSchema'
                )
            ),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route('/tenants/{tenantId}/apps/{appId}', name: 'get_single_app', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = $this->requestMapper->fromAttributes($request, GetAppQuery::class);

        return $this->responseMapper->serializeResponse($this->bus->ask($query));
    }
}
