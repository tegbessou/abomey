<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyPlayers;

final readonly class PlayerView
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}
}
