<?php

namespace App\Tests\Integration\Infrastructure\Domain\Model\Order\AliExpress;

use App\Domain\Model\Order\AliExpress\AliExpressProvinceRepositoryInterface;
use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeDsProvincesFactory;
use DateInterval;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class RedisAliExpressProvincesRepositoryTest extends IntegrationTestCase
{
    private AliExpressProvinceRepositoryInterface $repository;
    private TagAwareAdapter $cache;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AliExpressProvinceRepositoryInterface $repository */
        $repository = self::getContainer()->get(AliExpressProvinceRepositoryInterface::class);
        $this->repository = $repository;

        /** @var TagAwareAdapter $cache */
        $cache = self::getContainer()->get(TagAwareCacheInterface::class);
        $this->cache = $cache;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cache->delete(AeDsProvincesFactory::CACHE_KEY);
    }

    public function testFindReturnNull(): void
    {
        $this->assertNull($this->getCachedItems());
        $this->assertNull($this->repository->find('foo'));
    }

    public function testFindWithProvinceNameReturnNull(): void
    {
        $this->assertNull($this->getCachedItems());
        $this->assertNull($this->repository->find(AeDsProvincesFactory::COUNTRY_CODE, AeDsProvincesFactory::PROVINCE_NAME));
    }

    public function testFindReturnDsProvinceData(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();

        $this->assertNotEmpty($cachedItems);
        $this->assertEquals($cachedItems, $this->repository->find(AeDsProvincesFactory::COUNTRY_CODE, AeDsProvincesFactory::PROVINCE_NAME));
    }

    public function testSaveWithNewData(): void
    {
        $this->assertNull($this->getCachedItems());
        $provincesData = [AeDsProvincesFactory::getDsProvince()];

        $this->repository->save(AeDsProvincesFactory::COUNTRY_CODE, $provincesData);
        $cachedItems = $this->getCachedItems();
        $this->assertEquals($provincesData, $cachedItems);
    }

    public function testSaveOverwriteExistingData(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();
        $this->assertNotEmpty($cachedItems);

        $provincesData = [AeDsProvincesFactory::getDsProvince()];
        $provincesData = array_merge($cachedItems, $provincesData);
        $this->assertNotEquals($cachedItems, $provincesData);

        $this->repository->save(AeDsProvincesFactory::COUNTRY_CODE, $provincesData);
        $cachedItems = $this->getCachedItems();
        $this->assertEquals($provincesData, $cachedItems);
    }

    public function testRemove(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();
        $this->assertNotEmpty($cachedItems);

        $this->repository->remove(AeDsProvincesFactory::COUNTRY_CODE);
        $cachedItems = $this->getCachedItems();
        $this->assertEmpty($cachedItems);
    }

    private function seedCache(): void
    {
        $provincesData = [AeDsProvincesFactory::getDsProvince()];
        $cacheItem = $this->cache->getItem(AeDsProvincesFactory::CACHE_KEY);
        $cacheItem->set($provincesData);
        $cacheItem->expiresAfter(new DateInterval(AeDsProvincesFactory::CACHE_TTL));
        $this->cache->save($cacheItem);
    }

    /** @return ?DsProvinceData[]
     * @throws InvalidArgumentException
     */
    private function getCachedItems(): ?array
    {
        return $this->cache->getItem(AeDsProvincesFactory::CACHE_KEY)->get();
    }
}
