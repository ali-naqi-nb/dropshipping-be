<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Messenger;

use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use App\Infrastructure\Messenger\CorrelationIdStamp;
use App\Infrastructure\Messenger\MessageSerializer;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

final class MessageSerializerTest extends IntegrationTestCase
{
    private MessageSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $correlationIdStorage = $this->createMock(CorrelationIdStorageInterface::class);
        $correlationIdStorage
            ->method('setCorrelationId')
            ->with(CorrelationIdFactory::CORRELATION_ID);

        $correlationIdStorage
            ->method('getCorrelationId')
            ->willReturn(CorrelationIdFactory::CORRELATION_ID);

        $this->serializer = new MessageSerializer($correlationIdStorage);
    }

    public function testEncodeEnvelopmentNotHaveDefaultCorrelationIdHeader(): void
    {
        $this->assertArrayNotHasKey('headers', $this->serializer->encode($this->givenEnvelope()));
    }

    public function testEncodeEnvelopmentHasCorrelationIdHeader(): void
    {
        $response = $this->serializer->encode($this->givenEnvelope(true));
        $headers = $response['headers'];

        $this->assertSame(CorrelationIdFactory::CORRELATION_ID, $headers[CorrelationIdFactory::CORRELATION_HEADER_NAME]);
    }

    public function testDecodeEnvelopmentNotHaveCorrelationIdStamp(): void
    {
        $envelope = $this->serializer->decode($this->givenEncodedEnvelope());
        $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);

        $this->assertNull($correlationIdStamp);
        $this->assertInstanceOf(Envelope::class, $envelope);
    }

    public function testDecodeEnvelopmentHasCorrelationIdStamp(): void
    {
        $envelope = $this->serializer->decode($this->givenEncodedEnvelope(true));
        $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);

        $this->assertNotEmpty($correlationIdStamp);
        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertInstanceOf(CorrelationIdStamp::class, $correlationIdStamp);
    }

    private function givenEnvelope(bool $withCorrelationIdStamp = false): Envelope
    {
        $envelope = new Envelope(new stdClass());
        $envelope = $envelope->with(new BusNameStamp('event.bus'));

        if ($withCorrelationIdStamp) {
            $envelope = $envelope->with(new CorrelationIdStamp(CorrelationIdFactory::CORRELATION_ID));
        }

        return $envelope;
    }

    private function givenEncodedEnvelope(bool $withCorrelationId = false): array
    {
        $envelope = $this->givenEnvelope($withCorrelationId);

        $data = [
            'body' => addslashes(serialize($envelope)),
            'headers' => [
                'type' => get_class($envelope->getMessage()),
            ],
        ];

        if ($withCorrelationId) {
            $data['headers'][CorrelationIdFactory::CORRELATION_HEADER_NAME] = CorrelationIdFactory::CORRELATION_ID;
        }

        return $data;
    }
}
