<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

interface SerializerInterface
{
    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    Any data
     * @param string $format  Format name
     * @param array  $context Options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, string $format, array $context = []);

    /**
     * Deserializes data into the given type.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function deserialize($data, string $type, string $format, array $context = []);
}
