<?php

declare(strict_types=1);

namespace App\Tarot\Application\RecordClassicDeal;

final readonly class RecordClassicDealCommand
{
    /**
     * @param list<string>                                   $deadPlayerIds
     * @param list<array{announcerId: string, size: string}> $poignees
     * @param list<array{announcerId: string, type: string}> $miseres
     */
    public function __construct(
        public string $ownerId,
        public string $gameId,
        public array $deadPlayerIds,
        public ?string $partnerId,
        public string $takerId,
        public string $contract,
        public int $bouts,
        public int $pointsScored,
        public string $petitAuBout,
        public string $chelem,
        public array $poignees,
        public array $miseres,
    ) {}
}
