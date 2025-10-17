<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use App\Infrastructure\Http\ExceptionSubscriber;
use App\Tests\Integration\IntegrationTestCase;
use Exception;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ExceptionSubscriberTest extends IntegrationTestCase
{
    private ExceptionSubscriber $exceptionSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ExceptionSubscriber $exceptionSubscriber */
        $exceptionSubscriber = self::getContainer()->get(ExceptionSubscriber::class);
        $this->exceptionSubscriber = $exceptionSubscriber;
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onKernelException'], ExceptionSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider provideNotFoundExceptions
     */
    public function testOnKernelExceptionWithNotFoundException(Throwable $exception, int $statusCode, string $message): void
    {
        $event = $this->getKernelExceptionEvent($exception);
        $this->exceptionSubscriber->onKernelException($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertSame($statusCode, $response->getStatusCode());
        $expectedResponse = ['message' => $message];
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse($response));
    }

    private function getDecodedJsonResponse(Response $response): array
    {
        return json_decode((string) $response->getContent(), true);
    }

    private function getKernelExceptionEvent(Throwable $exception): ExceptionEvent
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = self::getContainer()->get(HttpKernelInterface::class);

        return new ExceptionEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    public function provideNotFoundExceptions(): array
    {
        return [
            'notFound' => [new NotFoundHttpException(), 404, 'Not found'],
            'methodNotAllowed' => [new MethodNotAllowedHttpException([]), 405, 'Method not allowed'],
        ];
    }

    public function provideUnhandledExceptions(): array
    {
        return [
            'json' => [new JsonException(), 400, 'Bad Request'],
            'internalServerError' => [new Exception(), 500, 'Internal Server Error'],
            'accessDenied' => [new AccessDeniedHttpException(code: 403), 403, 'Forbidden'],
        ];
    }
}
