<?php

declare(strict_types=1);

namespace App\Tarot\Application\RecordClassicDeal;

final readonly class RecordClassicDealCommand
{
    public function __construct(
        public string $ownerId,
        public string $gameId,
        public string $takerId,
        public string $contract,
        public int $bouts,
        public int $pointsScored,
    ) {}
}
