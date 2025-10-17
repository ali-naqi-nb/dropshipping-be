<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model;

use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Error\ConstraintViolationList;
use App\Domain\Model\Error\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface as SymfonyConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface as SymfonyConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    public function __construct(protected readonly SymfonyValidatorInterface $validator)
    {
    }

    public function validate(
        array $data,
        string $group = null,
        bool $allowExtraFields = true,
        bool $allowMissingFields = false,
    ): ConstraintViolationList {
        $errors = $this->validator->validate(
            $data,
            new Assert\Collection(
                fields: $this->getFields($group),
                allowExtraFields: $allowExtraFields,
                allowMissingFields: $allowMissingFields,
            )
        );

        return $this->formatErrors($errors);
    }

    /**
     * @return array<string, array> Key is field name and value is array of constraints
     */
    abstract protected function getFields(?string $group = null): array;

    protected function formatErrors(SymfonyConstraintViolationListInterface $errors): ConstraintViolationList
    {
        $response = new ConstraintViolationList();
        if (0 === $errors->count()) {
            return $response;
        }

        /** @var SymfonyConstraintViolationInterface $error */
        foreach ($errors as $error) {
            /** @var string $path */
            $path = preg_replace(['/]\[/', '/\[/', '/]/'], ['.', '', ''], $error->getPropertyPath());
            $response->addError(new ConstraintViolation((string) $error->getMessage(), $path));
        }

        return $response;
    }
}
