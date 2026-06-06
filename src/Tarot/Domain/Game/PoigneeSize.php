<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum PoigneeSize: string
{
    case Single = 'single';
    case Double = 'double';
    case Triple = 'triple';

    public function bonus(): int
    {
        return match ($this) {
            self::Single => 20,
            self::Double => 30,
            self::Triple => 40,
        };
    }
}
