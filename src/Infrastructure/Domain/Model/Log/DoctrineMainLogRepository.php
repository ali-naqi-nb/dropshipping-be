<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Log;

use App\Domain\Model\Log\MainLog;
use App\Domain\Model\Log\MainLogRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineMainLogRepository implements MainLogRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $mainEntityManager)
    {
        $this->entityManager = $mainEntityManager;
        $this->repository = $this->entityManager->getRepository(MainLog::class);
    }

    public function save(MainLog $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function findOneById(int $id): ?MainLog
    {
        /** @var ?MainLog $log */
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
            ->from(MainLog::class, 'l')
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
    public function findByTenantId(string $tenantId, ?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(MainLog::class, 'l')
            ->where('l.tenantId = :tenantId')
            ->setParameter('tenantId', $tenantId)
            ->orderBy('l.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByChannel(string $channel, ?int $limit = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(MainLog::class, 'l')
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
            ->delete(MainLog::class, 'l')
            ->where('l.createdAt < :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->execute();
    }
}
