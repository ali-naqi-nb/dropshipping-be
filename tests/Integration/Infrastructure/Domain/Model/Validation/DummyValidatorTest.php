<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Validation;

use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Error\ConstraintViolationList;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class DummyValidatorTest extends IntegrationTestCase
{
    private DummyValidator $validator;

    protected function setUp(): void
    {
        /** @var SymfonyValidatorInterface $symfonyValidator */
        $symfonyValidator = self::getContainer()->get(SymfonyValidatorInterface::class);

        $this->validator = new DummyValidator($symfonyValidator);
    }

    /**
     * @dataProvider provideValidateData
     */
    public function testValidate(array $data, ConstraintViolationList $expectedViolationsList): void
    {
        $this->assertEquals($expectedViolationsList, $this->validator->validate($data));
    }

    public function provideValidateData(): array
    {
        return [
            'no errors' => [
                'data' => [
                    'foo' => 'bar',
                ],
                'expectedViolationsList' => new ConstraintViolationList([]),
            ],
            'errors' => [
                'data' => [],
                'expectedViolationsList' => new ConstraintViolationList([
                    new ConstraintViolation('This field is missing.', 'foo'),
                ]),
            ],
        ];
    }
}
