<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class LogMessageMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if ($message instanceof DomainEventInterface) {
            /** @var ?ReceivedStamp $receivedStamp */
            $receivedStamp = $envelope->last(ReceivedStamp::class);

            $messageType = $message::class;
            $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
            $context = ['data' => $serializer->normalize($message, JsonEncoder::FORMAT)];

            if (null !== $receivedStamp) {
                $context['transport'] = $receivedStamp->getTransportName();
                $text = sprintf('Received message: %s (%s)', $messageType, $context['transport']);
            } else {
                $text = 'Sent message: '.$messageType;
            }

            $this->logger->info($text, $context);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
