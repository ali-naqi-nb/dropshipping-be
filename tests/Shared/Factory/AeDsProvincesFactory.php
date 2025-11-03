<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Infrastructure\Domain\Model\Order\DsProvinceData;

final class AeDsProvincesFactory
{
    public const CACHE_KEY = 'dropshipping_provinces_ali_express_'.self::COUNTRY_CODE;

    public const CACHE_TTL = 'PT24H';

    public const PROVINCE_NAME = 'sofia';
    public const COUNTRY_CODE = 'BGR';

    public static function getDsProvince(
        string $provinceName = self::PROVINCE_NAME,
        string $countryCode = self::COUNTRY_CODE,
    ): DsProvinceData {
        return new DsProvinceData(
            provinceName: $provinceName,
            countryCode: $countryCode
        );
    }
}
