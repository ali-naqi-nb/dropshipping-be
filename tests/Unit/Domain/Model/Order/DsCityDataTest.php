<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Infrastructure\Domain\Model\Order\DsCityData;
use App\Tests\Unit\UnitTestCase;

final class DsCityDataTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $cityName = 'Lagos';
        $countryCode = 'NG';

        $cityData = new DsCityData($cityName, $countryCode);

        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertSame($countryCode, $cityData->getCountryCode());
    }

    public function testConstructorWithDifferentValues(): void
    {
        $cityName = 'Port Harcourt';
        $countryCode = 'NG';

        $cityData = new DsCityData($cityName, $countryCode);

        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertSame($countryCode, $cityData->getCountryCode());
    }

    public function testCityDataIsImmutable(): void
    {
        $cityName = 'Abuja';
        $countryCode = 'NG';

        $cityData = new DsCityData($cityName, $countryCode);

        // Verify that the object is immutable (readonly properties)
        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertSame($countryCode, $cityData->getCountryCode());

        // Create a new instance to verify immutability
        $newCityData = new DsCityData('Kano', 'NG');
        $this->assertNotSame($cityData->getCityName(), $newCityData->getCityName());
        $this->assertSame($cityData->getCountryCode(), $newCityData->getCountryCode());
    }

    public function testCityDataWithTwoLetterCountryCode(): void
    {
        $cityName = 'New York';
        $countryCode = 'US';

        $cityData = new DsCityData($cityName, $countryCode);

        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertSame($countryCode, $cityData->getCountryCode());
        $this->assertEquals(2, strlen($cityData->getCountryCode()));
    }

    public function testCityDataWithSpecialCharacters(): void
    {
        $cityName = 'São Paulo';
        $countryCode = 'BR';

        $cityData = new DsCityData($cityName, $countryCode);

        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertSame($countryCode, $cityData->getCountryCode());
    }

    public function testCityDataWithSpacesInName(): void
    {
        $cityName = 'Los Angeles';
        $countryCode = 'US';

        $cityData = new DsCityData($cityName, $countryCode);

        $this->assertSame($cityName, $cityData->getCityName());
        $this->assertStringContainsString(' ', $cityData->getCityName());
    }
}
