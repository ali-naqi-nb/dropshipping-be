<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1;

use OpenApi\Annotations\OpenApi;
use OpenApi\Attributes as OA;
use OpenApi\Generator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Info(
    version: '0.1',
    title: 'NEXT BASKET Dropshipping manager service',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Auth Token (JWT)',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
final class GetDocumentationAction
{
    public function __construct(private readonly string $projectDir)
    {
    }

    #[Route(path: '/docs', name: 'docs', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var OpenApi $oa */
        $oa = Generator::scan([$this->projectDir.'/src']);
        $oaJson = $oa->toJson();
        /**
         * This is needed because of a bug in swagger lib (It generates schema property under additionalProperties
         * which leads to invalid schema error in swagger editor).
         *
         * @var string $oaJson
         */
        $oaJson = preg_replace('/\s*"schema": "ErrorSchema",/', '', $oaJson);

        return JsonResponse::fromJsonString($oaJson, Response::HTTP_OK);
    }
}
