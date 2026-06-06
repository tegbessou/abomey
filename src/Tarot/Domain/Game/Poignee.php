<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

final readonly class Poignee
{
    public function __construct(
        public string $announcerId,
        public PoigneeSize $size,
    ) {}
}
