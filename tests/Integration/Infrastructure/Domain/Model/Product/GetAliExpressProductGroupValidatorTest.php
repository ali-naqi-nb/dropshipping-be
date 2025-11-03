<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\GetAliExpressProductGroupValidatorInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Trait\Assertions\ValidationAssertionsTrait;

final class GetAliExpressProductGroupValidatorTest extends IntegrationTestCase
{
    use ValidationAssertionsTrait;

    private GetAliExpressProductGroupValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var GetAliExpressProductGroupValidatorInterface $validator */
        $validator = self::getContainer()->get(GetAliExpressProductGroupValidatorInterface::class);
        $this->validator = $validator;
    }

    /**
     * @dataProvider provideValidData
     */
    public function testValidateWithValidData(array $data): void
    {
        $result = $this->validator->validate($data);
        $this->assertNoErrors($result);
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testValidateWithInvalidData(array $data, array $expectedErrors): void
    {
        $result = $this->validator->validate($data);
        $this->assertErrors($expectedErrors, $result);
    }

    public function provideValidData(): array
    {
        return [
            'valid UUID v4' => [
                ['id' => '550e8400-e29b-41d4-a716-446655440000'],
            ],
            'valid UUID v1' => [
                ['id' => '12345678-1234-1234-8234-123456789012'],
            ],
            'valid UUID lowercase' => [
                ['id' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11'],
            ],
            'valid UUID uppercase' => [
                ['id' => 'A0EEBC99-9C0B-4EF8-BB6D-6BB9BD380A11'],
            ],
        ];
    }

    public function provideInvalidData(): array
    {
        return [
            'missing id field' => [
                [],
                [
                    ['path' => 'id', 'message' => 'This field is missing.'],
                ],
            ],
            'empty id' => [
                ['id' => ''],
                [
                    ['path' => 'id', 'message' => 'Product group ID is required.'],
                ],
            ],
            'null id' => [
                ['id' => null],
                [
                    ['path' => 'id', 'message' => 'Product group ID is required.'],
                ],
            ],
            'invalid UUID format' => [
                ['id' => 'invalid-uuid'],
                [
                    ['path' => 'id', 'message' => 'Product group ID must be a valid UUID.'],
                ],
            ],
            'UUID without dashes' => [
                ['id' => '550e8400e29b41d4a716446655440000'],
                [
                    ['path' => 'id', 'message' => 'Product group ID must be a valid UUID.'],
                ],
            ],
            'UUID with extra characters' => [
                ['id' => '550e8400-e29b-41d4-a716-446655440000-extra'],
                [
                    ['path' => 'id', 'message' => 'Product group ID must be a valid UUID.'],
                ],
            ],
            'numeric id' => [
                ['id' => 123],
                [
                    ['path' => 'id', 'message' => 'Product group ID must be a valid UUID.'],
                ],
            ],
            'short string' => [
                ['id' => 'abc123'],
                [
                    ['path' => 'id', 'message' => 'Product group ID must be a valid UUID.'],
                ],
            ],
        ];
    }
}
