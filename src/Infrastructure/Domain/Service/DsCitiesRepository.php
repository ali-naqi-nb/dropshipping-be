<?php

namespace App\Infrastructure\Domain\Service;

use App\Infrastructure\Domain\Model\Order\DsCityData;
use DateInterval;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

abstract class DsCitiesRepository
{
    protected const TTL = 'PT24H';
    protected const CACHE_KEY_PREFIX = 'dropshipping_cities_';
    protected const CACHE_KEY_SUFFIX = '';

    /** @param TagAwareAdapter $cache */
    public function __construct(protected readonly TagAwareCacheInterface $cache)
    {
    }

    /**
     * @return ?DsCityData[]
     *
     * @throws InvalidArgumentException
     */
    public function find(string $countryCode, ?string $cityName = null): ?array
    {
        $cities = $this->cache->getItem(static::CACHE_KEY_PREFIX.static::CACHE_KEY_SUFFIX.$countryCode)->get();

        if (null === $cityName || null === $cities) {
            return $cities;
        }

        $filteredCities = array_filter(
            $cities,
            function (DsCityData $city) use ($cityName) {
                $lowerCase = mb_strtolower($cityName);
                $cityLowerCase = mb_strtolower($city->getCityName());

                return
                    $cityLowerCase === $lowerCase
                    || str_contains($cityLowerCase, $lowerCase);
            }
        );

        return array_values($filteredCities);
    }

    /**
     * @param DsCityData[] $data
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
