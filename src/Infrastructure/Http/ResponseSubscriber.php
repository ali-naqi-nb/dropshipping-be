<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CorrelationIdStorageInterface $correlationIdStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $response->headers->set('X-Correlation-Id', $this->correlationIdStorage->getCorrelationId());

        $this->logger->info(
            'Request completed : '.$response->getStatusCode().' '.$request->getMethod().' '.$request->getUri(),
            [
                'statusCode' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ],
        );
    }
}
