<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Bus\Query;

use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Domain\Model\Bus\Query\QueryInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class InMemorySymfonyQueryBus implements QueryBusInterface
{
    use HandleTrait;

    /**
     * @param MessageBusInterface $queryBus Name is imported because we want to use query bus
     */
    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * @return QueryResponseInterface|QueryResponseInterface[]|null
     */
    public function ask(QueryInterface $query): null|QueryResponseInterface|array
    {
        /** @var QueryResponseInterface|QueryResponseInterface[]|null $response */
        $response = $this->handle($query);

        return $response;
    }
}
