<?php

declare(strict_types=1);

namespace App\Application\Shared;

final class NumberHelper
{
    public static function floatToInt(float $floatNumber, int $precision = 2): int
    {
        // Round is needed because casting from float to int is not working properly - https://bytes.com/topic/c/answers/962624-wrong-answer-multiplying-float-integer
        return (int) self::round($floatNumber * pow(10, $precision));
    }

    public static function intToFloat(int $intNumber, int $precision = 2): float
    {
        return $intNumber / pow(10, $precision);
    }

    public static function intToFloatString(int $intNumber, int $precision = 2): string
    {
        return self::format($intNumber / pow(10, $precision), $precision);
    }

    public static function round(int|float $number, int $precision = 2): float
    {
        return round($number, $precision);
    }

    public static function format(int|float $number, int $precision = 2): string
    {
        return number_format($number, $precision);
    }

    public static function getPercentageOfNumber(int|float $amount, int|float $total): int|float
    {
        return ($amount / $total) * 100;
    }
}
