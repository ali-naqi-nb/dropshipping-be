<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

interface DsProvincesRepositoryInterface
{
    public function find(string $countryCode, ?string $provinceName = null): ?array;

    public function remove(string $countryCode): void;

    public function save(string $countryCode, array $data): void;
}
