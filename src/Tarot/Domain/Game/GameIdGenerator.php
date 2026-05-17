<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

interface GameIdGenerator
{
    public function generate(): GameId;
}
