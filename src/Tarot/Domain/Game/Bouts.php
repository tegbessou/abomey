<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum Bouts: int
{
    case Zero = 0;
    case One = 1;
    case Two = 2;
    case Three = 3;

    public function target(): int
    {
        return match ($this) {
            self::Zero => 56,
            self::One => 51,
            self::Two => 41,
            self::Three => 36,
        };
    }
}
