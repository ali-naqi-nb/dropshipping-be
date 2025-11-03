<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Service\App;

use App\Application\Service\App\AppResponseMapper;
use App\Application\Shared\App\AppResponse;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use Doctrine\DBAL\Exception;

final class AppResponseMapperTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private TenantRepositoryInterface $tenantRepository;
    private AppResponseMapper $responseMapper;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var TenantRepositoryInterface $tenantRepository */
        $tenantRepository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->tenantRepository = $tenantRepository;

        /** @var AppResponseMapper $responseMapper */
        $responseMapper = self::getContainer()->get(AppResponseMapper::class);
        $this->responseMapper = $responseMapper;
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    public function testGetResponse(): void
    {
        $app = AppFactory::getApp();
        $response = $this->responseMapper->getResponse($app);

        $this->assertInstanceOf(AppResponse::class, $response);
    }

    public function testGetCollectionResponse(): void
    {
        /** @var string $tenantId */
        $tenantId = $this->connection->getTenantId();
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById($tenantId);

        $apps = [];
        foreach (AppId::cases() as $supportedAppId) {
            $app = $tenant->getApp($supportedAppId);
            if (null === $app) {
                $app = App::createWithDefaults($supportedAppId);
            }

            $apps[] = $app;
        }

        $response = $this->responseMapper->getCollectionResponse($apps);

        $this->assertIsArray($response);
        $this->assertInstanceOf(AppResponse::class, $response['items'][0]);
    }
}
