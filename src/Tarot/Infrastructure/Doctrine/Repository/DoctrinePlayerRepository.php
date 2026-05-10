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

    public function ofId(PlayerId $id): ?Player
    {
        return $this->entityManager->find(Player::class, $id);
    }
}
