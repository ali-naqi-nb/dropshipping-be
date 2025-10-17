<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

final class DoctrineDsOrderMappingRepository implements DsOrderMappingRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $tenantEntityManager)
    {
        $this->entityManager = $tenantEntityManager;
        $this->repository = $this->entityManager->getRepository(DsOrderMapping::class);
    }

    public function findNextId(string $id = null): string
    {
        return $id ?? (string) Uuid::v4();
    }

    public function findOneById(string $id): ?DsOrderMapping
    {
        /** @var ?DsOrderMapping $dsOrderMapping */
        $dsOrderMapping = $this->repository->findOneBy([
            'id' => Uuid::fromString($id)->toBinary(),
        ]);

        return $dsOrderMapping;
    }

    public function findOneByDsOrderId(string|int $dsOrderId): ?DsOrderMapping
    {
        /** @var ?DsOrderMapping $dsOrderMapping */
        $dsOrderMapping = $this->repository->findOneBy(['dsOrderId' => (string) $dsOrderId]);

        return $dsOrderMapping;
    }

    /** {@inheritDoc} */
    public function findByNBOrderId(string $nbOrderId): array
    {
        /** @var DsOrderMapping[] $dsOrderMappings */
        $dsOrderMappings = $this->repository->findBy(['nbOrderId' => Uuid::fromString($nbOrderId)->toBinary()]);

        return $dsOrderMappings;
    }

    /**
     * @return DsOrderMapping[]
     */
    public function findOneByDsProvider(string $dsProvider): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->from(DsOrderMapping::class, 'o')
            ->orderBy('o.createdAt', 'DESC')
            ->andWhere('o.dsProvider = :dsProvider')
            ->setParameter('dsProvider', $dsProvider);

        return $qb->getQuery()->getResult();
    }

    public function save(DsOrderMapping $dsOrderMapping): void
    {
        $this->entityManager->persist($dsOrderMapping);
        $this->entityManager->flush();
    }

    public function delete(DsOrderMapping $dsOrderMapping): void
    {
        $this->entityManager->remove($dsOrderMapping);
        $this->entityManager->flush();
    }
}
