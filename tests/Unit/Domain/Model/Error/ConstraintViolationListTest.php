<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Error;

use App\Domain\Model\Error\ConstraintViolationList;
use App\Tests\Shared\Factory\ConstraintViolationFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class ConstraintViolationListTest extends UnitTestCase
{
    public function testDefaultConstructorAndGetters(): void
    {
        $constraintViolationList = new ConstraintViolationList();

        $this->assertSame(0, $constraintViolationList->count());
        $this->assertFalse($constraintViolationList->hasErrors());
        $this->assertSame([], $constraintViolationList->getAll());
    }

    public function testConstructorAndGetters(): void
    {
        $errors = [
            Factory::getConstraintViolation(),
            Factory::getConstraintViolation(),
        ];
        $constraintViolationList = new ConstraintViolationList($errors);

        $this->assertSame(2, $constraintViolationList->count());
        $this->assertTrue($constraintViolationList->hasErrors());
        $this->assertSame($errors, $constraintViolationList->getAll());
    }

    public function testAddErrors(): void
    {
        $constraintViolationList = new ConstraintViolationList();

        $this->assertSame(0, $constraintViolationList->count());
        $this->assertFalse($constraintViolationList->hasErrors());

        $error = Factory::getConstraintViolation();
        $constraintViolationList->addError($error);
        $this->assertSame(1, $constraintViolationList->count());
        $this->assertTrue($constraintViolationList->hasErrors());
        $this->assertSame([$error], $constraintViolationList->getAll());
    }
}
