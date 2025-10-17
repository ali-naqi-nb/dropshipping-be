<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Connection;

use App\Domain\Model\Tenant\DbConfig;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;

final class DoctrineTenantConnection extends Connection
{
    private bool $isConnected = false;

    protected array $_params = [];

    private ?string $tenantId = null;

    public function __construct(array $params, Driver $driver, Configuration $config = null, ?EventManager $eventManager = null)
    {
        $this->_params = $params;
        parent::__construct($params, $driver, $config, $eventManager);
    }

    public function create(DbConfig $dbConfig): void
    {
        if ($this->isConnected()) {
            $this->close();
        }

        $this->tenantId = $dbConfig->getTenantId();
        $this->_params['host'] = $dbConfig->getDbHost();
        $this->_params['dbname'] = $dbConfig->getDatabase();
        $this->_params['user'] = $dbConfig->getUser();
        $this->_params['password'] = $dbConfig->getPassword();
        $this->_params['port'] = $dbConfig->getDbPort();

        $this->connect();
    }

    public function connect(): bool
    {
        if ($this->isConnected()) {
            return true;
        }

        $this->_conn = $this->_driver->connect($this->_params);

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->isConnected = true;

        return true;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function close(): void
    {
        if ($this->isConnected()) {
            parent::close();
            $this->isConnected = false;
        }
    }

    public function getParams(): array
    {
        return $this->_params;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
