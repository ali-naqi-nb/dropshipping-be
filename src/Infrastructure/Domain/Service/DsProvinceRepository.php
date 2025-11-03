<?php

namespace App\Infrastructure\Domain\Service;

use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use DateInterval;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class DsProvinceRepository
{
    protected const TTL = 'PT24H';
    protected const CACHE_KEY_PREFIX = 'dropshipping_provinces_';
    protected const CACHE_KEY_SUFFIX = '';

    /** @param TagAwareAdapter $cache */
    public function __construct(protected readonly TagAwareCacheInterface $cache)
    {
    }

    /**
     * @return ?DsProvinceData[]
     *
     * @throws InvalidArgumentException
     */
    public function find(string $countryCode, ?string $provinceName = null): ?array
    {
        $provinces = $this->cache->getItem(static::CACHE_KEY_PREFIX.static::CACHE_KEY_SUFFIX.$countryCode)->get();

        if (null === $provinceName || null === $provinces) {
            return $provinces;
        }

        $filteredProvinces = array_filter(
            $provinces,
            function (DsProvinceData $province) use ($provinceName) {
                $lowerCase = mb_strtolower($provinceName);
                $provinceLowerCase = mb_strtolower($province->getProvinceName());

                return
                    $provinceLowerCase === $lowerCase
                    || str_contains($provinceLowerCase, $lowerCase);
            }
        );

        return array_values($filteredProvinces);
    }

    /**
     * @param DsProvinceData[] $data
     *
     * @throws InvalidArgumentException
     */
    public function save(string $countryCode, array $data): void
    {
        $cacheItem = $this->cache->getItem(static::CACHE_KEY_PREFIX.static::CACHE_KEY_SUFFIX.$countryCode);
        $cacheItem->set($data);
        $cacheItem->expiresAfter(new DateInterval(static::TTL));
        $this->cache->save($cacheItem);
    }

    public function remove(string $countryCode): void
    {
        $this->cache->delete(static::CACHE_KEY_PREFIX.static::CACHE_KEY_SUFFIX.$countryCode);
    }
}
