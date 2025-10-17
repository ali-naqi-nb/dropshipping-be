<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\AuthToken\User\UserAuthToken;
use App\Infrastructure\Domain\Model\Session\SessionUser;

final class AuthTokenFactory
{
    public const ID = '6998d1fc-5614-42e2-bfde-1eacb7de8e43-'.self::TOKEN;
    public const TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IkhZSENsdzlOMkpLSFlqVXZMUlp3bU5RUXRzNFVGZE9PIn0.'
    .'eyJpYXQiOjE2NTAyNzAzMTIsImV4cCI6MTY1MDI3MTIxMiwibmJmIjoxNjUwMjcwOTEyLCJpc3MiOiJIWUhDbHc5TjJKS0hZalV2TFJad21OUVF'
    .'0czRVRmRPTyJ9.zqX-bwLSiUo2Kv-tAavnvnMEqw_Kgvi7_t7_EdXDcuUdgIVEgL-BHubH3zaRfedz4KMJGUMmbRrYPW0Uu3wzQA';

    public static function getUserAuthToken(
        string $id = self::ID,
        ?SessionUser $value = null,
        string $token = self::TOKEN
    ): UserAuthToken {
        $value = $value ?? SessionUserFactory::getSessionUser();

        return new UserAuthToken($id, $value, $token);
    }
}
