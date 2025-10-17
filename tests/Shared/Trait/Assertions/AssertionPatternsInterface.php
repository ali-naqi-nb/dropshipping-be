<?php

declare(strict_types=1);

namespace App\Tests\Shared\Trait\Assertions;

use App\Tests\Shared\Factory\CurrencyFactory;
use App\Tests\Shared\Factory\DateTimeFactory;

/**
 * This interface contains common/generic patterns.
 * Some patterns are intentionally surrounded with double quotes instead of single to make it possible use them in json matcher
 * This cannot be a trait because traits cannot define constants.
 */
interface AssertionPatternsInterface
{
    public const DATE_TIME = "@datetime@.isInDateFormat('".DateTimeFactory::DATE_TIME_FORMAT."')";
    public const NULLABLE_DATE_TIME = self::DATE_TIME.self::NULLABLE;
    public const URL = '@string@.isUrl()';
    public const NULLABLE_URL = self::URL.self::NULLABLE;
    public const IP = '@string@.isIp()';
    public const EMAIL = '@string@.isEmail()';
    public const PHONE = "@string@.matchRegex('/^[(]?[+]?[0-9\s()-]*[0-9]{1,}[\s()-]*$/')";
    public const FLOAT_2ND_PRECISION = "@string@.matchRegex('/\d+\.\d{2}/')";
    public const FLOAT_3RD_PRECISION = "@string@.matchRegex('/\d+\.\d{3}/')";
    public const CURRENCY = "@string@.oneOf(contains('".CurrencyFactory::BGN."'), contains('".CurrencyFactory::USD."'), contains('".CurrencyFactory::EUR."'))";
    public const NULLABLE = '||@null@';
}
