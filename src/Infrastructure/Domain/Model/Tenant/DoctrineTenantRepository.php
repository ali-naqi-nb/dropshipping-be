<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Tenant;

use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

final class DoctrineTenantRepository implements TenantRepositoryInterface
{
    private readonly EntityManagerInterface $entityManager;
    private readonly ObjectRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Tenant::class);
    }

    public function findOneById(string $id): ?Tenant
    {
        /** @var ?Tenant $tenant */
        $tenant = $this->repository->findOneBy(['id' => $id]);

        return $tenant;
    }

    public function findOneByAliexpressSellerId(string|int $sellerId): ?Tenant
    {
        $appId = AppId::AliExpress->value;
        $path = '$."'.$appId.'".sellerId';

        /** @var ?Tenant $tenant */
        $tenant = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Tenant::class, 't')
            ->where("JSON_EXTRACT(t.apps, '$path') = :sellerId")
            ->setParameter('sellerId', $sellerId)
            ->getQuery()
            ->getOneOrNullResult();

        return $tenant;
    }

    /**
     * @return Tenant[]
     */
    public function findAll(int $chunk, int $chunkSize = self::CHUNK_SIZE): array
    {
        $offset = $chunk * $chunkSize;
        /** @var Tenant[] $tenants */
        $tenants = $this->repository->findBy([], ['id' => 'ASC'], $chunkSize, $offset);

        return $tenants;
    }

    /**
     * @return Tenant[]
     */
    public function findTenantsByStatus(int $chunk, array $status = [], int $chunkSize = self::CHUNK_SIZE): array
    {
        $offset = $chunk * $chunkSize;

        /** @var Tenant[] $tenants */
        $tenants = $this->repository->findBy([
            'status' => $status,
        ], ['id' => 'ASC'], $chunkSize, $offset);

        return $tenants;
    }

    public function findAllWithNullDbConfiguredAt(int $chunk, int $chunkSize = self::CHUNK_SIZE): array
    {
        $offset = $chunk * $chunkSize;
        /** @var Tenant[] $tenants */
        $tenants = $this->repository->findBy(['configuredAt' => null], [], $chunkSize, $offset);

        return $tenants;
    }

    public function save(Tenant $tenant): void
    {
        $this->entityManager->persist($tenant);
        $this->entityManager->flush();
    }

    public function remove(Tenant $tenant): void
    {
        $this->entityManager->remove($tenant);
        $this->entityManager->flush();
    }

    public function findTenantsWithAppInstalled(AppId $appId, int $chunk, int $chunkSize = self::CHUNK_SIZE): array
    {
        $offset = $chunk * $chunkSize;
        $appValue = $appId->value;
        $path = '$."' . $appValue . '"';

        /** @var Tenant[] $tenants */
        $tenants = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Tenant::class, 't')
            ->where("JSON_EXTRACT(t.apps, '$path') IS NOT NULL")
            ->andWhere('t.deletedAt IS NULL')
            ->andWhere('t.isAvailable = :available')
            ->setParameter('available', true)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults($chunkSize)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        // Filter to ensure app is actually installed (not just present)
        return array_filter($tenants, fn(Tenant $tenant) => $tenant->isAppInstalled($appId));
    }
}
