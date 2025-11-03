<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Service;

interface CallIdGeneratorInterface
{
    public function generate(): string;
}
