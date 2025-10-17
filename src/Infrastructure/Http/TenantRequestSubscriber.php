<?php

namespace App\Infrastructure\Http;

use App\Domain\Model\Session\SessionUserInterface;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TenantRequestSubscriber implements EventSubscriberInterface
{
    private bool $hasActiveTransaction = false;

    public function __construct(
        private readonly DoctrineTenantConnection $doctrineTenantConnection,
        private readonly RedisTenantConnection $redisTenantConnection,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly TenantServiceInterface $tenantService,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly array $nonTenantRoutes,
        private readonly bool $wrapInTransaction, // During tests, we want to reset db after each tests,
        private readonly string $adminRoutesNamePrefix,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            /*
             * Important! - storeTenantId() should be executed before TokenAuthenticator::authenticate(),
             * which is subscribed in FirewallListener::onKernelRequest() with priority = 8
             */
            RequestEvent::class => [['storeTenantId', 10], ['createRedisTenantConnection', 9], ['createDoctrineTenantConnection']],
            ResponseEvent::class => 'onKernelResponse',
        ];
    }

    /**
     * In case of a valid tenant request the tenant id is stored in TenantStorage.
     */
    public function storeTenantId(RequestEvent $event): void
    {
        if (!$this->isTenantRequest($event)) {
            return;
        }

        $tenantId = (string) $event->getRequest()->headers->get('x-tenant-id');
        $errorResponse = $this->validateTenantId($tenantId);
        if (null !== $errorResponse) {
            $event->setResponse($errorResponse);

            return;
        }

        $this->tenantStorage->setId($tenantId);
    }

    public function createRedisTenantConnection(RequestEvent $event): void
    {
        $tenantId = $this->tenantStorage->getId();
        if (null === $tenantId) {
            return;
        }

        $this->redisTenantConnection->connect();
    }

    public function createDoctrineTenantConnection(RequestEvent $event): void
    {
        $tenantId = $this->tenantStorage->getId();
        if (null === $tenantId) {
            return;
        }

        if (!$this->tenantService->isAvailable($tenantId)) {
            $event->setResponse(
                new JsonResponse(['message' => 'Service is unavailable.'], Response::HTTP_SERVICE_UNAVAILABLE)
            );

            return;
        }

        $routeName = (string) $event->getRequest()->attributes->get('_route');
        $errorResponse = $this->validateUserPermissions($tenantId, $routeName);
        if (null !== $errorResponse) {
            $event->setResponse($errorResponse);

            return;
        }

        $dbConfig = $this->tenantService->getDbConfig($tenantId);
        // TODO: I think it's more correct to return an error in case of null db config (tenant is not found in our db)
        if (null !== $dbConfig) {
            $this->doctrineTenantConnection->create($dbConfig);
            if ($this->wrapInTransaction) {
                $this->doctrineTenantConnection->beginTransaction();
                $this->hasActiveTransaction = true;
            }
        }

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById($tenantId);
        $event->getRequest()->attributes->set('tenant', $tenant);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->hasActiveTransaction) {
            $this->doctrineTenantConnection->rollBack();
        }
    }

    private function isTenantRequest(RequestEvent $event): bool
    {
        $routeName = (string) $event->getRequest()->attributes->get('_route');
        $isDebug = str_starts_with($routeName, '_');
        $isTenantRoute = !in_array($routeName, $this->nonTenantRoutes, true);

        return !$isDebug && $event->isMainRequest() && $isTenantRoute;
    }

    private function validateTenantId(string $tenantId): ?JsonResponse
    {
        if ('' === $tenantId) {
            return new JsonResponse(
                ['errors' => ['tenantId' => $this->translator->trans('Tenant id is missing')]],
                Response::HTTP_BAD_REQUEST
            );
        }

        // TODO: add validation if it's a valid uuid to prevent further processing of the request

        return null;
    }

    private function validateUserPermissions(string $tenantId, string $routeName): ?JsonResponse
    {
        // If route is not admin no permission check is needed
        if (!str_starts_with($routeName, $this->adminRoutesNamePrefix)) {
            return null;
        }

        $companyId = $this->tenantService->getCompanyId($tenantId);
        /** @var SessionUserInterface|null $sessionUser */
        $sessionUser = $this->security->getUser();

        if (null === $sessionUser || (!$sessionUser->isSuperAdmin() && !$sessionUser->hasAccessToCompany($companyId))) {
            return new JsonResponse(['message' => 'Has no access to this tenant.'], Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
