<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum PetitAuBout: string
{
    case None = 'none';
    case Taker = 'taker';
    case Defense = 'defense';

    public function bonus(int $contractMultiplier): int
    {
        return match ($this) {
            self::None => 0,
            self::Taker => 10 * $contractMultiplier,
            self::Defense => -10 * $contractMultiplier,
        };
    }
}
