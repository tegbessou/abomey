<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePlayerRepository implements PlayerRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function create(Player $player): void
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();
    }

    public function ofId(PlayerId $id, string $owner): ?Player
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Player::class, 'p')
            ->where('p.id = :id')
            ->andWhere('p.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result instanceof Player) {
            return null;
        }

        return $result;
    }

    public function ofIds(array $ids, string $owner): array
    {
        if ([] === $ids) {
            return [];
        }

        /** @var list<Player> $players */
        $players = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Player::class, 'p')
            ->where('p.id IN (:ids)')
            ->andWhere('p.owner = :owner')
            ->setParameter('ids', $ids)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getResult();

        return $players;
    }

    public function allOf(string $owner): array
    {
        /** @var list<Player> $players */
        $players = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Player::class, 'p')
            ->where('p.owner = :owner')
            ->orderBy('p.name', 'ASC')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getResult();

        return $players;
    }

    public function deleteAllOf(string $owner): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Player::class, 'p')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->execute();
    }
}
