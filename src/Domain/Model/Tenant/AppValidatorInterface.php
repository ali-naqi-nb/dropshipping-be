<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Error\ValidatorInterface;

interface AppValidatorInterface extends ValidatorInterface
{
    public function validateAppId(string $appId): ?ConstraintViolation;

    public function validateExchangeTokenAppId(string $appId): ?ConstraintViolation;

    public function validateAppInstalledAndActive(string $tenantId, string $appId): ?ConstraintViolation;
}
