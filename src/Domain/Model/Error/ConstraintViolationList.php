<?php

declare(strict_types=1);

namespace App\Domain\Model\Error;

final class ConstraintViolationList
{
    /** @param ConstraintViolation[] $errors */
    public function __construct(private array $errors = [])
    {
    }

    public function count(): int
    {
        return count($this->errors);
    }

    public function hasErrors(): bool
    {
        return $this->count() > 0;
    }

    /**
     * @return ConstraintViolation[]
     */
    public function getAll(): array
    {
        return $this->errors;
    }

    public function addError(ConstraintViolation $error): void
    {
        $this->errors[] = $error;
    }
}
