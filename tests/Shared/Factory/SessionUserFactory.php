<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Infrastructure\Domain\Model\Session\SessionUser;

final class SessionUserFactory
{
    public const USER_ID = '6998d1fc-5614-42e2-bfde-1eacb7de8e43';
    public const EMAIL = 'test@nextbasket.com';
    public const PERMISSIONS = ['canCreateCompany'];
    public const COMPANY_ID = '9c4bb7f8-59df-48b1-9f65-ec143ef4652c';
    public const SECOND_COMPANY_ID = '64739949-9141-49b8-b644-905cf896cf95';

    public static function getSessionUser(
        string $userId = self::USER_ID,
        string $email = self::EMAIL,
        array $permissions = self::PERMISSIONS,
        bool $isSuperAdmin = true,
        bool $isNbEmployee = false,
        array $companiesIds = [self::COMPANY_ID]
    ): SessionUser {
        return new SessionUser($userId, $email, $permissions, $isSuperAdmin, $isNbEmployee, $companiesIds);
    }
}
