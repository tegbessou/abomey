<?php

declare(strict_types=1);

namespace App\Tarot\Application\CreatePlayer;

final readonly class CreatePlayerCommand
{
    public function __construct(
        public string $ownerId,
        public string $name,
    ) {}
}
