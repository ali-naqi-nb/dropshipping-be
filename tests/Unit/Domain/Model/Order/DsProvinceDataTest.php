<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use App\Tests\Unit\UnitTestCase;

final class DsProvinceDataTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $provinceName = 'Lagos State';
        $countryCode = 'NG';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertSame($countryCode, $provinceData->getCountryCode());
    }

    public function testConstructorWithDifferentValues(): void
    {
        $provinceName = 'Rivers State';
        $countryCode = 'NG';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertSame($countryCode, $provinceData->getCountryCode());
    }

    public function testProvinceDataIsImmutable(): void
    {
        $provinceName = 'Abia State';
        $countryCode = 'NG';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        // Verify that the object is immutable (readonly properties)
        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertSame($countryCode, $provinceData->getCountryCode());

        // Create a new instance to verify immutability
        $newProvinceData = new DsProvinceData('Kano State', 'NG');
        $this->assertNotSame($provinceData->getProvinceName(), $newProvinceData->getProvinceName());
        $this->assertSame($provinceData->getCountryCode(), $newProvinceData->getCountryCode());
    }

    public function testProvinceDataWithTwoLetterCountryCode(): void
    {
        $provinceName = 'California';
        $countryCode = 'US';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertSame($countryCode, $provinceData->getCountryCode());
        $this->assertEquals(2, strlen($provinceData->getCountryCode()));
    }

    public function testProvinceDataWithSpecialCharacters(): void
    {
        $provinceName = 'São Paulo';
        $countryCode = 'BR';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertSame($countryCode, $provinceData->getCountryCode());
    }

    public function testProvinceDataWithSpacesInName(): void
    {
        $provinceName = 'Federal Capital Territory';
        $countryCode = 'NG';

        $provinceData = new DsProvinceData($provinceName, $countryCode);

        $this->assertSame($provinceName, $provinceData->getProvinceName());
        $this->assertStringContainsString(' ', $provinceData->getProvinceName());
    }

    public function testProvinceDataWithDifferentCountries(): void
    {
        $nigeriaProvince = new DsProvinceData('Lagos State', 'NG');
        $usProvince = new DsProvinceData('California', 'US');
        $ukProvince = new DsProvinceData('Greater London', 'GB');

        $this->assertSame('NG', $nigeriaProvince->getCountryCode());
        $this->assertSame('US', $usProvince->getCountryCode());
        $this->assertSame('GB', $ukProvince->getCountryCode());

        $this->assertNotSame(
            $nigeriaProvince->getProvinceName(),
            $usProvince->getProvinceName()
        );
    }
}
