<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

class DbConfig
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $user,
        private readonly string $password,
        private readonly string $database,
        private readonly string $dbHost,
        private readonly int $dbPort
    ) {
    }

    public static function fromString(string $tenantId, string $data): self
    {
        [$user, $password, $database, $host, $port] = explode('|', $data);

        return new self($tenantId, $user, $password, $database, $host, (int) $port);
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function getDbPort(): int
    {
        return $this->dbPort;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s|%s|%s|%s|%d',
            $this->getUser(),
            $this->getPassword(),
            $this->getDatabase(),
            $this->getDbHost(),
            $this->getDbPort()
        );
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
