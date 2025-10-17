<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Attribute;

use Attribute;

/**
 * Can be applied to invokable class or a method.
 * If applied to class, the __invoke method will be used as the command handler.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Rpc
{
    /**
     * @param string|null $service If not passed, the service name will be automatically detected from the "app.service_name" parameter
     * @param string|null $command If not passed, the command name will be automatically detected from the method name, or from the class name if the attribute is applied to a class
     */
    public function __construct(
        private readonly ?string $service = null,
        private readonly ?string $command = null,
    ) {
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }
}
