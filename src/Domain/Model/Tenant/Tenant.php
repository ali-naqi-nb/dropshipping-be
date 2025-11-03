<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use Datetime;
use Symfony\Component\Uid\Uuid;

class Tenant
{
    private Uuid $id;
    private Uuid $companyId;

    private ?DateTime $configuredAt = null;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt = null;
    private bool $isAvailable = false;
    private string $status;
    private ?array $apps = null;

    public function __construct(
        string $id,
        string $companyId,
        private readonly string $domain,
        private string $dbConfig,
        private string $defaultLanguage,
        private string $defaultCurrency,
        ShopStatus $status,
    ) {
        $time = new Datetime();
        $this->setId($id);
        $this->setCompanyId($companyId);
        $this->status = $status->value;
        $this->createdAt = $time;
        $this->updatedAt = $time;
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getCompanyId(): string
    {
        return (string) $this->companyId;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getDbConfig(): string
    {
        return $this->dbConfig;
    }

    public function setDbConfig(string $dbConfig): void
    {
        $this->dbConfig = $dbConfig;
        if (!empty($dbConfig)) {
            $this->setConfiguredAt(new Datetime());
        }
    }

    private function setId(string $id): void
    {
        $this->id = Uuid::fromString($id);
    }

    private function setCompanyId(string $companyId): void
    {
        $this->companyId = Uuid::fromString($companyId);
    }

    public function setConfiguredAt(?Datetime $configuredAt): void
    {
        $this->configuredAt = $configuredAt;
    }

    public function getConfiguredAt(): ?Datetime
    {
        return $this->configuredAt;
    }

    public function getCreatedAt(): Datetime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): Datetime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?Datetime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?Datetime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function makeAvailable(): void
    {
        $this->isAvailable = true;
        $this->updatedAt = new Datetime();
    }

    public function makeUnavailable(): void
    {
        $this->isAvailable = false;
        $this->updatedAt = new Datetime();
    }

    public function setDefaultLanguage(string $defaultLanguage): void
    {
        $this->defaultLanguage = $defaultLanguage;
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultCurrency(string $defaultCurrency): void
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function setStatus(ShopStatus $status): void
    {
        $this->status = $status->value;
    }

    public function getStatus(): ShopStatus
    {
        return ShopStatus::from($this->status);
    }

    public function installApp(AppId $appId): void
    {
        $app = $this->getApp($appId);
        if (null === $app) {
            $app = App::createWithDefaults($appId);
        }
        $app->install();
        $this->apps[$app->getAppId()->value] = $app->getConfig();
    }

    public function getApp(AppId $appId): ?App
    {
        if (null === $this->apps || !isset($this->apps[$appId->value])) {
            return null;
        }

        return App::fromAppData($appId, $this->apps[$appId->value]);
    }

    public function isAppInstalled(AppId $appId): bool
    {
        $app = $this->getApp($appId);

        return !(null === $app) && $app->isInstalled();
    }

    public function populateApp(App $app): void
    {
        if (null === $this->apps) {
            $this->apps = [];
        }
        $this->apps[$app->getAppId()->value] = $app->getConfig();
    }

    public function removeApp(App $app): void
    {
        unset($this->apps[$app->getAppId()->value]);
        if ([] === $this->apps) {
            $this->apps = null;
        }
    }

    /** @return App[]|null */
    public function getApps(): ?array
    {
        if (null === $this->apps) {
            return null;
        }

        $apps = [];
        foreach ($this->apps as $appId => $appData) {
            $apps[] = App::fromAppData(AppId::from($appId), $appData);
        }

        return $apps;
    }
}
