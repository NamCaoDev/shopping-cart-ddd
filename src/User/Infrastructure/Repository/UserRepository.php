<?php

namespace App\User\Infrastructure\Repository;

use App\User\Application\Port\UserRepositoryPort;
use App\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, UserRepositoryPort
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used by Symfony Security to allow login with EITHER username OR email.
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :query OR u.username = :query')
            ->andWhere('u.isActive = :active')
            ->setParameter('query', $identifier)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findById(string $id): ?User
    {
        return $this->find($id); // Doctrine's built-in find method
    }

    public function isEmailTaken(string $email): bool
    {
        return (bool) $this->count(['email' => $email]);
    }

    public function isUsernameTaken(string $username): bool
    {
        return (bool) $this->count(['username' => $username]);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
