<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Application\Command\App\Delete\DeleteAppCommand;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteAppAction
{
    use HandleTrait;

    private readonly RequestMapper $requestMapper;
    private readonly ResponseMapper $responseMapper;

    public function __construct(
        MessageBusInterface $commandBus,
        RequestMapper $requestMapper,
        ResponseMapper $responseMapper
    ) {
        $this->messageBus = $commandBus;
        $this->requestMapper = $requestMapper;
        $this->responseMapper = $responseMapper;
    }

    #[OA\Delete(
        path: '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}',
        summary: 'Delete App',
        security: [['bearerAuth' => []]],
        tags: ['Admin Apps'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantIdInPath'),
            new OA\Parameter(ref: '#/components/parameters/appId'),
        ],
        responses: [
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'App was deleted successfully.'),
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
    #[Route('/tenants/{tenantId}/apps/{appId}', name: 'uninstall_app', methods: ['DELETE'])]
    public function __invoke(Request $request): JsonResponse
    {
        $command = $this->requestMapper->fromRequest($request, DeleteAppCommand::class);

        return $this->responseMapper->serializeResponse($this->handle($command), Response::HTTP_NO_CONTENT);
    }
}
