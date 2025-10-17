<?php

declare(strict_types=1);

namespace App\Domain\Model\AuthToken\User;

use App\Domain\Model\Session\SessionUserInterface;

interface UserAuthTokenRepositoryInterface
{
    public function findNextUserId(string $userId, string $token): string;

    public function findValueById(string $authTokenId): ?SessionUserInterface;
}
