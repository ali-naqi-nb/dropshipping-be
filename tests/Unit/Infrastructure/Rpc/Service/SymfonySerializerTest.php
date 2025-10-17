<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Service;

use App\Infrastructure\Messenger\SymfonySerializer;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonySerializerTest extends UnitTestCase
{
    public function testSerialize(): void
    {
        $data = ['foo' => 'bar'];
        $format = 'json';
        $context = [];

        $mockSymfonySerializer = $this->createMock(SerializerInterface::class);
        $mockSymfonySerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($data, $format, $context)
            ->willReturn('{"foo":"bar"}');

        $serializer = new SymfonySerializer($mockSymfonySerializer);

        $result = $serializer->serialize($data, $format, $context);

        $this->assertEquals('{"foo":"bar"}', $result);
    }

    public function testDeserialize(): void
    {
        $data = '{"foo":"bar"}';
        $type = 'array';
        $format = 'json';
        $context = [];

        $mockSymfonySerializer = $this->createMock(SerializerInterface::class);
        $mockSymfonySerializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($data, $type, $format, $context)
            ->willReturn(['foo' => 'bar']);

        $serializer = new SymfonySerializer($mockSymfonySerializer);

        $result = $serializer->deserialize($data, $type, $format, $context);

        $this->assertEquals(['foo' => 'bar'], $result);
    }
}
