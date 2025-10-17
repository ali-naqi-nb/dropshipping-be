<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Session;

use App\Domain\Model\Session\SessionUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SessionUser implements UserInterface, SessionUserInterface
{
    /**
     * @param string[] $permissions
     * @param string[] $companiesIds
     */
    public function __construct(
        private readonly string $userId,
        private readonly string $email,
        private readonly array $permissions,
        private readonly bool $isSuperAdmin,
        private readonly bool $isNbEmployee,
        private readonly array $companiesIds
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /** @return string[] */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /** @return string[] */
    public function getRoles(): array
    {
        return $this->getPermissions();
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function isNbEmployee(): bool
    {
        return $this->isNbEmployee;
    }

    /** @return string[] */
    public function getCompaniesIds(): array
    {
        return $this->companiesIds;
    }

    public function hasAccessToCompany(?string $companyId): bool
    {
        return $this->isSuperAdmin() || (null !== $companyId && in_array($companyId, $this->getCompaniesIds()));
    }
}
