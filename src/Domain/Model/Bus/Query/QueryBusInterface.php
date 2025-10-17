<?php

declare(strict_types=1);

namespace App\Domain\Model\Bus\Query;

interface QueryBusInterface
{
    /**
     * @return QueryResponseInterface|QueryResponseInterface[]|null
     */
    public function ask(QueryInterface $query): null|QueryResponseInterface|array;
}
