<?php

namespace App\Tests\Integration\Infrastructure\Domain\Model\Order\AliExpress;

use App\Domain\Model\Order\AliExpress\AliExpressCitiesRepositoryInterface;
use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeDsCitiesFactory;
use DateInterval;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class RedisAliExpressCitiesRepositoryTest extends IntegrationTestCase
{
    private AliExpressCitiesRepositoryInterface $repository;
    private TagAwareAdapter $cache;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AliExpressCitiesRepositoryInterface $repository */
        $repository = self::getContainer()->get(AliExpressCitiesRepositoryInterface::class);
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
        $this->cache->delete(AeDsCitiesFactory::CACHE_KEY);
    }

    public function testFindReturnNull(): void
    {
        $this->assertNull($this->getCachedItems());
        $this->assertNull($this->repository->find('foo'));
    }

    public function testFindWithProvinceNameReturnNull(): void
    {
        $this->assertNull($this->getCachedItems());
        $this->assertNull($this->repository->find(AeDsCitiesFactory::COUNTRY_CODE, AeDsCitiesFactory::CITY_NAME));
    }

    public function testFindReturnDsProvinceData(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();

        $this->assertNotEmpty($cachedItems);
        $this->assertEquals($cachedItems, $this->repository->find(AeDsCitiesFactory::COUNTRY_CODE, AeDsCitiesFactory::CITY_NAME));
    }

    public function testSaveWithNewData(): void
    {
        $this->assertNull($this->getCachedItems());
        $citiesData = [AeDsCitiesFactory::getDsCity()];

        $this->repository->save(AeDsCitiesFactory::COUNTRY_CODE, $citiesData);
        $cachedItems = $this->getCachedItems();
        $this->assertEquals($citiesData, $cachedItems);
    }

    public function testSaveOverwriteExistingData(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();
        $this->assertNotEmpty($cachedItems);

        $citiesData = [AeDsCitiesFactory::getDsCity()];
        $citiesData = array_merge($cachedItems, $citiesData);
        $this->assertNotEquals($cachedItems, $citiesData);

        $this->repository->save(AeDsCitiesFactory::COUNTRY_CODE, $citiesData);
        $cachedItems = $this->getCachedItems();
        $this->assertEquals($citiesData, $cachedItems);
    }

    public function testRemove(): void
    {
        $this->seedCache();
        $cachedItems = $this->getCachedItems();
        $this->assertNotEmpty($cachedItems);

        $this->repository->remove(AeDsCitiesFactory::COUNTRY_CODE);
        $cachedItems = $this->getCachedItems();
        $this->assertEmpty($cachedItems);
    }

    private function seedCache(): void
    {
        $citiesData = [AeDsCitiesFactory::getDsCity()];
        $cacheItem = $this->cache->getItem(AeDsCitiesFactory::CACHE_KEY);
        $cacheItem->set($citiesData);
        $cacheItem->expiresAfter(new DateInterval(AeDsCitiesFactory::CACHE_TTL));
        $this->cache->save($cacheItem);
    }

    /** @return ?DsProvinceData[]
     * @throws InvalidArgumentException
     */
    private function getCachedItems(): ?array
    {
        return $this->cache->getItem(AeDsCitiesFactory::CACHE_KEY)->get();
    }
}
