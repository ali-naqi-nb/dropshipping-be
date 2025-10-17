<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared;

use App\Application\Shared\NumberHelper;
use App\Tests\Unit\UnitTestCase;

final class NumberHelperTest extends UnitTestCase
{
    /**
     * @dataProvider provideFloatToIntData
     */
    public function testFloatToInt(float $input, int $expected, ?int $precision = null): void
    {
        $result = $precision ? NumberHelper::floatToInt($input, $precision) : NumberHelper::floatToInt($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider provideIntToFloatData
     */
    public function testIntToFloat(int $input, float $expected, ?int $precision = null): void
    {
        $result = $precision ? NumberHelper::intToFloat($input, $precision) : NumberHelper::intToFloat($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider provideIntToFloatStringData
     */
    public function testIntToFloatString(int $input, string $expected, ?int $precision = null): void
    {
        $result = $precision ? NumberHelper::intToFloatString($input, $precision) : NumberHelper::intToFloatString($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider provideRoundData
     */
    public function testRound(float $input, float $expected, ?int $precision = null): void
    {
        $result = $precision ? NumberHelper::round($input, $precision) : NumberHelper::round($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider provideFormatData
     */
    public function testFormat(int|float $input, string $expected, ?int $precision = null): void
    {
        $result = $precision ? NumberHelper::format($input, $precision) : NumberHelper::format($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider provideGetPercentageOfNumberData
     */
    public function testGetPercentageOfNumber(int|float $amount, int|float $total, int|float $expected): void
    {
        $result = NumberHelper::getPercentageOfNumber($amount, $total);

        $this->assertSame($expected, $result);
    }

    public function provideFloatToIntData(): array
    {
        return [
            'positiveNoPrecision' => [2.3, 230],
            'positiveWithPrecision' => [6.75, 67500, 4],
            'negativeNoPrecision' => [-2.3, -230],
            'negativeWithPrecision' => [-6.75, -67500, 4],
            'zeroNoPrecision' => [0.0, 0],
            'zeroWithPrecision' => [0.0, 0, 4],
        ];
    }

    public function provideIntToFloatData(): array
    {
        return [
            'positiveNoPrecision' => [230, 2.3],
            'positiveWithPrecision' => [6, 0.0006, 4],
            'negativeNoPrecision' => [-17, -0.17],
            'negativeWithPrecision' => [-65, -0.0065, 4],
            'zeroNoPrecision' => [0, 0.0],
            'zeroWithPrecision' => [0, 0.0000, 4],
        ];
    }

    public function provideIntToFloatStringData(): array
    {
        return [
            'positiveNoPrecision' => [230, '2.30'],
            'positiveWithPrecision' => [6, '0.0006', 4],
            'negativeNoPrecision' => [-17, '-0.17'],
            'negativeWithPrecision' => [-65, '-0.0065', 4],
            'zeroNoPrecision' => [0, '0.00'],
            'zeroWithPrecision' => [0, '0.0000', 4],
        ];
    }

    public function provideRoundData(): array
    {
        return [
            'positiveNoPrecision' => [17.34, 17.34],
            'positiveWithPrecision' => [23.456, 23.5, 1],
            'negativeNoPrecision' => [-12.648997, -12.65],
            'negativeWithPrecision' => [-12.648997, -12.6490, 4],
            'zeroNoPrecision' => [0, 0.0],
            'zeroWithPrecision' => [0, 0.0000, 4],
        ];
    }

    public function provideFormatData(): array
    {
        return [
            'positiveNoPrecision' => [2, '2.00'],
            'positiveWithPrecision' => [23.456, '23.5', 1],
            'negativeNoPrecision' => [-12.648997, '-12.65'],
            'negativeWithPrecision' => [-12, '-12.0000', 4],
            'zeroNoPrecision' => [0, '0.00'],
            'zeroWithPrecision' => [0, '0.0000', 4],
            'bigNumberNoPrecision' => [15000, '15,000.00'],
            'bigNumberWithPrecision' => [15100, '15,100.0000', 4],
        ];
    }

    public function provideGetPercentageOfNumberData(): array
    {
        return [
            'intInput' => [50, 100, 50.0],
            'floatInput' => [53, 112, 47.32142857142857],
            'amountBiggerThanTotal' => [112, 53, 211.32075471698113],
            'negativeAmount' => [-20, 100, -20.0],
            'negativeAmountAndTotal' => [-20, -100, 20.0],
        ];
    }
}
