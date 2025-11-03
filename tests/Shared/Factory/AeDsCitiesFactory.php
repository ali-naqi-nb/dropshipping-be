<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Infrastructure\Domain\Model\Order\DsCityData;

final class AeDsCitiesFactory
{
    public const CACHE_KEY = 'dropshipping_cities_ali_express_'.self::COUNTRY_CODE;
    public const CACHE_TTL = 'PT24H';

    public const CITY_NAME = 'Sofia';
    public const COUNTRY_CODE = 'BGR';

    public static function getDsCity(
        string $cityName = self::CITY_NAME,
        string $countryCode = self::COUNTRY_CODE,
    ): DsCityData {
        return new DsCityData(
            cityName: $cityName,
            countryCode: $countryCode
        );
    }
}
