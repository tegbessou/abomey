<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Player;

interface PlayerRepository
{
    public function create(Player $player): void;

    public function ofId(PlayerId $id): ?Player;
}
