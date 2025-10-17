<?php

declare(strict_types=1);

namespace App\Application\Service\AliExpress;

final class AeUtil
{
    public const AE_PRODUCT_URL_PATTERN = '/^https:\/\/www\.aliexpress\.com\/item\/(\d+)\.html.*$/';

    private const COUNTRY_CALLING_CODE = [
        'AF' => '93', 'AL' => '355',
        'DZ' => '213', 'AR' => '54',
        'AM' => '374', 'AU' => '61',
        'AT' => '43', 'AZ' => '994',
        'BH' => '973', 'BD' => '880',
        'BY' => '375', 'BE' => '32',
        'BJ' => '229', 'BO' => '591',
        'BA' => '387', 'BR' => '55',
        'BG' => '359', 'CA' => '1',
        'CL' => '56', 'CN' => '86',
        'CO' => '57', 'HR' => '385',
        'CU' => '53', 'CY' => '357',
        'CZ' => '420', 'DK' => '45',
        'DO' => '1', 'EG' => '20',
        'EE' => '372', 'FI' => '358',
        'FR' => '33', 'GE' => '995',
        'DE' => '49', 'GR' => '30',
        'HK' => '852', 'HU' => '36',
        'IS' => '354', 'IN' => '91',
        'ID' => '62', 'IR' => '98',
        'IQ' => '964', 'IE' => '353',
        'IL' => '972', 'IT' => '39',
        'JP' => '81', 'JO' => '962',
        'KZ' => '7', 'KE' => '254',
        'KR' => '82', 'KW' => '965',
        'LV' => '371', 'LB' => '961',
        'LY' => '218', 'LT' => '370',
        'LU' => '352', 'MY' => '60',
        'MT' => '356', 'MX' => '52',
        'MD' => '373', 'ME' => '382',
        'MA' => '212', 'NL' => '31',
        'NZ' => '64', 'NG' => '234',
        'NO' => '47', 'PK' => '92',
        'PS' => '970', 'PE' => '51',
        'PH' => '63', 'PL' => '48',
        'PT' => '351', 'QA' => '974',
        'RO' => '40', 'RU' => '7',
        'SA' => '966', 'RS' => '381',
        'SG' => '65', 'SK' => '421',
        'SI' => '386', 'ZA' => '27',
        'ES' => '34', 'LK' => '94',
        'SE' => '46', 'CH' => '41',
        'SY' => '963', 'TH' => '66',
        'TR' => '90', 'UA' => '380',
        'AE' => '971', 'GB' => '44',
        'US' => '1', 'UY' => '598',
        'UZ' => '998', 'VE' => '58',
        'VN' => '84', 'YE' => '967',
        'ZM' => '260', 'ZW' => '263',
    ];

    public static function getProductId(string $productUrl): ?int
    {
        preg_match(self::AE_PRODUCT_URL_PATTERN, $productUrl, $matches);
        $aeProductId = $matches[1] ?? null;

        if (null === $aeProductId) {
            return null;
        }

        return (int) $aeProductId;
    }

    public static function toBase100(string $value): int
    {
        return (int) (floatval($value) * 100);
    }

    public static function formatPhoneNumber(string $phoneNumber): string
    {
        return str_replace('+', '', $phoneNumber);
    }

    public static function getPhoneCountryCode(string $countryCode): ?string
    {
        return self::COUNTRY_CALLING_CODE[strtoupper($countryCode)] ?? null;
    }
}
