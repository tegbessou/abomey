<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

final readonly class Ranking
{
    /**
     * @param list<string> $orderedPlayerIds
     */
    public function __construct(
        private array $orderedPlayerIds,
    ) {
        if (count($orderedPlayerIds) !== count(array_unique($orderedPlayerIds))) {
            throw new InvalidRankingException();
        }
    }

    public function positionOf(string $playerId): int
    {
        $index = array_search($playerId, $this->orderedPlayerIds, true);

        return $index + 1;
    }

    /**
     * @return list<string>
     */
    public function players(): array
    {
        return $this->orderedPlayerIds;
    }
}
