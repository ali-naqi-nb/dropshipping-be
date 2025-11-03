<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Order;

final class DsCityData
{
    public function __construct(
        private readonly string $cityName,
        private readonly string $countryCode,
    ) {
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
