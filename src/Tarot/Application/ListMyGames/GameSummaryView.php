<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

final readonly class GameSummaryView
{
    /**
     * @param list<string> $participantNames
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $mode,
        public array $participantNames,
    ) {}
}
