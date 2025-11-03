<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

enum ProductSource: string
{
    case AliExpress = 'AliExpress';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return [
            self::AliExpress->value,
        ];
    }
}
