<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum Contract: string
{
    case Garde = 'garde';
    case GardeSans = 'garde_sans';
    case GardeContre = 'garde_contre';

    public function multiplier(): int
    {
        return match ($this) {
            self::Garde => 1,
            self::GardeSans => 2,
            self::GardeContre => 4,
        };
    }
}
