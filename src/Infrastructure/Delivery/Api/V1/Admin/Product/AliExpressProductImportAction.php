<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Application\Command\Product\AliExpressProductImport\AliExpressProductImportCommand;
use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use OpenApi\Attributes as OA;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class AliExpressProductImportAction
{
    public function __construct(
        private readonly CommandBusInterface $bus,
        private readonly RequestMapper $requestMapper,
        private readonly ResponseMapper $responseMapper
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    #[OA\Post(
        path: '/dropshipping/admin/v1/{_locale}/aliexpress-product-import',
        summary: 'AliExpress Product Import',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'JSON payload',
            required: true,
            content: new OA\JsonContent(
                required: ['aeProductUrl', 'aeProductShipsTo'],
                properties: [
                    new OA\Property(property: 'aeProductUrl', type: 'string'),
                    new OA\Property(property: 'aeProductShipsTo', type: 'string'),
                ],
            )
        ),
        tags: ['Admin Product'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/_locale'),
            new OA\Parameter(ref: '#/components/parameters/tenantId'),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/AliExpressProductImportResponse', response: Response::HTTP_CREATED),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'List of 422 errors.',
                content: new OA\JsonContent(
                    examples: [
                        'failedFetchingAeDetails' => new OA\Examples(
                            example: 'failedFetchingAeDetails',
                            summary: 'Failed to get product information from AliExpress.',
                            value: ['errors' => ['common' => 'Failed to get product information from AliExpress.']]
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
    #[Route('/aliexpress-product-import', name: 'aliexpress_product_import', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var AliExpressProductImportCommand $command */
        $command = $this->requestMapper->fromBody($request, AliExpressProductImportCommand::class);

        return $this->responseMapper->serializeResponse($this->bus->dispatch($command), Response::HTTP_CREATED);
    }
}
