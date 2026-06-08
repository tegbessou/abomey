<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

final readonly class DealScoreLine
{
    public function __construct(
        public string $name,
        public int $points,
    ) {}
}
