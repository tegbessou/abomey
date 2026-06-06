<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

final readonly class Misere
{
    public function __construct(
        public string $announcerId,
        public MisereType $type,
    ) {}
}
