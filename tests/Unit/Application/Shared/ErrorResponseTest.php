<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared;

use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ConstraintViolationList;
use App\Domain\Model\Error\ErrorType;
use App\Tests\Shared\Factory\ErrorResponseFactory;
use App\Tests\Unit\UnitTestCase;

final class ErrorResponseTest extends UnitTestCase
{
    public function testFromConstraintViolationList(): void
    {
        $errors = new ConstraintViolationList();
        $errors->addError(ErrorResponseFactory::getConstraintViolation());
        $errorResponse = ErrorResponse::fromConstraintViolationList($errors);

        $this->assertSame(ErrorType::Invalid, $errorResponse->getType());
        $this->assertSame(['id' => ErrorResponseFactory::MESSAGE_INVALID_UUID], $errorResponse->getErrors());
    }

    public function testFromConstraintViolation(): void
    {
        $error = ErrorResponseFactory::getConstraintViolation();
        $errorResponse = ErrorResponse::fromConstraintViolation($error);

        $this->assertSame(ErrorType::Error, $errorResponse->getType());
        $this->assertSame(ErrorResponseFactory::ROOT_ID, $errorResponse->getErrors());
    }

    public function testFromCommonError(): void
    {
        $errorResponse = ErrorResponse::fromCommonError(ErrorResponseFactory::MESSAGE_CODE_NOT_UNIQUE);

        $this->assertSame(ErrorType::Error, $errorResponse->getType());
        $this->assertSame(['common' => ErrorResponseFactory::MESSAGE_CODE_NOT_UNIQUE], $errorResponse->getErrors());
    }

    public function testNotFound(): void
    {
        $errorResponse = ErrorResponse::notFound();

        $this->assertSame(ErrorType::NotFound, $errorResponse->getType());
        $this->assertSame(['message' => ErrorResponseFactory::MESSAGE_NOT_FOUND], $errorResponse->getErrors());
    }
}
