<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Traversable;

/**
 * This class allows us to have a separate serializer for the Rpc component and the rest of the application.
 * This is useful because the Rpc component requires a different set of normalizers and encoders.
 */
final class SymfonySerializerFactory
{
    public static function create(
        Traversable $taggedNormalizers,
        array $additionalNormalizers,
        array $encoders,
    ): SerializerInterface {
        return new Serializer(
            array_merge($additionalNormalizers, iterator_to_array($taggedNormalizers)),
            $encoders
        );
    }
}
