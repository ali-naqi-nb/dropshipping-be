<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Product;

use App\Application\Service\TranslatorInterface;
use App\Infrastructure\Domain\Model\Product\AeProductImportProductValidator;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Shared\Factory\LocaleFactory;
use App\Tests\Shared\Trait\Assertions\ValidationAssertionsTrait;

final class AeProductImportProductValidatorTest extends IntegrationTestCase
{
    use ValidationAssertionsTrait;

    private AeProductImportProductValidator $validator;

    private const REQUIRED_DATA = [
        'aeProductUrl' => Factory::AE_PRODUCT_URL,
        'aeProductShipsTo' => Factory::AE_PRODUCT_SHIPS_TO,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AeProductImportProductValidator $validator */
        $validator = self::getContainer()->get(AeProductImportProductValidator::class);
        $this->validator = $validator;

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);
        $translator->setLocale(LocaleFactory::EN);
    }

    public function testValidateWithValidData(): void
    {
        $this->assertNoErrors($this->validator->validate(self::REQUIRED_DATA));
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testValidateWithInvalidData(array $data, array $expectedErrors): void
    {
        $this->assertErrors($expectedErrors, $this->validator->validate($data));
    }

    public function provideInvalidData(): array
    {
        return [
            'empty' => [
                [
                    'aeProductUrl' => '',
                    'aeProductShipsTo' => '',
                ],
                [
                    ['path' => 'aeProductUrl', 'message' => 'This value should not be blank.'],
                    ['path' => 'aeProductShipsTo', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalidUrl' => [
                array_merge(self::REQUIRED_DATA, [
                    'aeProductUrl' => 'https://www.google.com',
                ]),
                [
                    ['path' => 'aeProductUrl', 'message' => 'Invalid AliExpress product URL.'],
                ],
            ],
            'invalidCountry' => [
                array_merge(self::REQUIRED_DATA, [
                    'aeProductShipsTo' => '77',
                ]),
                [
                    ['path' => 'aeProductShipsTo', 'message' => 'This value is not a valid country.'],
                ],
            ],
        ];
    }
}
