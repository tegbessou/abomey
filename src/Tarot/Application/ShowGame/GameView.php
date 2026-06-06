<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

use App\Tarot\Application\Shared\ParticipantSummaryView;

final readonly class GameView
{
    /**
     * @param list<ParticipantSummaryView> $participants
     * @param list<DealView>               $deals
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $mode,
        public array $participants,
        public array $deals,
    ) {}
}
