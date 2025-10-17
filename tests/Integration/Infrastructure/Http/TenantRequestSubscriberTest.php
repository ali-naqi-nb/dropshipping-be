<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Http\TenantRequestSubscriber;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\SessionUserFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final class TenantRequestSubscriberTest extends IntegrationTestCase
{
    private TenantRequestSubscriber $tenantRequestSubscriber;
    private RequestEvent $requestEvent;
    private TokenStorageInterface $tokenStorage;
    private TenantStorageInterface $tenantStorage;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TenantRequestSubscriber $tenantRequestSubscriber */
        $tenantRequestSubscriber = self::getContainer()->get(TenantRequestSubscriber::class);
        $this->tenantRequestSubscriber = $tenantRequestSubscriber;

        /** @var HttpKernelInterface $httpKernel */
        $httpKernel = self::getContainer()->get(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($httpKernel, new Request(), HttpKernelInterface::MAIN_REQUEST);
        $this->requestEvent = $requestEvent;

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = self::getContainer()->get('security.token_storage');
        $this->tokenStorage = $tokenStorage;

        /** @var TenantStorageInterface $tenantStorage */
        $tenantStorage = self::getContainer()->get(TenantStorageInterface::class);
        $this->tenantStorage = $tenantStorage;
    }

    public function testGetSubscribedEvents(): void
    {
        $expectedEvents = [
            RequestEvent::class => [['storeTenantId', 10], ['createRedisTenantConnection', 9], ['createDoctrineTenantConnection']],
            ResponseEvent::class => 'onKernelResponse',
        ];

        $this->assertEquals($expectedEvents, TenantRequestSubscriber::getSubscribedEvents());
    }

    public function testStoreTenantIdEmptyTenantIdFail(): void
    {
        $this->tenantStorage->setId('');
        $this->tenantRequestSubscriber->storeTenantId($this->requestEvent);

        $this->assertNotNull($this->requestEvent->getResponse());
        /** @var Response $response */
        $response = $this->requestEvent->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $expectedResponse['errors'] = ['tenantId' => 'Tenant id is missing'];
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse($response));
    }

    public function testCreateDoctrineTenantConnectionServiceIsNotAvailableFail(): void
    {
        $this->tenantStorage->setId(TenantFactory::NON_EXISTING_TENANT_ID);
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNotNull($this->requestEvent->getResponse());
        /** @var Response $response */
        $response = $this->requestEvent->getResponse();
        $this->assertSame(503, $response->getStatusCode());
        $expectedResponse['message'] = 'Service is unavailable.';
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse($response));
    }

    public function testCreateDoctrineTenantConnectionNoUserToAdminRouteFail(): void
    {
        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'admin_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNotNull($this->requestEvent->getResponse());
        /** @var Response $response */
        $response = $this->requestEvent->getResponse();
        $this->assertSame(403, $response->getStatusCode());
        $expectedResponse['message'] = 'Has no access to this tenant.';
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse($response));
    }

    public function testStoreTenantIdWithDebugRoute(): void
    {
        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', '_profiler');
        $this->tenantRequestSubscriber->storeTenantId($this->requestEvent);

        /** @var DoctrineTenantConnection $tenantConnection */
        $tenantConnection = self::getContainer()->get('doctrine.dbal.tenant_connection');
        $this->assertFalse($tenantConnection->isTransactionActive());
        $this->assertNotSame(TenantFactory::TENANT_ID, $tenantConnection->getTenantId());
    }

    public function testCreateDoctrineTenantConnectionHasAccessNoUserToPublicRouteSuccess(): void
    {
        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'public_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNull($this->requestEvent->getResponse());
    }

    public function testCreateDoctrineTenantConnectionHasAccessSuperAdminToAdminRouteSuccess(): void
    {
        $this->setSuperAdminToken();
        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'admin_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNull($this->requestEvent->getResponse());
    }

    public function testCreateDoctrineTenantConnectionHasAccessNoSuperAdminToAdminRouteSuccess(): void
    {
        $this->setNotSuperAdminToken();
        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'admin_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNull($this->requestEvent->getResponse());
    }

    public function testCreateDoctrineTenantConnectionHasNoAccessNoSuperAdminToAdminRouteSuccess(): void
    {
        $this->setNotSuperAdminToken();
        $this->tenantStorage->setId(TenantFactory::SECOND_TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'admin_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertNotNull($this->requestEvent->getResponse());
        /** @var Response $response */
        $response = $this->requestEvent->getResponse();
        $this->assertSame(403, $response->getStatusCode());
        $expectedResponse['message'] = 'Has no access to this tenant.';
        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse($response));
    }

    public function testOnKernelResponseRollbacksTransaction(): void
    {
        /** @var DoctrineTenantConnection $tenantConnection */
        $tenantConnection = self::getContainer()->get('doctrine.dbal.tenant_connection');
        $this->assertFalse($tenantConnection->isTransactionActive());

        $this->tenantStorage->setId(TenantFactory::TENANT_ID);
        $this->requestEvent->getRequest()->attributes->set('_route', 'public_route');
        $this->tenantRequestSubscriber->createDoctrineTenantConnection($this->requestEvent);

        $this->assertTrue($tenantConnection->isTransactionActive());

        /** @var HttpKernelInterface $kernel */
        $kernel = self::getContainer()->get(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $this->tenantRequestSubscriber->onKernelResponse($event);

        $this->assertFalse($tenantConnection->isTransactionActive());
    }

    private function setSuperAdminToken(): void
    {
        $this->tokenStorage->setToken(
            new PostAuthenticationToken(SessionUserFactory::getSessionUser(), 'main', SessionUserFactory::PERMISSIONS)
        );
    }

    private function setNotSuperAdminToken(): void
    {
        $this->tokenStorage->setToken(
            new PostAuthenticationToken(
                SessionUserFactory::getSessionUser(isSuperAdmin: false),
                'main',
                SessionUserFactory::PERMISSIONS
            )
        );
    }

    private function getDecodedJsonResponse(Response $response): array
    {
        return json_decode((string) $response->getContent(), true);
    }
}
