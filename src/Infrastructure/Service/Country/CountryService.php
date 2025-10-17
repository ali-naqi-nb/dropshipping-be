<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Country;

use App\Application\Service\Country\CountryServiceInterface;
use Symfony\Component\Intl\Countries;

final class CountryService implements CountryServiceInterface
{
    public function getTranslatedName(string $alpha3, string $locale): string
    {
        return Countries::getAlpha3Name($alpha3, $locale);
    }

    public function convertThreeToTwoLetterCountryCode(string $alpha3Code): string
    {
        return Countries::getAlpha2Code($alpha3Code);
    }
}
