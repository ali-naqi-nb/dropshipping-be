<?php

declare(strict_types=1);

namespace App\Tests\Shared\Double\Service\Encryption;

use App\Application\Service\Encryption\EncryptorInterface;

final class NoopEncryptor implements EncryptorInterface
{
    public function encrypt(string $rawData, string $key, int $length = null): string
    {
        return $rawData;
    }

    public function decrypt(string $encryptedData, string $key): string
    {
        return $encryptedData;
    }

    public function generateKey(): string
    {
        return '';
    }
}
