<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

use App\Tarot\Application\Shared\ParticipantSummaryView;

final readonly class GameView
{
    /**
     * @param list<ParticipantSummaryView> $participants the roster, in join order
     * @param list<ParticipantSummaryView> $standings    the same players, ranked by cumulative score (highest first)
     * @param list<DealView>               $deals
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $mode,
        public array $participants,
        public array $standings,
        public array $deals,
    ) {}
}
