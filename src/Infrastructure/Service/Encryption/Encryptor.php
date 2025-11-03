<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Encryption;

use App\Application\Service\Encryption\EncryptorInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

final class Encryptor implements EncryptorInterface
{
    private const PADDING_SYMBOL = '+';

    public function encrypt(string $rawData, string $key, int $length = null): string
    {
        if (null !== $length && strlen($rawData) < $length) {
            $rawData = str_pad($rawData, $length, self::PADDING_SYMBOL);
        }

        return Crypto::encrypt($rawData, Key::loadFromAsciiSafeString($key));
    }

    public function decrypt(string $encryptedData, string $key): string
    {
        return trim(Crypto::decrypt($encryptedData, Key::loadFromAsciiSafeString($key)), self::PADDING_SYMBOL);
    }

    public function generateKey(): string
    {
        return Key::createNewRandomKey()->saveToAsciiSafeString();
    }
}
