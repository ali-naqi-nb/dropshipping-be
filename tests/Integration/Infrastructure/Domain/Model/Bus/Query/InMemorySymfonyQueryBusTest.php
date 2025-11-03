<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Query;

use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class InMemorySymfonyQueryBusTest extends IntegrationTestCase
{
    private QueryBusInterface $queryBus;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var QueryBusInterface $queryBus */
        $queryBus = self::getContainer()->get(QueryBusInterface::class);
        $this->queryBus = $queryBus;
    }

    public function testQueryWithOnlyOneHandlerReturnsSuccess(): void
    {
        $this->assertInstanceOf(DummyQueryResponse::class, $this->queryBus->ask(new DummyQuery()));
    }

    public function testWithQueryWithoutHandlerThrowsException(): void
    {
        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage('No handler for message');

        $this->queryBus->ask(new UnhandledDummyQuery());
    }

    public function testWithQueryWithMoreThanOneHandlerThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"App\Tests\Integration\Infrastructure\Domain\Model\Bus\Query\MultipleHandledDummyQuery" was handled multiple times.');

        $this->queryBus->ask(new MultipleHandledDummyQuery());
    }
}
