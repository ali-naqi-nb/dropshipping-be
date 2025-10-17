<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use App\Application\Shared\Error\ErrorResponse;
use App\Infrastructure\Http\ResponseMapper;
use App\Tests\Shared\Factory\DateTimeFactory;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ResponseMapperTest extends KernelTestCase
{
    private ResponseMapper $responseMapper;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ResponseMapper $responseMapper */
        $responseMapper = self::getContainer()->get(ResponseMapper::class);
        $this->responseMapper = $responseMapper;
    }

    public function testSerializeResponseWithSuccessfulResponse(): void
    {
        $string = 'Dummy';
        $array = [1, 2, 3];
        $dateTime = new DateTime();
        $int = 100;
        $float = 100.01;

        $data = new DummyResponse($string, $array, $dateTime, $int, $float, true, null);
        $expectedResponse = [
            'string' => $string,
            'array' => $array,
            'dateTime' => $dateTime->format(DateTimeFactory::DATE_TIME_FORMAT),
            'int' => $int,
            'float' => $float,
            'isTrue' => true,
            'nullableString' => null,
        ];
        $response = $this->responseMapper->serializeResponse($data, 201);

        $this->assertEquals(new JsonResponse(['data' => $expectedResponse], 201), $response);
    }

    public function testSerializeErrorResponse(): void
    {
        $response = $this->responseMapper->serializeErrorResponse(ErrorResponse::fromCommonError('Test Error'));

        $this->assertEquals(new JsonResponse(['errors' => ['common' => 'Test Error']], 422), $response);
    }

    public function testSerializeResponseWithErrorResponse(): void
    {
        $response = $this->responseMapper->serializeResponse(ErrorResponse::fromCommonError('Test Error'));

        $this->assertEquals(new JsonResponse(['errors' => ['common' => 'Test Error']], 422), $response);
    }

    public function testSerializeResponseWithNull(): void
    {
        $response = $this->responseMapper->serializeResponse(null, 204);

        $this->assertEquals(new JsonResponse(null, 204), $response);
    }

    public function testSerializeNotFoundErrorResponse(): void
    {
        $response = $this->responseMapper->serializeErrorResponse(ErrorResponse::notFound());

        $this->assertEquals(new JsonResponse(['message' => 'Not Found'], 404), $response);
    }

    public function testSerializeErrorResponseWithBasePathForRemoval(): void
    {
        $response = $this->responseMapper->serializeErrorResponse(
            ErrorResponse::fromError('Test Error', 'base.common'),
            'base.'
        );

        $this->assertEquals(new JsonResponse(['errors' => ['common' => 'Test Error']], 422), $response);
    }
}
