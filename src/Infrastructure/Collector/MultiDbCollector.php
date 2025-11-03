<?php

namespace App\Infrastructure\Collector;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MultiDbCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly Connection $defaultConnection,
        private readonly Connection $tenantConnection
    ) {
    }

    public function getDefault(): string
    {
        return $this->data['default'];
    }

    public function getTenant(): string
    {
        return $this->data['tenant'];
    }

    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        $this->data['default'] = $this->defaultConnection->getDatabase();
        $this->data['tenant'] = $this->tenantConnection->isConnected() ? $this->tenantConnection->getDatabase() : null;
    }

    public function getName(): string
    {
        return 'database_connections';
    }
}
