<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

interface DsCitiesRepositoryInterface
{
    public function find(string $countryCode, ?string $cityName = null): ?array;

    public function remove(string $countryCode): void;

    public function save(string $countryCode, array $data): void;
}
