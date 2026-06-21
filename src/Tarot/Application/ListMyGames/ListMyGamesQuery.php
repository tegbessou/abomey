<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

final readonly class ListMyGamesQuery
{
    public function __construct(
        public string $ownerId,
    ) {}
}
