<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

enum ShopStatus: string
{
    case Test = 'test';
    case Live = 'live';
    case TestExpired = 'testExpired';
    case Suspended = 'suspended';
    case Deleted = '';

    public static function toArray(): array
    {
        return [
            self::Test->value,
            self::Live->value,
            self::TestExpired->value,
            self::Suspended->value,
            self::Deleted->value,
        ];
    }
}
