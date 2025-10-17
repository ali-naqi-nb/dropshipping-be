<?php

declare(strict_types=1);

namespace App\Tests\Shared\Trait;

use App\Tests\Shared\Factory\AuthTokenFactory;
use App\Tests\Shared\Factory\SessionUserFactory;

trait UsersHeadersTrait
{
    protected function setUserHeaders(): void
    {
        $this->client->setServerParameter('HTTP_authorization', AuthTokenFactory::TOKEN);
        $this->client->setServerParameter('HTTP_x-consumer-custom-id', SessionUserFactory::USER_ID);
    }
}
