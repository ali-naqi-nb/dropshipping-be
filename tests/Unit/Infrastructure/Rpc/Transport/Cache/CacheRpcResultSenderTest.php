<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\Cache;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\Cache\CacheKeyGeneratorInterface;
use App\Infrastructure\Rpc\Transport\Cache\CacheRpcResultSender;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

final class CacheRpcResultSenderTest extends UnitTestCase
{
    private CacheKeyGeneratorInterface&MockObject $cacheKeyGenerator;
    private SerializerInterface&MockObject $serializer;
    private AdapterInterface&MockObject $cache;
    private ClockInterface&MockObject $clock;
    private LoggerInterface&MockObject $logger;
    private CacheRpcResultSender $resultSender;
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

        $this->resultSender = new CacheRpcResultSender(
            $this->cacheKeyGenerator,
            $this->serializer,
            $this->cache,
            $this->clock,
            $this->logger,
        );
    }

    public function testSend(): void
    {
        $this->cacheKeyGenerator->method('get')->willReturn('cacheKey');
        $this->serializer->method('serialize')->willReturn('serializedResult');

        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT - 5)))
        ;

        $cacheItem = new CacheItem();

        $this->cache->method('getItem')
            ->with('cacheKey')
            ->willReturn($cacheItem);

        $rpcCommand = RpcCommandFactory::getRpcCommand();
        $rpcCommandResult = RpcResultFactory::getRpcCommandResult();

        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->callback(function (CacheItem $cacheItem) {
                return 'serializedResult' === $cacheItem->get();
            }));

        $this->resultSender->send($rpcCommand, $rpcCommandResult);

        $this->assertEquals([
            'debug' => [
                [
                    'CacheRpcResultSender: sending a result to cache',
                    [
                        'commandId' => $rpcCommandResult->getCommandId(),
                        'cacheKey' => 'cacheKey',
                        'secondsUntilTimeout' => 5,
                    ],
                ],
            ],
        ], $this->logs);
    }
}
