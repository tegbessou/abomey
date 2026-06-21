<?php

declare(strict_types=1);

namespace App\Tarot\Application\RecordVachette;

final readonly class RecordVachetteCommand
{
    /**
     * @param list<string> $deadPlayerIds
     * @param list<string> $ranking
     */
    public function __construct(
        public string $ownerId,
        public string $gameId,
        public array $deadPlayerIds,
        public array $ranking,
    ) {}
}
