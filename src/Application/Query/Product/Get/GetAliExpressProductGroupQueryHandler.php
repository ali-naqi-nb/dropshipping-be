<?php

declare(strict_types=1);

namespace App\Application\Query\Product\Get;

use App\Application\Shared\Error\ErrorResponse;
use App\Application\Shared\Product\AeProductGroupResponse;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\GetAliExpressProductGroupValidatorInterface;

final class GetAliExpressProductGroupQueryHandler
{
    public function __construct(
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly GetAliExpressProductGroupValidatorInterface $validator
    ) {
    }

    public function __invoke(GetAliExpressProductGroupQuery $query): AeProductGroupResponse|ErrorResponse
    {
        $errors = $this->validator->validate(['id' => $query->getId()]);

        if ($errors->hasErrors()) {
            return ErrorResponse::fromConstraintViolationList($errors);
        }

        $aeProductImport = $this->productImportRepository->findOneById($query->getId());

        if (null === $aeProductImport) {
            return ErrorResponse::notFound('Product group not found');
        }

        return new AeProductGroupResponse(
            id: $aeProductImport->getId(),
            aeProductId: $aeProductImport->getAeProductId(),
            progressStep: $aeProductImport->getCompletedStep(),
            totalSteps: $aeProductImport->getTotalSteps(),
        );
    }
}
