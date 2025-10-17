<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Error;

use App\Domain\Model\Error\ConstraintViolation;
use App\Tests\Shared\Factory\ConstraintViolationFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class ConstraintViolationTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $constraintViolation = new ConstraintViolation(Factory::MESSAGE, Factory::PATH);

        $this->assertSame(Factory::MESSAGE, $constraintViolation->getMessage());
        $this->assertSame(Factory::PATH, $constraintViolation->getPath());
    }

    public function testConstructorWithOnlyRequiredData(): void
    {
        $constraintViolation = new ConstraintViolation(Factory::MESSAGE);

        $this->assertSame(Factory::MESSAGE, $constraintViolation->getMessage());
        $this->assertSame(Factory::PATH_COMMON, $constraintViolation->getPath());
    }
}
