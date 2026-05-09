<?php

declare(strict_types=1);

namespace App\Tests\Fake\Tarot;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerRepository;

final class InMemoryPlayerRepository implements PlayerRepository
{
    /** @var array<string, Player> */
    private array $players = [];

    public function create(Player $player): void
    {
        $this->players[$player->getId()->toString()] = $player;
    }

    public function ofId(PlayerId $id): ?Player
    {
        return $this->players[$id->toString()] ?? null;
    }
}
