<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http;

use App\Infrastructure\Http\ResponseSubscriber;
use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use App\Tests\Unit\UnitTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseSubscriberTest extends UnitTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([KernelEvents::RESPONSE => 'onKernelResponse'], ResponseSubscriber::getSubscribedEvents());
    }

    public function testOnKernelResponseLogResponse(): void
    {
        $requestMock = $this->createMock(Request::class);

        $responseMock = $this->createMock(Response::class);
        $headersMock = $this->createMock(ResponseHeaderBag::class);
        $headersMock->expects($this->once())
            ->method('set')
            ->with('X-Correlation-Id', CorrelationIdFactory::CORRELATION_ID);
        $responseMock->headers = $headersMock;

        $responseEventMock = $this->createMock(ResponseEvent::class);
        $responseEventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);
        $responseEventMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($responseMock);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info');

        $correlationStorageMock = $this->createMock(CorrelationIdStorageInterface::class);
        $correlationStorageMock->expects($this->once())
            ->method('getCorrelationId')
            ->willReturn(CorrelationIdFactory::CORRELATION_ID);

        $subscriber = new ResponseSubscriber($loggerMock, $correlationStorageMock);
        $subscriber->onKernelResponse($responseEventMock);
    }
}
