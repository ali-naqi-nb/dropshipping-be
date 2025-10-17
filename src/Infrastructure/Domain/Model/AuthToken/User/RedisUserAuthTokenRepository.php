<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\AuthToken\User;

use App\Domain\Model\AuthToken\User\UserAuthTokenRepositoryInterface;
use App\Domain\Model\Session\SessionUserInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

final class RedisUserAuthTokenRepository implements UserAuthTokenRepositoryInterface
{
    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function findNextUserId(string $userId, string $token): string
    {
        return sprintf('%s-%s', $userId, $token);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findValueById(string $authTokenId): ?SessionUserInterface
    {
        return $this->cache->getItem($authTokenId)->get();
    }
}
