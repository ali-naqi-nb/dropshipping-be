<?php

namespace App\Infrastructure\Domain\Model\Order\AliExpress;

use App\Domain\Model\Order\AliExpress\AliExpressCitiesRepositoryInterface;
use App\Infrastructure\Domain\Service\DsCitiesRepository;

final class RedisAliExpressCitiesRepository extends DsCitiesRepository implements AliExpressCitiesRepositoryInterface
{
    protected const CACHE_KEY_SUFFIX = 'ali_express_';
}
