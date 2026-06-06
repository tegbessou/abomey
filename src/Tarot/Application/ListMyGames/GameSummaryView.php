<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

use App\Tarot\Application\Shared\ParticipantSummaryView;

final readonly class GameSummaryView
{
    /**
     * @param list<ParticipantSummaryView> $participants
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $mode,
        public int $dealCount,
        public array $participants,
    ) {}
}
