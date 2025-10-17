<?php

declare(strict_types=1);

namespace App\Tests\Shared\Random;

use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

final class Generator
{
    public static function string(int $length = 10): string
    {
        return ByteString::fromRandom($length, implode('', range('A', 'Z')))->toString();
    }

    public static function digitsOnlyString(int $length = 10): string
    {
        return ByteString::fromRandom($length, implode('', range('0', '9')))->toString();
    }

    public static function uuid(): string
    {
        return (string) Uuid::v4();
    }
}
