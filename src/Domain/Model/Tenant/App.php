<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

final class App
{
    public const CONFIG_DEFAULTS = [
        'isActive' => false,
        'isInstalled' => false,
    ];
    private AppId $appId;
    private array $config;

    public function __construct(
        AppId $appId,
        array $config,
    ) {
        $this->appId = $appId;
        $this->config = array_merge(self::CONFIG_DEFAULTS, $config);
    }

    public function getAppId(): AppId
    {
        return $this->appId;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function isInstalled(): bool
    {
        return $this->config['isInstalled'];
    }

    public function install(): void
    {
        $this->config['isInstalled'] = true;
    }

    public function isActive(): bool
    {
        return $this->config['isActive'] ?? false;
    }

    public static function fromAppData(AppId $appId, array $appData): self
    {
        return new self($appId, $appData);
    }

    public static function createWithDefaults(AppId $appId): self
    {
        return new self($appId, ['isActive' => false, 'isInstalled' => false]);
    }

    public function appendConfig(string $key, mixed $value): array
    {
        $this->config[$key] = $value;

        return $this->config;
    }
}
