<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db\Fixtures\Main;

use App\Tests\Shared\Factory\AuthTokenFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @codeCoverageIgnore
 */
final class AuthTokenFixtures extends Fixture implements FixtureGroupInterface
{
    /** @param TagAwareAdapter $cache */
    public function __construct(
        Connection $connection,
        string $resourceDir,
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->cache->clear();

        $authToken = AuthTokenFactory::getUserAuthToken();
        $cacheItem = $this->cache->getItem($authToken->getId());
        $cacheItem->set($authToken->getValue());
        $cacheItem->expiresAfter($authToken->getExpiresAfter());
        $this->cache->save($cacheItem);
    }

    public static function getGroups(): array
    {
        return ['test-main'];
    }
}
