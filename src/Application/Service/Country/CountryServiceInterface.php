<?php

declare(strict_types=1);

namespace App\Application\Service\Country;

interface CountryServiceInterface
{
    public function getTranslatedName(string $alpha3, string $locale): string;

    public function convertThreeToTwoLetterCountryCode(string $alpha3Code): string;
}
