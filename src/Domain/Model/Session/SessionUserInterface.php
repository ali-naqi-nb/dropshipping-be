<?php

declare(strict_types=1);

namespace App\Domain\Model\Session;

/**
 * @property string $email
 * @property array  $permissions
 */
interface SessionUserInterface
{
    public function getUserId(): string;

    public function getEmail(): string;

    public function getPermissions(): array;

    public function isSuperAdmin(): bool;

    public function isNbEmployee(): bool;

    /** @return string[] */
    public function getCompaniesIds(): array;

    public function hasAccessToCompany(?string $companyId): bool;
}
