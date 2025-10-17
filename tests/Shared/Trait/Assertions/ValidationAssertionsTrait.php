<?php

declare(strict_types=1);

namespace App\Tests\Shared\Trait\Assertions;

use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Error\ConstraintViolationList;

trait ValidationAssertionsTrait
{
    protected function assertNoErrors(ConstraintViolationList $errors): void
    {
        $this->assertFalse($errors->hasErrors());
        $this->assertSame(0, $errors->count());
        $this->assertEmpty($errors->getAll());
    }

    protected function assertErrors(array $expectedErrors, ConstraintViolationList $errors): void
    {
        $this->assertTrue($errors->hasErrors());

        $mappedErrors = array_map(
            fn (ConstraintViolation $error) => ['path' => $error->getPath(), 'message' => $error->getMessage()],
            $errors->getAll()
        );

        $this->assertSame($expectedErrors, $mappedErrors);

        $this->assertSame(count($expectedErrors), $errors->count());
    }

    protected function assertErrorsIgnoreOrder(array $expectedErrors, ConstraintViolationList $errors): void
    {
        $this->assertTrue($errors->hasErrors());

        $mappedErrors = array_map(
            fn (ConstraintViolation $error) => ['path' => $error->getPath(), 'message' => $error->getMessage()],
            $errors->getAll()
        );

        $this->assertSame(count($expectedErrors), $errors->count());

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $mappedErrors);
        }
    }
}
