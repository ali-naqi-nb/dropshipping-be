<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Rpc\V1\Order;

use App\Application\Query\Order\GetBySource\GetOrdersBySourceQuery;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Infrastructure\Rpc\Attribute\Rpc;
use App\Infrastructure\Rpc\Exception\InvalidRequestException;

#[Rpc(command: 'getOrdersBySource')]
final class GetOrdersBySourceAction
{
    public function __construct(
        private readonly QueryBusInterface $bus,
    ) {
    }

    public function __invoke(GetOrdersBySourceQuery $query): null|QueryResponseInterface|array
    {
        $response = $this->bus->ask($query);

        if ($response instanceof ErrorResponse) {
            throw InvalidRequestException::fromErrorResponse($response);
        }

        return $response;
    }
}
