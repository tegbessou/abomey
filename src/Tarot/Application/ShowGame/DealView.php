<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

final readonly class DealView
{
    /**
     * @param array<string, int>  $pointsByPlayerId
     * @param list<DealScoreLine> $scores
     */
    public function __construct(
        public int $position,
        public array $pointsByPlayerId,
        public array $scores,
    ) {}
}
