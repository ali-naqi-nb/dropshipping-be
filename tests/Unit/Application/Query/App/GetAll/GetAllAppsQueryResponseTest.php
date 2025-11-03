<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\App\GetAll;

use App\Application\Query\App\GetAll\GetAllAppsQueryResponse;
use App\Application\Shared\App\AppResponse;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Unit\UnitTestCase;

final class GetAllAppsQueryResponseTest extends UnitTestCase
{
    public function testGetItems(): void
    {
        $app = AppFactory::getApp();
        $response = GetAllAppsQueryResponse::fromApps([$app]);
        $appResponses = [AppResponse::fromApp($app)];
        $this->assertEquals($appResponses, $response->getItems());
    }
}
