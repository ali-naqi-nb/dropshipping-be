<?php

declare(strict_types=1);

namespace App\Application\Command\Product\AliExpressProductImport;

use App\Application\Service\ProductServiceInterface;
use App\Application\Shared\Error\ErrorResponse;
use App\Application\Shared\Product\AeProductGroupResponse;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\CreateAliexpressProductGroupValidatorInterface;

final class CreateAliExpressProductGroupCommandHandler
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly CreateAliexpressProductGroupValidatorInterface $validator
    ) {
    }

    public function __invoke(CreateAliExpressProductGroupCommand $command): AeProductGroupResponse|ErrorResponse
    {
        $errors = $this->validator->validate($command->toArray());

        if ($errors->hasErrors()) {
            return ErrorResponse::fromConstraintViolationList($errors);
        }

        $product = $command->getProducts()[0];
        $aeProductImport = $this->productImportRepository->findOneByAeProductId($product['aeProductId']);

        if (null !== $aeProductImport) {
            return new AeProductGroupResponse(
                id: $aeProductImport->getId(),
                aeProductId: $product['aeProductId'],
                progressStep: $aeProductImport->getCompletedStep(),
                totalSteps: $aeProductImport->getTotalSteps(),
            );
        }

        $aeProductImport = new AeProductImport(
            groupData: $command->toArray(),
            id: $this->productImportRepository->findNextId(),
            aeProductId: $product['aeProductId']
        );

        $this->productImportRepository->save($aeProductImport);

        $this->productService->sendDsProductTypeImport(
            productTypeName: $product['productTypeName'],
            dsProductId: (string) $product['aeProductId']
        );

        return new AeProductGroupResponse(
            id: $aeProductImport->getId(),
            aeProductId: $product['aeProductId'],
            progressStep: $aeProductImport->getCompletedStep(),
            totalSteps: $aeProductImport->getTotalSteps(),
        );
    }
}
