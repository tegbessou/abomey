<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyPlayers;

final readonly class ListMyPlayersQuery
{
    public function __construct(
        public string $ownerId,
    ) {}
}
