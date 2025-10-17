<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Cache;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;

final class CacheRpcResultReceiver implements RpcResultReceiverInterface
{
    private const DEFAULT_SLEEP_TIME_MS = 100;

    public function __construct(
        private readonly CacheKeyGeneratorInterface $cacheKeyGenerator,
        private readonly SerializerInterface $serializer,
        private readonly CacheItemPoolInterface $cache,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
        private readonly int $sleepTimeMs = self::DEFAULT_SLEEP_TIME_MS,
    ) {
    }

    public function receive(RpcCommand $command): RpcResult
    {
        $cacheKey = $this->cacheKeyGenerator->get($command->getCommandId());

        while (true) {
            /** @var CacheItem $cacheItem */
            $cacheItem = $this->cache->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                $this->logger->debug('CacheRpcResultReceiver: received a result', [
                    'result' => $cacheItem->get(),
                ]);

                $result = $cacheItem->get();

                return $this->serializer->deserialize($result, RpcResult::class, 'json');
            }

            $currentTimestamp = $this->clock->now()->getTimestamp();
            if ($currentTimestamp > $command->getTimeoutAt()) {
                $this->logger->warning('CacheRpcResultReceiver: the command has timed out');

                throw new TimeoutException();
            }

            usleep($this->sleepTimeMs);
        }
    }
}
