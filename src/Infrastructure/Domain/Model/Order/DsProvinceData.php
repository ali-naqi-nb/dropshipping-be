<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Order;

final class DsProvinceData
{
    public function __construct(
        private readonly string $provinceName,
        private readonly int|string $countryCode,
    ) {
    }

    public function getProvinceName(): string
    {
        return $this->provinceName;
    }

    public function getCountryCode(): int|string
    {
        return $this->countryCode;
    }
}
