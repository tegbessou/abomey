<?php

declare(strict_types=1);

namespace App\Tarot\Application\GetLastDeal;

final readonly class VachetteLastDealView
{
    /**
     * @param list<string> $deadPlayerIds
     * @param list<string> $ranking
     */
    public function __construct(
        public array $deadPlayerIds,
        public array $ranking,
    ) {}
}
