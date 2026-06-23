<?php

declare(strict_types=1);

namespace App\Tarot\Application\GetLastDeal;

final readonly class GetLastDealQuery
{
    public function __construct(
        public string $ownerId,
        public string $gameId,
    ) {}
}
