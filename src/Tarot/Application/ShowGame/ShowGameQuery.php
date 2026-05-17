<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

final readonly class ShowGameQuery
{
    public function __construct(
        public string $ownerId,
        public string $gameId,
    ) {}
}
