<?php

declare(strict_types=1);

namespace App\Tarot\Application\Shared;

final readonly class ParticipantSummaryView
{
    public function __construct(
        public string $id,
        public string $name,
        public int $cumulativeScore,
    ) {}
}
