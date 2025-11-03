<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Application\Command\App\RefreshToken\RefreshTokenAppCommand;
use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RefreshTokenAppAction
{
    public function __construct(
        private readonly CommandBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    #[OA\Post(
        path: '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/refresh-token',
        summary: 'Refresh App Token',
        security: [['bearerAuth' => []]],
        tags: ['Admin Apps'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantIdInPath'),
            new OA\Parameter(ref: '#/components/parameters/appId'),
        ],
        responses: [
            new OA\Response(response: Response::HTTP_OK, description: 'Token was refreshed successfully.'),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'List of 422 errors.',
                content: new OA\JsonContent(
                    examples: [
                        'notSupportedApp' => new OA\Examples(
                            example: 'notSupportedApp',
                            summary: 'App is not supported.',
                            value: ['errors' => ['appId' => 'App "not-supported-app-name" is not supported for refresh-token.']]
                        ),
                        'appNotInstalledOrNotActive' => new OA\Examples(
                            example: 'appNotInstalledOrNotActive',
                            summary: 'The app is\'t installed.',
                            value: ['errors' => ['appId' => 'App "ali-express" is either not installed or not active.']]
                        ),
                    ],
                    ref: '#/components/schemas/ErrorSchema'
                )
            ),
            new OA\Response(ref: '#/components/responses/InternalServerErrorResponse', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    #[Route('/tenants/{tenantId}/apps/{appId}/refresh-token', name: 'refresh_app_token', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $command = $this->requestMapper->fromRequest($request, RefreshTokenAppCommand::class);

        return $this->responseMapper->serializeResponse($this->bus->dispatch($command));
    }
}
