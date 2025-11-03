<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

final class DoctrineAeProductImportProductRepository implements AeProductImportProductRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $tenantEntityManager)
    {
        $this->entityManager = $tenantEntityManager;
        $this->repository = $this->entityManager->getRepository(AeProductImportProduct::class);
    }

    public function findOneByAeProductIdAndAeSkuId(int|string $aeProductId, int|string $aeSkuId): ?AeProductImportProduct
    {
        /** @var ?AeProductImportProduct $importProduct */
        $importProduct = $this->repository->findOneBy([
            'aeProductId' => $aeProductId,
            'aeSkuId' => $aeSkuId,
        ]);

        return $importProduct;
    }

    public function findOneByNbProductId(string $nbProductId): ?AeProductImportProduct
    {
        /** @var ?AeProductImportProduct $importProduct */
        $importProduct = $this->repository->findOneBy([
            'nbProductId' => Uuid::fromString($nbProductId)->toBinary(),
        ]);

        return $importProduct;
    }

    public function findOneBy(array $criteria): ?AeProductImportProduct
    {
        /** @var ?AeProductImportProduct $importProduct */
        $importProduct = $this->repository->findOneBy($criteria);

        return $importProduct;
    }

    public function findByAeProductId(string $aeProductId): array
    {
        /** @var AeProductImportProduct[] $aeProductImportProducts */
        $aeProductImportProducts = $this->repository->findBy(['aeProductId' => $aeProductId]);

        return $aeProductImportProducts;
    }

    public function save(AeProductImportProduct $importProduct): void
    {
        $this->entityManager->persist($importProduct);
        $this->entityManager->flush();
    }

    public function delete(AeProductImportProduct $importProduct): void
    {
        $this->entityManager->remove($importProduct);
        $this->entityManager->flush();
    }

    public function findAllDistinctAeProductIds(?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT p.aeProductId')
            ->from(AeProductImportProduct::class, 'p')
            ->where('p.nbProductId IS NOT NULL')
            ->orderBy('p.aeProductId', 'ASC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $result = $qb->getQuery()->getScalarResult();

        return array_map(fn(array $row) => (int)$row['aeProductId'], $result);
    }

    public function findAllByAeProductId(int|string $aeProductId): array
    {
        /** @var AeProductImportProduct[] $products */
        $products = $this->repository->findBy(['aeProductId' => $aeProductId]);

        return $products;
    }
}
