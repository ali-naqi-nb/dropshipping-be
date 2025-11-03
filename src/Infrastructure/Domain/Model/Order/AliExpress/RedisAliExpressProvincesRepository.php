<?php

namespace App\Infrastructure\Domain\Model\Order\AliExpress;

use App\Domain\Model\Order\AliExpress\AliExpressProvinceRepositoryInterface;
use App\Infrastructure\Domain\Service\DsProvinceRepository;

final class RedisAliExpressProvincesRepository extends DsProvinceRepository implements AliExpressProvinceRepositoryInterface
{
    protected const CACHE_KEY_SUFFIX = 'ali_express_';
}
