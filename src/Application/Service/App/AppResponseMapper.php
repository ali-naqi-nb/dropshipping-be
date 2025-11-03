<?php

declare(strict_types=1);

namespace App\Application\Service\App;

use App\Application\Shared\App\AppResponse;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;

final class AppResponseMapper
{
    public function __construct(private readonly string $aliExpressAppKey)
    {
    }

    public function getResponse(App $app): AppResponse
    {
        if (AppId::AliExpress === $app->getAppId()) {
            $app->appendConfig('clientId', (int) $this->aliExpressAppKey);
        }

        return AppResponse::fromApp($app);
    }

    public function getCollectionResponse(array $apps): array
    {
        $arr = [];

        foreach ($apps as $app) {
            $arr[] = $this->getResponse($app);
        }

        return ['items' => $arr];
    }
}
