<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Domain\Model\Product\AeProductImportProductAttributeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineAeProductImportProductAttributeRepository implements AeProductImportProductAttributeRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $tenantEntityManager)
    {
        $this->entityManager = $tenantEntityManager;
        $this->repository = $this->entityManager->getRepository(AeProductImportProductAttribute::class);
    }

    public function findByAeProductId(string $aeProductId): ?array
    {
        /** @var ?AeProductImportProductAttribute[] $aeProductImportProductAttributes */
        $aeProductImportProductAttributes = $this->repository->findBy([
            'aeProductId' => $aeProductId,
        ]);

        return $aeProductImportProductAttributes;
    }
}
