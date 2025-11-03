<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Model\AuthToken;

use App\Domain\Model\Session\SessionUserInterface;
use App\Infrastructure\Domain\Model\AuthToken\User\RedisUserAuthTokenRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AuthTokenFactory;
use App\Tests\Shared\Factory\SessionUserFactory;
use App\Tests\Shared\Factory\UserFactory;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class RedisUserAuthTokenRepositoryTest extends IntegrationTestCase
{
    private RedisUserAuthTokenRepository $repository;

    private TagAwareAdapter $cache;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RedisUserAuthTokenRepository $repository */
        $repository = self::getContainer()->get(RedisUserAuthTokenRepository::class);
        $this->repository = $repository;

        /** @var TagAwareAdapter $cache */
        $cache = self::getContainer()->get(TagAwareCacheInterface::class);
        $this->cache = $cache;
    }

    public function testFindNextUserIdReturnsCorrectId(): void
    {
        $expected = sprintf('%s-%s', UserFactory::ADMIN_ID, AuthTokenFactory::TOKEN);
        $this->assertSame($expected, $this->repository->findNextUserId(UserFactory::ADMIN_ID, AuthTokenFactory::TOKEN));
    }

    public function testFindValueByIdReturnsSessionUserInterface(): void
    {
        $id = $this->repository->findNextUserId(SessionUserFactory::USER_ID, AuthTokenFactory::TOKEN);
        $this->assertTrue($this->cache->getItem($id)->isHit());

        $result = $this->repository->findValueById($id);
        $this->assertInstanceOf(SessionUserInterface::class, $result);
        $this->assertSame(SessionUserFactory::USER_ID, $result->getUserId());
    }

    public function testFindValueByIdReturnsNull(): void
    {
        $id = $this->repository->findNextUserId(UserFactory::ADMIN_ID, AuthTokenFactory::TOKEN);
        $this->assertFalse($this->cache->getItem($id)->isHit());

        $this->assertNull($this->repository->findValueById($id));
    }
}
