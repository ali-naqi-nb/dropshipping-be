<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class LocaleFactory
{
    public const BG = 'bg_BG';
    public const EN = 'en_US';
    public const RO = 'ro_RO';
    public const GR = 'el_GR';
    public const DE = 'de_DE';
    public const FR = 'fr_FR';
    public const HU = 'hu_HU';
    public const PL = 'pl_PL';
    public const ID = 'id_ID';
    public const PK = 'ur_PK';
    public const PH = 'tl_PH';

    public const NOT_SUPPORTED = 'fk_FK';

    public const SHIPPING_BG = 'bg';
    public const SHIPPING_EN = 'en';
    public const SHIPPING_RO = 'ro';

    public const COUNTRY_CODE_BGR = 'BGR';
    public const COUNTRY_BULGARIA_TRANSLATIONS = [
        self::EN => 'Bulgaria',
        self::BG => 'България',
        self::RO => 'Bulgaria',
    ];

    /**
     * Return list with main languages for testing purposes. Additional languages as RO, will be handled only for successfull create/update.
     */
    public static function getSupportedLocales(): array
    {
        return [self::EN, self::BG];
    }
}
