<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

enum AppId: string
{
    case AliExpress = 'ali-express';

    public static function exchangeTokenAppIds(): array
    {
        return [
            self::AliExpress,
        ];
    }
}
