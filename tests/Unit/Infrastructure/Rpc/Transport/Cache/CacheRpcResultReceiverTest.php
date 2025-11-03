<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\Cache;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\Cache\CacheKeyGeneratorInterface;
use App\Infrastructure\Rpc\Transport\Cache\CacheRpcResultReceiver;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

final class CacheRpcResultReceiverTest extends UnitTestCase
{
    private const SLEEP_TIME_MS = 2;

    private CacheKeyGeneratorInterface&MockObject $cacheKeyGenerator;
    private SerializerInterface&MockObject $serializer;
    private AdapterInterface&MockObject $cache;
    private ClockInterface&MockObject $clock;
    private LoggerInterface&MockObject $logger;
    private CacheRpcResultReceiver $resultReceiver;
    private array $logs = [];

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->cache = $this->createMock(AdapterInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cacheKeyGenerator = $this->createMock(CacheKeyGeneratorInterface::class);

        $this->logger->method('debug')
            ->willReturnCallback(function () {
                $this->logs['debug'][] = func_get_args();
            });
        $this->logger->method('warning')
            ->willReturnCallback(function () {
                $this->logs['warning'][] = func_get_args();
            });

        $this->resultReceiver = new CacheRpcResultReceiver(
            $this->cacheKeyGenerator,
            $this->serializer,
            $this->cache,
            $this->clock,
            $this->logger,
            self::SLEEP_TIME_MS,
        );
    }

    public function testReceive(): void
    {
        $this->cacheKeyGenerator->method('get')->willReturn('cacheKey');
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT - 5)))
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();
        $rpcResult = RpcResultFactory::getRpcCommandResult();

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('serializedResult', RpcResult::class, 'json')
            ->willReturn($rpcResult)
        ;

        $cacheIsHitProperty = new ReflectionProperty(CacheItem::class, 'isHit');

        $nonHitCacheItem = new CacheItem();
        $cacheIsHitProperty->setValue($nonHitCacheItem, false);

        $cacheItem = new CacheItem();
        $cacheIsHitProperty->setValue($cacheItem, true);
        $cacheItem->set('serializedResult');

        $this->cache->method('getItem')
            ->with('cacheKey')
            ->willReturnOnConsecutiveCalls($nonHitCacheItem, $cacheItem);

        $result = $this->resultReceiver->receive($rpcCommand);

        $this->assertSame($rpcResult, $result);
        $this->assertSame([
            'debug' => [
                [
                    'CacheRpcResultReceiver: received a result',
                    [
                        'result' => 'serializedResult',
                    ],
                ],
            ],
        ], $this->logs);
    }

    public function testTimeout(): void
    {
        $this->cacheKeyGenerator->method('get')->willReturn('cacheKey');

        $this->clock->method('now')
            ->willReturnOnConsecutiveCalls(
                new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT - 5)),
                new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT + 1))
            )
        ;

        $cacheIsHitProperty = new ReflectionProperty(CacheItem::class, 'isHit');

        $nonHitCacheItem = new CacheItem();
        $cacheIsHitProperty->setValue($nonHitCacheItem, false);

        $this->cache->method('getItem')
            ->with('cacheKey')
            ->willReturn($nonHitCacheItem);

        $this->expectException(TimeoutException::class);
        $this->resultReceiver->receive(RpcCommandFactory::getRpcCommand());

        $this->assertSame([
            'warning' => [
                [
                    'CacheRpcResultReceiver: the command has timed out',
                ],
            ],
        ], $this->logs);
    }
}
