<?php

declare(strict_types=1);

namespace App\Application\Query\App\GetAll;

use App\Application\Shared\App\AppResponse;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Domain\Model\Tenant\App;

final class GetAllAppsQueryResponse implements QueryResponseInterface
{
    /** @param AppResponse[] $appsResponses */
    private function __construct(private readonly array $appsResponses)
    {
    }

    /** @param App[] $apps */
    public static function fromApps(array $apps): self
    {
        return new self(
            array_map(static fn (App $app) => AppResponse::fromApp($app), $apps)
        );
    }

    /**
     * @return AppResponse[]
     */
    public function getItems(): array
    {
        return $this->appsResponses;
    }
}
