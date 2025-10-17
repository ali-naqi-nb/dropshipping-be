<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Exception\RequestBodyParsingException;
use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use JsonException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestSubscriber implements EventSubscriberInterface
{
    private const HEADER_CORRELATION_ID = 'kong-request-id';

    public function __construct(
        private readonly CorrelationIdStorageInterface $correlationIdStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    /**
     * @throws RequestBodyParsingException|JsonException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->parseBody($request);

        $correlationId = $request->headers->get(self::HEADER_CORRELATION_ID);
        if (null !== $correlationId) {
            $this->correlationIdStorage->setCorrelationId($correlationId);
        }
    }

    /**
     * @throws JsonException
     */
    private function parseBody(Request $request): void
    {
        if ('json' === $request->getContentTypeFormat() && $request->getContent()) {
            $json = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (is_array($json)) {
                $request->request->replace($json);
            }
        }
    }
}
