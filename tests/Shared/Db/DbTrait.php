<?php

declare(strict_types=1);

namespace App\Tests\Shared\Db;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

trait DbTrait
{
    protected function getDbConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        return $connection;
    }

    protected function getSchemaManager(): AbstractSchemaManager
    {
        return $this->getDbConnection()->createSchemaManager();
    }

    protected function doesDbUserExist(string $user): bool
    {
        $userInDb = $this->getDbConnection()
            ->prepare('SELECT User FROM mysql.user WHERE User = :user')
            ->executeQuery(['user' => $user])
            ->fetchFirstColumn();

        return !empty($userInDb);
    }

    protected function doesDbExist(string $database): bool
    {
        $databases = $this->getSchemaManager()->listDatabases();

        return in_array($database, $databases, true);
    }

    protected function deleteDbUser(string $user): void
    {
        $this->getDbConnection()
            ->prepare('DROP USER IF EXISTS :user@"%"')
            ->executeStatement(['user' => $user]);
    }

    protected function dropDb(string $database): void
    {
        $this->getSchemaManager()->dropDatabase($database);
    }
}
