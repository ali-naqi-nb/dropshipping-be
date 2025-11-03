<?php

declare(strict_types=1);

namespace App\Application\Shared\Error;

use App\Domain\Model\Bus\Command\CommandResponseInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Error\ConstraintViolationList;
use App\Domain\Model\Error\ErrorType;

final class ErrorResponse implements CommandResponseInterface, QueryResponseInterface
{
    private const MESSAGE_NOT_FOUND = 'Not Found';

    private function __construct(
        private readonly ConstraintViolationList|ConstraintViolation $errors,
        private readonly ErrorType $type = ErrorType::Invalid
    ) {
    }

    public static function fromConstraintViolationList(ConstraintViolationList $errors, ErrorType $type = ErrorType::Invalid): self
    {
        return new self($errors, $type);
    }

    public static function fromConstraintViolation(ConstraintViolation $error, ErrorType $type = ErrorType::Error): self
    {
        return new self($error, $type);
    }

    public static function fromError(string $message, string $path): self
    {
        return new self(new ConstraintViolation($message, $path), ErrorType::Error);
    }

    public static function fromCommonError(string $message): self
    {
        return new self(new ConstraintViolation($message), ErrorType::Error);
    }

    public static function notFound(string $message = self::MESSAGE_NOT_FOUND): self
    {
        return new self(new ConstraintViolation($message, ConstraintViolation::PATH_MESSAGE), ErrorType::NotFound);
    }

    /** @return array<string, string> */
    public function getErrors(): array
    {
        if ($this->errors instanceof ConstraintViolation) {
            return [$this->errors->getPath() => $this->errors->getMessage()];
        }

        $errors = [];
        foreach ($this->errors->getAll() as $error) {
            // We want to return the first error for each path
            if (!isset($errors[$error->getPath()])) {
                $errors[$error->getPath()] = $error->getMessage();
            }
        }

        return $errors;
    }

    public function getType(): ErrorType
    {
        return $this->type;
    }
}
