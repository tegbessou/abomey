<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

interface GameRepository
{
    public function create(Game $game): void;

    public function ofId(GameId $id, string $owner): ?Game;
}
