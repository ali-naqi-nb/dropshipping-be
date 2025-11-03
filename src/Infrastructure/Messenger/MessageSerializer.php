<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

/**
 * @deprecated for backward compatibility only. Use messenger.transport.json_serializer
 */
final class MessageSerializer extends PhpSerializer
{
    private const HEADER_CORRELATION_ID = 'correlation-id';

    public function __construct(private readonly CorrelationIdStorageInterface $correlationIdStorage)
    {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $envelope = parent::decode($encodedEnvelope);

        /** @var ?BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        if (null !== $busNameStamp && !in_array($busNameStamp->getBusName(), ['command.bus', 'query.bus', 'event.bus'])) {
            $envelope = $envelope->withoutStampsOfType(BusNameStamp::class);
        }

        if (isset($encodedEnvelope['headers'][self::HEADER_CORRELATION_ID])) {
            $correlationId = $encodedEnvelope['headers'][self::HEADER_CORRELATION_ID];

            $this->correlationIdStorage->setCorrelationId($correlationId);

            $envelope = $envelope->with(new CorrelationIdStamp($correlationId));
        }

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        /**
         * When bus name is event.bus
         *  1. remove bus name stamp because there is no guarantee that in other microservices the bus name will be the same
         *  2. add correlation id header in order to connect logs.
         */
        /** @var ?BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        if (null !== $busNameStamp && 'event.bus' === $busNameStamp->getBusName()) {
            $envelope = $envelope->withoutStampsOfType(BusNameStamp::class);

            $data = parent::encode($envelope);

            /** @var CorrelationIdStamp $stamp */
            $stamp = $envelope->last(CorrelationIdStamp::class);
            if (null !== $stamp) {
                $data['headers'] = [self::HEADER_CORRELATION_ID => $stamp->getId()];
            }

            return $data;
        }

        return parent::encode($envelope);
    }
}
