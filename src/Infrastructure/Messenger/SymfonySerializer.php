<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final class SymfonySerializer implements SerializerInterface
{
    public function __construct(private readonly SymfonySerializerInterface $rpcSerializer)
    {
    }

    public function serialize($data, string $format, array $context = []): string
    {
        return $this->rpcSerializer->serialize($data, $format, $context);
    }

    public function deserialize($data, string $type, string $format, array $context = []): mixed
    {
        return $this->rpcSerializer->deserialize($data, $type, $format, $context);
    }
}
