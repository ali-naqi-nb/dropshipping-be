<?php

declare(strict_types=1);

namespace App\Application\Service\Encryption;

interface EncryptorInterface
{
    public function encrypt(string $rawData, string $key, int $length = null): string;

    public function decrypt(string $encryptedData, string $key): string;

    public function generateKey(): string;
}
