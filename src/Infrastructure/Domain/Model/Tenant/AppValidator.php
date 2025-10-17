<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Tenant;

use App\Application\Service\TranslatorInterface;
use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Domain\Model\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class AppValidator extends AbstractValidator implements AppValidatorInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TranslatorInterface $translator,
        SymfonyValidatorInterface $validator,
    ) {
        parent::__construct($validator);
    }

    public function validateAppId(string $appId): ?ConstraintViolation
    {
        if (!in_array($appId, array_column(AppId::cases(), 'value'), true)) {
            return new ConstraintViolation(
                $this->translator->trans('App "{appId}" is not supported.', ['{appId}' => $appId]),
                'appId',
            );
        }

        return null;
    }

    public function validateAppInstalledAndActive(string $tenantId, string $appId): ?ConstraintViolation
    {
        $tenant = $this->tenantRepository->findOneById($tenantId);
        $appIdEnum = AppId::tryFrom($appId);
        if ($tenant && null !== $appIdEnum && $tenant->isAppInstalled($appIdEnum) && $tenant->getApp($appIdEnum)?->isActive()) {
            return null;
        }

        return new ConstraintViolation(
            $this->translator->trans('App "{appId}" is either not installed or not active', ['{appId}' => $appId]),
            'appId',
        );
    }

    public function validateExchangeTokenAppId(string $appId): ?ConstraintViolation
    {
        $validateAppId = $this->validateAppId($appId);

        if (null !== $validateAppId) {
            return $validateAppId;
        }

        if (!in_array($appId, array_column(AppId::exchangeTokenAppIds(), 'value'), true)) {
            return new ConstraintViolation(
                $this->translator->trans('App "{appId}" is not supported for exchange-token.', ['{appId}' => $appId]),
                'appId',
            );
        }

        return null;
    }

    /** {@inheritDoc} */
    public function getFields(?string $group = null): array
    {
        return [
            'config' => [
                new Assert\Sequentially([
                    new Assert\NotBlank(),
                    new Assert\Collection(
                        fields: [
                            'isActive' => [new Assert\Type('boolean')],
                        ],
                        allowExtraFields: true,
                        allowMissingFields: false,
                    ),
                ]),
            ],
        ];
    }
}
