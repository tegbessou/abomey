<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Doctrine\Repository;

use App\Account\Domain\User\User;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function create(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function update(User $user): void
    {
        $this->entityManager->flush();
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function ofId(UserId $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function ofExternalIdentifier(string $externalIdentifier): ?User
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.externalIdentifier = :externalIdentifier')
            ->setParameter('externalIdentifier', $externalIdentifier)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result instanceof User) {
            return null;
        }

        return $result;
    }
}
