<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use App\Domain\Model\Bus\Command\CommandResponseInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Tests\Shared\Factory\DateTimeFactory;
use DateTimeInterface;

final class DummyResponse implements CommandResponseInterface, QueryResponseInterface
{
    public function __construct(
        private string $string,
        private array $array,
        private DateTimeInterface $dateTime,
        private int $int,
        private float $float,
        private bool $isTrue,
        private ?string $nullableString
    ) {
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function getDateTime(): string
    {
        return $this->dateTime->format(DateTimeFactory::DATE_TIME_FORMAT);
    }

    public function getInt(): int
    {
        return $this->int;
    }

    public function getFloat(): float
    {
        return $this->float;
    }

    public function getIsTrue(): bool
    {
        return $this->isTrue;
    }

    public function getNullableString(): ?string
    {
        return $this->nullableString;
    }
}
