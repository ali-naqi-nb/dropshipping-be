<?php

declare(strict_types=1);

namespace App\Application\Command\App\Install;

use App\Application\Service\TranslatorInterface;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\App\CreateDbAppInstalled;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class InstallAppCommandHandler
{
    public function __construct(
        private readonly AppValidatorInterface $validator,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TranslatorInterface $translator,
        private readonly EventBusInterface $eventBus,
        private readonly string $appServiceName
    ) {
    }

    public function __invoke(InstallAppCommand $command): ?ErrorResponse
    {
        $appError = $this->validator->validateAppId($command->getAppId());
        if (null !== $appError) {
            return ErrorResponse::fromConstraintViolation($appError);
        }

        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound($this->translator->trans('Tenant not found.'));
        }

        $tenant->installApp(AppId::from($command->getAppId()));
        $this->tenantRepository->save($tenant);

        /** @var array $apps */
        $apps = $tenant->getApps();

        if (1 === count($apps) && null === $tenant->getConfiguredAt()) {
            $this->eventBus->publish(new CreateDbAppInstalled(
                tenantId: $tenant->getId(),
                serviceName: $this->appServiceName,
                appId: $command->getAppId()
            ));
        }

        return null;
    }
}
