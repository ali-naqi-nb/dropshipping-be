<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    private readonly LoggerInterface $logger;

    /**
     * ExceptionListener constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof MethodNotAllowedHttpException) {
            $code = Response::HTTP_METHOD_NOT_ALLOWED;
            $message = 'Method not allowed';
            $event->setResponse(new JsonResponse(['message' => $message], $code));

            return;
        }

        if ($exception instanceof NotFoundHttpException) {
            $code = Response::HTTP_NOT_FOUND;
            $message = 'Not found';
            $event->setResponse(new JsonResponse(['message' => $message], $code));

            return;
        }

        $this->logger->critical(
            $exception->getMessage(),
            [
                'file' => $exception->getFile(),
                'exception' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'code' => $exception->getCode(),
                'request' => $event->getRequest(),
            ]
        );

        $code = $exception->getCode();
        if (!isset(Response::$statusTexts[$code])) {
            $code = match (get_class($exception)) {
                BadRequestHttpException::class => Response::HTTP_BAD_REQUEST,
                JsonException::class => Response::HTTP_BAD_REQUEST,
                default => Response::HTTP_INTERNAL_SERVER_ERROR,
            };
        }

        $message = Response::$statusTexts[$code];

        $event->setResponse(new JsonResponse(['errors' => ['message' => $message]], $code));
    }
}
