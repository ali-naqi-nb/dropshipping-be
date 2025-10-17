<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http;

use App\Infrastructure\Http\RequestMapper;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\ArrayType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\BoolType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\DateTimeType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\DefaultStringType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\DefaultValueType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\FloatType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\IntType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NonConstructorType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableArrayType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableBoolType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableDateTimeType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableFloatType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableIntType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\NullableStringType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\StringType;
use App\Tests\Unit\Infrastructure\Http\RequestMappingType\UnsupportedType;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class RequestMapperTest extends KernelTestCase
{
    /**
     * @param class-string $type
     * @dataProvider provideFromBodyWithNullableFieldsData
     */
    public function testFromBodyWithNullableFields(string $type, string $getterName): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBody($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromBodyWithNotNullableFieldsData
     */
    public function testFromBodyWithNotNullableFields(
        string $type,
        string $getterName,
        mixed $expectedEmptyValue
    ): void {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBody($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $realValue = $typeInstance->$getterName();
        if ($realValue instanceof DateTimeImmutable) {
            $this->assertLessThanOrEqual($expectedEmptyValue->getTimestamp() + 100, $realValue->getTimestamp());
        } else {
            $this->assertSame($expectedEmptyValue, $realValue);
        }
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromBodyWithSetData
     */
    public function testFromBodyWithSetData(
        string $type,
        string $getterName,
        array $content,
        mixed $expectedValue,
    ): void {
        /** @var string $jsonContent */
        $jsonContent = json_encode($content);
        $request = new Request(content: $jsonContent);
        // This is need because RequestSubscriber can't be used here
        $request->request->replace(json_decode((string) $request->getContent(), true));
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBody($request, $type);
        $this->assertInstanceOf($type, $typeInstance);

        $realValue = $typeInstance->$getterName();
        if ($realValue instanceof DateTimeImmutable) {
            $this->assertLessThanOrEqual($expectedValue->getTimestamp() + 100, $realValue->getTimestamp());
        } else {
            $this->assertSame($expectedValue, $realValue);
        }
    }

    public function testFromBodyThrowsException(): void
    {
        /** @var string $content */
        $content = json_encode(['unsupportedType' => 'test']);
        $request = new Request(content: $content);
        // This is need because RequestSubscriber can't be used here
        $request->request->replace(json_decode((string) $request->getContent(), true));
        $mapper = new RequestMapper();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to convert request to command/query');

        $mapper->fromBody($request, UnsupportedType::class);
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromBodyWithNullableFieldsData
     */
    public function testFromQueryWithNullableFields(string $type, string $getterName): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromQuery($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromBodyWithNotNullableFieldsData
     */
    public function testFromQueryWithNotNullableFields(
        string $type,
        string $getterName,
        mixed $expectedEmptyValue
    ): void {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromQuery($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $realValue = $typeInstance->$getterName();
        if ($realValue instanceof DateTimeImmutable) {
            $this->assertLessThanOrEqual($expectedEmptyValue->getTimestamp() + 100, $realValue->getTimestamp());
        } else {
            $this->assertSame($expectedEmptyValue, $realValue);
        }
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromQueryWithSetData
     */
    public function testFromQueryWithSetData(
        string $type,
        string $getterName,
        array $query,
        mixed $expectedValue,
    ): void {
        $request = new Request($query);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromQuery($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyWithNullableFieldsData
     */
    public function testFromAttributesWithNullableFields(string $type, string $getterName): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyWithNotNullableFieldsData
     */
    public function testFromAttributesWithNotNullableFields(
        string $type,
        string $getterName,
        mixed $expectedEmptyValue
    ): void {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $realValue = $typeInstance->$getterName();
        if ($realValue instanceof DateTimeImmutable) {
            $this->assertLessThanOrEqual($expectedEmptyValue->getTimestamp() + 100, $realValue->getTimestamp());
        } else {
            $this->assertSame($expectedEmptyValue, $realValue);
        }
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyAndAttributesWithSetData
     */
    public function testFromAttributesWithSetData(
        string $type,
        string $getterName,
        array $attributes,
        mixed $expectedValue,
    ): void {
        $request = new Request([], [], $attributes);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertEquals($expectedValue, $typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyWithNullableFieldsData
     */
    public function testFromBodyAndAttributesWithNullableFields(string $type, string $getterName): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBodyAndAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyWithNotNullableFieldsData
     */
    public function testFromBodyAndAttributesWithNotNullableFields(
        string $type,
        string $getterName,
        mixed $expectedEmptyValue
    ): void {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBodyAndAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $realValue = $typeInstance->$getterName();
        if ($realValue instanceof DateTimeImmutable) {
            $this->assertLessThanOrEqual($expectedEmptyValue->getTimestamp() + 100, $realValue->getTimestamp());
        } else {
            $this->assertSame($expectedEmptyValue, $realValue);
        }
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromBodyAndAttributesWithSetData
     */
    public function testFromBodyAndAttributesWithSetData(
        string $type,
        string $getterName,
        array $data,
        mixed $expectedValue,
    ): void {
        $request = new Request([], [], $data);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBodyAndAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }

        $request = new Request([], $data, []);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromBodyAndAttributes($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithNullableFieldsData
     */
    public function testFromHeadersWithNullableFields(string $type, string $getterName, array $fieldMapping): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithSetData
     */
    public function testFromHeadersWithSetData(
        string $type,
        string $getterName,
        array $headers,
        mixed $expectedValue,
        array $fieldMapping
    ): void {
        $request = new Request(server: $headers);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromRequestWithSetData
     */
    public function testFromRequestAttributesWithSetData(
        string $type,
        string $getterName,
        array $attributes,
        mixed $expectedValue,
    ): void {
        $request = new Request(attributes: $attributes);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromRequest($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertEquals($expectedValue, $typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     *
     * @dataProvider provideFromRequestWithSetData
     */
    public function testFromRequestQueryWithSetData(
        string $type,
        string $getterName,
        array $query,
        mixed $expectedValue,
    ): void {
        $request = new Request(query: $query);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromRequest($request, $type);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertEquals($expectedValue, $typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithNullableFieldsData
     */
    public function testFromAttributesAndHeadersWithNullableFields(string $type, string $getterName, array $fieldMapping): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromAttributesAndHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithSetData
     */
    public function testFromAttributesAndHeadersWithSetData(
        string $type,
        string $getterName,
        array $headers,
        mixed $expectedValue,
        array $fieldMapping
    ): void {
        $request = new Request(server: $headers);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromAttributesAndHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithNullableFieldsData
     */
    public function testFromQueryAndHeadersWithNullableFields(string $type, string $getterName, array $fieldMapping): void
    {
        $request = new Request();
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromQueryAndHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        $this->assertNull($typeInstance->$getterName());
    }

    /**
     * @param class-string $type
     * @dataProvider provideFromHeadersWithSetData
     */
    public function testFromQueryAndHeadersWithSetData(
        string $type,
        string $getterName,
        array $headers,
        mixed $expectedValue,
        array $fieldMapping
    ): void {
        $request = new Request(server: $headers);
        $mapper = new RequestMapper();

        $typeInstance = $mapper->fromQueryAndHeaders($request, $type, $fieldMapping);
        $this->assertInstanceOf($type, $typeInstance);
        if ($expectedValue instanceof DateTimeImmutable) {
            $this->assertEquals($expectedValue, $typeInstance->$getterName());
        } else {
            $this->assertSame($expectedValue, $typeInstance->$getterName());
        }
    }

    public function testNonConstructorType(): void
    {
        $request = new Request();
        $mapper = new RequestMapper();
        $type = NonConstructorType::class;

        $typeInstance = $mapper->fromRequest($request, $type);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromBody($request, $type);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromAttributes($request, $type);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromBodyAndAttributes($request, $type);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromHeaders($request, $type, []);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromQuery($request, $type);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromQueryAndHeaders($request, $type, []);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);

        $typeInstance = $mapper->fromQueryAndHeaders($request, $type, []);
        $this->assertInstanceOf(NonConstructorType::class, $typeInstance);
    }

    public function provideFromBodyWithNullableFieldsData(): array
    {
        return [
            'nullableString' => [NullableStringType::class, 'getNullableString'],
            'nullableInt' => [NullableIntType::class, 'getNullableInt'],
            'nullableFloat' => [NullableFloatType::class, 'getNullableFloat'],
            'nullableBool' => [NullableBoolType::class, 'getNullableBool'],
            'nullableDateTime' => [NullableDateTimeType::class, 'getNullableDateTime'],
            'nullableArray' => [NullableArrayType::class, 'getNullableArray'],
        ];
    }

    public function provideFromBodyWithNotNullableFieldsData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ''],
            'int' => [IntType::class, 'getInt', 0],
            'float' => [FloatType::class, 'getFloat', 0.0],
            'bool' => [BoolType::class, 'getBool', false],
            'dateTime' => [DateTimeType::class, 'getDateTime', new DateTimeImmutable()],
            'array' => [ArrayType::class, 'getArray', []],
            'defaultValue' => [DefaultValueType::class, 'getDefault', DefaultValueType::DEFAULT_VALUE],
        ];
    }

    public function provideFromBodyWithSetData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ['string' => 'test'], 'test'],
            'stringFromArray' => [StringType::class, 'getString', ['string' => []], ''],
            'nullableStringFromArray' => [NullableStringType::class, 'getNullableString', ['string' => []], null],
            'defaultStringFromArray' => [DefaultStringType::class, 'getDefault', ['string' => []], DefaultStringType::DEFAULT_VALUE],
            'intFromString' => [IntType::class, 'getInt', ['int' => '123'], 123],
            'int' => [IntType::class, 'getInt', ['int' => 123], 123],
            'intOverflow' => [IntType::class, 'getInt', ['int' => 9223372036854775808], PHP_INT_MAX],
            'intOverflowDigit' => [IntType::class, 'getInt', ['int' => 92233720368547758078], PHP_INT_MAX],
            'intOverflowFromString' => [IntType::class, 'getInt', ['int' => '9223372036854775808'], PHP_INT_MAX],
            'intOverflowFromStringDigit' => [IntType::class, 'getInt', ['int' => '92233720368547758078'], PHP_INT_MAX],
            'float' => [FloatType::class, 'getFloat', ['float' => '12.32'], 12.32],
            'boolFrom1String' => [BoolType::class, 'getBool', ['bool' => '1'], true],
            'boolFrom0String' => [BoolType::class, 'getBool', ['bool' => '0'], false],
            'boolFromEmptyString' => [BoolType::class, 'getBool', ['bool' => ''], false],
            'boolFromTrue' => [BoolType::class, 'getBool', ['bool' => true], true],
            'boolFromFalse' => [BoolType::class, 'getBool', ['bool' => false], false],
            'dateTimeFromString' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => '2022-04-01 12:34:56'],
                new DateTimeImmutable('2022-04-01 12:34:56'),
            ],
            'dateTimeFromInvalidString' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 'test'],
                new DateTimeImmutable(),
            ],
            'dateTimeFromUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 1650383951],
                new DateTimeImmutable('@'. 1650383951),
            ],
            'dateTimeFromInvalidUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 9999999999],
                new DateTimeImmutable('@'. 9999999999),
            ],
            'array' => [
                ArrayType::class,
                'getArray',
                ['array' => [123, 12.34, 'test', false]],
                [123, 12.34, 'test', false],
            ],
            'defaultValue' => [
                DefaultValueType::class,
                'getDefault',
                ['default' => DefaultValueType::NON_DEFAULT_VALUE],
                DefaultValueType::NON_DEFAULT_VALUE,
            ],
        ];
    }

    public function provideFromQueryWithSetData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ['string' => 'test'], 'test'],
            'stringFromArray' => [StringType::class, 'getString', ['string' => []], ''],
            'nullableStringFromArray' => [NullableStringType::class, 'getNullableString', ['string' => []], null],
            'defaultStringFromArray' => [DefaultStringType::class, 'getDefault', ['string' => []], DefaultStringType::DEFAULT_VALUE],
            'int' => [IntType::class, 'getInt', ['int' => '123'], 123],
            'float' => [FloatType::class, 'getFloat', ['float' => '12.32'], 12.32],
            'boolFrom1String' => [BoolType::class, 'getBool', ['bool' => '1'], true],
            'boolFrom0String' => [BoolType::class, 'getBool', ['bool' => '0'], false],
            'boolFromEmptyString' => [BoolType::class, 'getBool', ['bool' => ''], false],
            'boolFromFalse' => [BoolType::class, 'getBool', ['bool' => 'false'], true],
            'dateTimeFromString' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => '2022-04-01 12:34:56'],
                new DateTimeImmutable('2022-04-01 12:34:56'),
            ],
            'dateTimeFromUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => '1650383951'],
                new DateTimeImmutable('@'. 1650383951),
            ],
            'array' => [
                ArrayType::class,
                'getArray',
                ['array' => [123, 12.34, 'test', false]],
                [123, 12.34, 'test', false],
            ],
            'defaultValue' => [
                DefaultValueType::class,
                'getDefault',
                ['default' => DefaultValueType::NON_DEFAULT_VALUE],
                DefaultValueType::NON_DEFAULT_VALUE,
            ],
        ];
    }

    public function provideFromBodyAndAttributesWithSetData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ['string' => 'test'], 'test'],
            'stringFromArray' => [StringType::class, 'getString', ['string' => []], ''],
            'nullableStringFromArray' => [NullableStringType::class, 'getNullableString', ['string' => []], null],
            'defaultStringFromArray' => [DefaultStringType::class, 'getDefault', ['string' => []], DefaultStringType::DEFAULT_VALUE],
            'intFromString' => [IntType::class, 'getInt', ['int' => '123'], 123],
            'intOverflow' => [IntType::class, 'getInt', ['int' => 9223372036854775808], PHP_INT_MAX],
            'intOverflowDigit' => [IntType::class, 'getInt', ['int' => 92233720368547758078], PHP_INT_MAX],
            'intOverflowFromString' => [IntType::class, 'getInt', ['int' => '9223372036854775808'], PHP_INT_MAX],
            'intOverflowFromStringDigit' => [IntType::class, 'getInt', ['int' => '92233720368547758078'], PHP_INT_MAX],
            'int' => [IntType::class, 'getInt', ['int' => 123], 123],
            'float' => [FloatType::class, 'getFloat', ['float' => '12.32'], 12.32],
            'boolFrom1String' => [BoolType::class, 'getBool', ['bool' => '1'], true],
            'boolFrom0String' => [BoolType::class, 'getBool', ['bool' => '0'], false],
            'boolFromEmptyString' => [BoolType::class, 'getBool', ['bool' => ''], false],
            'boolFromTrue' => [BoolType::class, 'getBool', ['bool' => true], true],
            'boolFromFalse' => [BoolType::class, 'getBool', ['bool' => false], false],
            'dateTimeFromString' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => '2022-04-01 12:34:56'],
                new DateTimeImmutable('2022-04-01 12:34:56'),
            ],
            'dateTimeFromUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 1650383951],
                new DateTimeImmutable('@'. 1650383951),
            ],
            'dateTimeFromInvalidUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 9999999999],
                new DateTimeImmutable('@'. 9999999999),
            ],
            'array' => [
                ArrayType::class,
                'getArray',
                ['array' => [123, 12.34, 'test', false]],
                [123, 12.34, 'test', false],
            ],
            'defaultValue' => [
                DefaultValueType::class,
                'getDefault',
                ['default' => DefaultValueType::NON_DEFAULT_VALUE],
                DefaultValueType::NON_DEFAULT_VALUE,
            ],
        ];
    }

    public function provideFromRequestWithSetData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ['string' => 'test'], 'test'],
            'stringFromArray' => [StringType::class, 'getString', ['string' => []], ''],
            'nullableStringFromArray' => [NullableStringType::class, 'getNullableString', ['string' => []], null],
            'defaultStringFromArray' => [DefaultStringType::class, 'getDefault', ['string' => []], DefaultStringType::DEFAULT_VALUE],
            'intFromString' => [IntType::class, 'getInt', ['int' => '123'], 123],
            'int' => [IntType::class, 'getInt', ['int' => 123], 123],
            'intOverflow' => [IntType::class, 'getInt', ['int' => 9223372036854775808], PHP_INT_MAX],
            'intOverflowDigit' => [IntType::class, 'getInt', ['int' => 92233720368547758078], PHP_INT_MAX],
            'intOverflowFromString' => [IntType::class, 'getInt', ['int' => '9223372036854775808'], PHP_INT_MAX],
            'intOverflowFromStringDigit' => [IntType::class, 'getInt', ['int' => '92233720368547758078'], PHP_INT_MAX],
            'float' => [FloatType::class, 'getFloat', ['float' => '12.32'], 12.32],
            'boolFrom1String' => [BoolType::class, 'getBool', ['bool' => '1'], true],
            'boolFrom0String' => [BoolType::class, 'getBool', ['bool' => '0'], false],
            'boolFromEmptyString' => [BoolType::class, 'getBool', ['bool' => ''], false],
            'boolFromTrue' => [BoolType::class, 'getBool', ['bool' => true], true],
            'boolFromFalse' => [BoolType::class, 'getBool', ['bool' => false], false],
            'dateTimeFromString' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => '2022-04-01 12:34:56'],
                new DateTimeImmutable('2022-04-01 12:34:56'),
            ],
            'dateTimeFromUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 1650383951],
                new DateTimeImmutable('@'. 1650383951),
            ],
            'dateTimeFromInvalidUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['dateTime' => 9999999999],
                new DateTimeImmutable('@'. 9999999999),
            ],
            'array' => [
                ArrayType::class,
                'getArray',
                ['array' => [123, 12.34, 'test', false]],
                [123, 12.34, 'test', false],
            ],
            'defaultValue' => [
                DefaultValueType::class,
                'getDefault',
                ['default' => DefaultValueType::NON_DEFAULT_VALUE],
                DefaultValueType::NON_DEFAULT_VALUE,
            ],
        ];
    }

    public function provideFromHeadersWithNullableFieldsData(): array
    {
        return [
            'nullableString' => [NullableStringType::class, 'getNullableString', ['nullableString' => 'nullable-string']],
            'nullableInt' => [NullableIntType::class, 'getNullableInt', ['nullableInt' => 'nullable-int']],
            'nullableFloat' => [NullableFloatType::class, 'getNullableFloat', ['nullableFloat' => 'nullable-float']],
            'nullableBool' => [NullableBoolType::class, 'getNullableBool', ['nullableBool' => 'nullable-bool']],
            'nullableDateTime' => [NullableDateTimeType::class, 'getNullableDateTime', ['nullableDateTime' => 'nullable-date-time']],
            'nullableArray' => [NullableArrayType::class, 'getNullableArray', ['nullableArray' => 'nullable-array']],
        ];
    }

    public function provideFromHeadersWithNotNullableFieldsData(): array
    {
        return [
            'string' => [StringType::class, 'getString', '', ['string' => 'test-string']],
            'int' => [IntType::class, 'getInt', 0, ['int' => 'test-int']],
            'float' => [FloatType::class, 'getFloat', 0.0, ['float' => 'test-float']],
            'bool' => [BoolType::class, 'getBool', false, ['bool' => 'test-bool']],
            'dateTime' => [DateTimeType::class, 'getDateTime', new DateTimeImmutable(), ['dateTime' => 'test-date-time']],
            'array' => [ArrayType::class, 'getArray', [], ['array' => 'test-array']],
            'defaultValue' => [DefaultValueType::class, 'getDefault', [], ['default' => DefaultValueType::DEFAULT_VALUE]],
        ];
    }

    public function provideFromHeadersWithSetData(): array
    {
        return [
            'string' => [StringType::class, 'getString', ['HTTP_test-string' => 'test'], 'test', ['string' => 'test-string']],
            'int' => [IntType::class, 'getInt', ['HTTP_test-int' => '123'], 123, ['int' => 'test-int']],
            'float' => [FloatType::class, 'getFloat', ['HTTP_test-float' => '12.32'], 12.32, ['float' => 'test-float']],
            'boolFrom1String' => [BoolType::class, 'getBool', ['HTTP_test-bool' => '1'], true, ['bool' => 'test-bool']],
            'boolFrom0String' => [BoolType::class, 'getBool', ['HTTP_test-bool' => '0'], false, ['bool' => 'test-bool']],
            'boolFromEmptyString' => [BoolType::class, 'getBool', ['HTTP_test-bool' => ''], false, ['bool' => 'test-bool']],
            'boolFromFalse' => [BoolType::class, 'getBool', ['HTTP_test-bool' => 'false'], true, ['bool' => 'test-bool']],
            'dateTimeFromString' => [
                DateTimeType::class,
                'getDateTime',
                ['HTTP_test-date-time' => '2022-04-01 12:34:56'],
                new DateTimeImmutable('2022-04-01 12:34:56'),
                ['dateTime' => 'test-date-time'],
            ],
            'dateTimeFromUnixTimestamp' => [
                DateTimeType::class,
                'getDateTime',
                ['HTTP_test-date-time' => '1650383951'],
                new DateTimeImmutable('@'. 1650383951),
                ['dateTime' => 'test-date-time'],
            ],
            'defaultValue' => [
                DefaultValueType::class,
                'getDefault',
                ['HTTP_test-default' => DefaultValueType::NON_DEFAULT_VALUE],
                DefaultValueType::NON_DEFAULT_VALUE,
                ['default' => 'test-default'],
            ],
        ];
    }
}
