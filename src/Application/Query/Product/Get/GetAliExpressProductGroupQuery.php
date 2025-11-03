<?php

declare(strict_types=1);

namespace App\Application\Query\Product\Get;

use App\Domain\Model\Bus\Query\QueryInterface;

final class GetAliExpressProductGroupQuery implements QueryInterface
{
    public function __construct(private readonly string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
