<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Amqp;

use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\Server\RpcCommandServerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AmqpRpcCommandReceiver
{
    public function __construct(
        private readonly RpcCommandServerInterface $rpcServer,
        private readonly DoctrineTenantConnection $doctrineTenantConnection,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly TenantServiceInterface $service,
        private readonly RedisTenantConnection $redisTenantConnection,
    ) {
    }

    public function __invoke(RpcCommand $command): void
    {
        try {
            // set the tenant in the connection if tenant id is provided
            if (null !== $command->getTenantId()) {
                $dbConfig = $this->service->getDbConfig($command->getTenantId());
                if (null !== $dbConfig) {
                    $this->tenantStorage->setId($command->getTenantId());
                    $this->doctrineTenantConnection->create($dbConfig);
                    $this->redisTenantConnection->connect();
                }
            }
            $this->rpcServer->handle($command);
        } catch (TimeoutException) {
            // discard the message
            return;
        }
    }
}
