<?php

declare(strict_types=1);

namespace App\Domain\Model\Error;

interface ValidatorInterface
{
    public const GROUP_CREATE = 'Create';
    public const GROUP_UPDATE = 'Update';
    public const GROUP_DELETE = 'Delete';

    public function validate(
        array $data,
        string $group = null,
        bool $allowExtraFields = true,
        bool $allowMissingFields = false,
    ): ConstraintViolationList;
}
