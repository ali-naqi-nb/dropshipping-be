<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Cache;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;

final class CacheRpcResultSender implements RpcResultSenderInterface
{
    public function __construct(
        private readonly CacheKeyGeneratorInterface $cacheKeyGenerator,
        private readonly SerializerInterface $serializer,
        private readonly CacheItemPoolInterface $cache,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(RpcCommand $command, RpcResult $result): void
    {
        $serializedResult = $this->serializer->serialize($result, 'json');
        $cacheKey = $this->cacheKeyGenerator->get($result->getCommandId());

        $currentTimestamp = $this->clock->now()->getTimestamp();
        $secondsUntilTimeout = $command->getTimeoutAt() - $currentTimestamp;

        $this->logger->debug('CacheRpcResultSender: sending a result to cache', [
            'commandId' => $result->getCommandId(),
            'cacheKey' => $cacheKey,
            'secondsUntilTimeout' => $secondsUntilTimeout,
        ]);

        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set($serializedResult);
        $cacheItem->expiresAfter($secondsUntilTimeout);

        $this->cache->save($cacheItem);
    }
}
