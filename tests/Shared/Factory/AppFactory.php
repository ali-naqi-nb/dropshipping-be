<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;

final class AppFactory
{
    public const ALI_EXPRESS_ID = 'ali-express';
    public const ALI_EXPRESS_TOKEN = 'ali-express-token';
    public const ALI_EXPRESS_FAILED_TOKEN = 'ali-express-failed';

    public const APP_ID_NOT_SUPPORTED = 'not-supported-app-id';

    public const INSTALLED_AND_ACTIVATED_CONFIG = ['isActive' => true, 'isInstalled' => true];

    public const NOT_INSTALLED_AND_NOT_ACTIVATED_CONFIG = ['isActive' => false, 'isInstalled' => false];

    public const APP_DEFAULT_CONFIG = ['isActive' => false, 'isInstalled' => false];

    public const NEW_CONFIG = [
        'isInstalled' => true,
        'isActive' => true,
    ];

    public const NEW_CONFIG_2 = [
        'isActive' => false,
        'isInstalled' => true,
    ];

    public const ALI_EXPRESS_CONFIG = [
        'isActive' => true,
        'isInstalled' => true,
        'clientId' => 0,
    ];

    public const ALI_EXPRESS_NOT_INSTALLED_AND_NOT_ACTIVE_CONFIG = [
        'isActive' => false,
        'isInstalled' => false,
        'clientId' => 0,
    ];

    public static function getApp(
        string $appId = self::ALI_EXPRESS_ID,
        array $config = self::ALI_EXPRESS_CONFIG
    ): App {
        return new App(AppId::from($appId), $config);
    }
}
