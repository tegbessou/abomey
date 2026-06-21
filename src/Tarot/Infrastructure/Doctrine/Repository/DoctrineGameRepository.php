<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineGameRepository implements GameRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function create(Game $game): void
    {
        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }

    public function update(Game $game): void
    {
        $this->entityManager->flush();
    }

    public function ofId(GameId $id, string $owner): ?Game
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(Game::class, 'g')
            ->where('g.id = :id')
            ->andWhere('g.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result instanceof Game) {
            return null;
        }

        return $result;
    }

    public function ofOwner(string $owner): array
    {
        /** @var list<Game> $results */
        $results = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(Game::class, 'g')
            ->where('g.owner = :owner')
            ->orderBy('g.createdAt', 'DESC')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
