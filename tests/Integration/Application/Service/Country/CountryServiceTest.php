<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Service\Country;

use App\Infrastructure\Service\Country\CountryService;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\LocaleFactory;

final class CountryServiceTest extends IntegrationTestCase
{
    private CountryService $country;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CountryService $country */
        $country = self::getContainer()->get(CountryService::class);
        $this->country = $country;
    }

    public function testGetNameFromAlpha3(): void
    {
        foreach (LocaleFactory::getSupportedLocales() as $supportedLocale) {
            $this->assertSame(LocaleFactory::COUNTRY_BULGARIA_TRANSLATIONS[$supportedLocale], $this->country->getTranslatedName(LocaleFactory::COUNTRY_CODE_BGR, $supportedLocale));
        }
    }

    public function testConvertThreeToTwoLetterCountryCode(): void
    {
        $this->assertSame('US', $this->country->convertThreeToTwoLetterCountryCode('USA'));
    }
}
