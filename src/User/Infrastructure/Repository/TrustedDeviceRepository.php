<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Repository;

use App\User\Application\Port\TrustedDeviceRepositoryPort;
use App\User\Domain\Entity\TrustedDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

/**
 * @extends ServiceEntityRepository<TrustedDevice>
 */
final class TrustedDeviceRepository extends ServiceEntityRepository implements TrustedDeviceRepositoryPort
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrustedDevice::class);
    }

    public function save(TrustedDevice $device): void
    {
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();
    }

    public function findByHash(string $tokenHash): ?TrustedDevice
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function deleteExpired(DateTimeImmutable $now): int
    {
        // Bulk delete query is highly efficient and avoids loading entities into memory
        return $this->getEntityManager()->createQueryBuilder()
            ->delete(TrustedDevice::class, 't')
            ->where('t.expiresAt <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }
}
