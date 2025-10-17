<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

final class DoctrineAeProductImportRepository implements AeProductImportRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $tenantEntityManager)
    {
        $this->entityManager = $tenantEntityManager;
        $this->repository = $this->entityManager->getRepository(AeProductImport::class);
    }

    public function findNextId(?string $id = null): string
    {
        return $id ?? (string) Uuid::v4();
    }

    public function findOneById(string $id): ?AeProductImport
    {
        /** @var ?AeProductImport $import */
        $import = $this->repository->findOneBy(['id' => $id]);

        return $import;
    }

    public function findOneByAeProductId(int|string $aeProductId): ?AeProductImport
    {
        /** @var ?AeProductImport $import */
        $import = $this->repository->findOneBy(['aeProductId' => $aeProductId]);

        return $import;
    }

    public function findOneByAeProductIdAndAeSkuId(int|string $aeProductId, int|string $aeSkuId): ?AeProductImport
    {
        /** @var ?AeProductImport $import */
        $import = $this->repository->findOneBy(['aeProductId' => $aeProductId, 'aeSkuId' => $aeSkuId]);

        return $import;
    }

    public function save(AeProductImport $import): void
    {
        $this->entityManager->persist($import);
        $this->entityManager->flush();
    }

    public function delete(AeProductImport $import): void
    {
        $this->entityManager->remove($import);
        $this->entityManager->flush();
    }
}
