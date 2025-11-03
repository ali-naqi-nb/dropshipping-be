<?php

declare(strict_types=1);

namespace App\Application\Shared\App;

use App\Domain\Model\Bus\Command\CommandResponseInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Domain\Model\Tenant\App;

final class AppResponse implements QueryResponseInterface, CommandResponseInterface
{
    private function __construct(
        private readonly string $appId,
        private array $config
    ) {
    }

    public static function fromApp(App $app): self
    {
        return new self($app->getAppId()->value, $app->getConfig());
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
