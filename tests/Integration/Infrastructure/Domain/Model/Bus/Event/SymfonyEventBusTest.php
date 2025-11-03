<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Event;

use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

final class SymfonyEventBusTest extends IntegrationTestCase
{
    private EventBusInterface $eventBus;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventBusInterface $eventBus */
        $eventBus = self::getContainer()->get(EventBusInterface::class);
        $this->eventBus = $eventBus;
    }

    public function testEventIsPublishedInTransport(): void
    {
        $this->eventBus->publish(new DummyEvent());

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_dummy');
        $this->assertCount(1, $transport->getSent());
        $this->assertInstanceOf(DummyEvent::class, $transport->getSent()[0]->getMessage());
    }
}
