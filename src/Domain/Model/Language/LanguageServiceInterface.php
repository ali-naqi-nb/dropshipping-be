<?php

declare(strict_types=1);

namespace App\Domain\Model\Language;

interface LanguageServiceInterface
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

    public const DEFAULT = self::BG;

    public const SHIPPING_BG = 'bg';

    public const SHIPPING_EN = 'en';

    public const SHIPPING_RO = 'ro';

    public function isSupported(string $language): bool;

    /** @return string[] */
    public function getSupported(): array;
}
