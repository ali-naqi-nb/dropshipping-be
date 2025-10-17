<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Service;

use App\Infrastructure\Messenger\SymfonySerializerFactory;
use App\Tests\Unit\UnitTestCase;
use ArrayObject;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonySerializerFactoryTest extends UnitTestCase
{
    public function testCreate(): void
    {
        $taggedNormalizers = new ArrayObject(
            [$this->createMock(NormalizerInterface::class)],
        );
        $additionalNormalizers = [
            $this->createMock(NormalizerInterface::class),
        ];
        $encoders = [
            $this->createMock(EncoderInterface::class),
        ];

        $serializer = SymfonySerializerFactory::create($taggedNormalizers, $additionalNormalizers, $encoders);

        $this->assertInstanceOf(SerializerInterface::class, $serializer);
    }
}
