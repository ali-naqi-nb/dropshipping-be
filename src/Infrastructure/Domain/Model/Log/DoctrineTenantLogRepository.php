<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Log;

use App\Domain\Model\Log\TenantLog;
use App\Domain\Model\Log\TenantLogRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineTenantLogRepository implements TenantLogRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $tenantEntityManager)
    {
        $this->entityManager = $tenantEntityManager;
        $this->repository = $this->entityManager->getRepository(TenantLog::class);
    }

    public function save(TenantLog $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function findOneById(int $id): ?TenantLog
    {
        /** @var ?TenantLog $log */
        $log = $this->repository->findOneBy(['id' => $id]);

        return $log;
    }

    /**
     * {@inheritDoc}
     */
    public function findByLevel(string $level, ?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(TenantLog::class, 'l')
            ->where('l.level = :level')
            ->setParameter('level', $level)
            ->orderBy('l.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByUserId(string $userId, ?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(TenantLog::class, 'l')
            ->where('l.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByRequestId(string $requestId): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(TenantLog::class, 'l')
            ->where('l.requestId = :requestId')
            ->setParameter('requestId', $requestId)
            ->orderBy('l.createdAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByChannel(string $channel, ?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(TenantLog::class, 'l')
            ->where('l.channel = :channel')
            ->setParameter('channel', $channel)
            ->orderBy('l.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function deleteOlderThan(DateTime $date): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->delete(TenantLog::class, 'l')
            ->where('l.createdAt < :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->execute();
    }
}
