<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http;

use App\Infrastructure\Http\RequestSubscriber;
use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use App\Tests\Unit\UnitTestCase;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestSubscriberTest extends UnitTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([KernelEvents::REQUEST => 'onKernelRequest'], RequestSubscriber::getSubscribedEvents());
    }

    public function testOnKernelRequestSaveCorrelationIdHeader(): void
    {
        $request = new Request();
        $request->headers->set('Kong-Request-Id', CorrelationIdFactory::CORRELATION_ID);

        $correlationIdStorage = $this->createMock(CorrelationIdStorageInterface::class);

        $correlationIdStorage
            ->expects($this->once())
            ->method('setCorrelationId')
            ->with(CorrelationIdFactory::CORRELATION_ID);

        $requestEventMock = $this->createMock(RequestEvent::class);

        $requestEventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber = new RequestSubscriber($correlationIdStorage);
        $subscriber->onKernelRequest($requestEventMock);
    }

    public function testOnKernelRequestParseBody(): void
    {
        $request = new Request(server: ['CONTENT_TYPE' => 'application/json'], content: '{"field": "value"}');

        $this->assertNull($request->request->get('field'));

        $requestEventMock = $this->createMock(RequestEvent::class);

        $requestEventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $correlationIdStorage = $this->createMock(CorrelationIdStorageInterface::class);

        $subscriber = new RequestSubscriber($correlationIdStorage);

        $subscriber->onKernelRequest($requestEventMock);

        $this->assertSame('value', $request->request->get('field'));
    }

    public function testOnKernelRequestThrowsTranslatedException(): void
    {
        $correlationIdStorage = $this->createMock(CorrelationIdStorageInterface::class);

        $content = '{test: "invalid"}';

        $request = new Request(server: ['CONTENT_TYPE' => 'application/json'], content: $content);

        $requestEventMock = $this->createMock(RequestEvent::class);

        $requestEventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber = new RequestSubscriber($correlationIdStorage);

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error');

        $subscriber->onKernelRequest($requestEventMock);
    }
}
