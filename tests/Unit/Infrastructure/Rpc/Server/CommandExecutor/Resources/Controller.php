<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor\Resources;

final class Controller
{
    public function void(): void
    {
    }

    public function primitive(int $id): int
    {
        return $id;
    }

    public function object(Dto $inputDto): Dto
    {
        return $inputDto;
    }

    public function mixed(mixed $parameter): mixed
    {
        return $parameter;
    }

    public function default(int $default = 3): int
    {
        return $default;
    }

    public function union(int|string $id): int|string
    {
        return $id;
    }

    public function multipleParameters(
        int $id,
        Dto $inputDto,
        mixed $mixed,
        int|string $union,
        int $default = 3,
    ): int {
        return $default;
    }
}
