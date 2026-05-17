<?php

declare(strict_types=1);

namespace App\Tests\Fake\Tarot;

use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;

final class InMemoryGameRepository implements GameRepository
{
    /** @var array<string, Game> */
    private array $games = [];

    public function create(Game $game): void
    {
        $this->games[$game->getId()->toString()] = $game;
    }

    public function ofId(GameId $id, string $owner): ?Game
    {
        $game = $this->games[$id->toString()] ?? null;

        if (null === $game || $game->getOwner() !== $owner) {
            return null;
        }

        return $game;
    }
}
