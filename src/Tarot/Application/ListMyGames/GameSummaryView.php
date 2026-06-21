<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

use App\Tarot\Application\Shared\ParticipantSummaryView;

final readonly class GameSummaryView
{
    /**
     * @param list<ParticipantSummaryView> $participants the roster, in join order
     * @param list<ParticipantSummaryView> $standings    the same players, ranked by cumulative score (highest first)
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $mode,
        public int $dealCount,
        public array $participants,
        public array $standings,
    ) {}
}
