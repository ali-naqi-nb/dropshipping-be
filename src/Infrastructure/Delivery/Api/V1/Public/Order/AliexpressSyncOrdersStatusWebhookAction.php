<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Public\Order;

use App\Application\Command\Order\AliexpressSyncOrderStatus\AliexpressSyncOrdersStatusCommand;
use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AliexpressSyncOrdersStatusWebhookAction
{
    public function __construct(
        private readonly CommandBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper,
    ) {
    }

    #[OA\Post(
        path: '/dropshipping/v1/{_locale}/aliexpress/orders/webhook',
        description: 'Handle order status updates',
        summary: 'Webhook',
        requestBody: new OA\RequestBody(
            description: 'JSON',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                )
            )
        ),
        tags: ['Public Aliexpress Orders'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: 'JSON',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/BadRequestErrorResponse', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: Response::HTTP_NOT_FOUND),
        ]
    )]
    #[Route('/aliexpress/orders/webhook', name: 'sync_orders_status_webhook', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $command = $this->requestMapper->fromRequest($request, AliexpressSyncOrdersStatusCommand::class);

        return $this->responseMapper->serializeResponse($this->bus->dispatch($command));
    }
}
