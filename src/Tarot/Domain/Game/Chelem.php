<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum Chelem: string
{
    case None = 'none';
    case Realised = 'realised';
    case AnnouncedRealised = 'announced_realised';
    case AnnouncedFailed = 'announced_failed';

    public function bonus(): int
    {
        return match ($this) {
            self::None => 0,
            self::Realised => 200,
            self::AnnouncedRealised => 400,
            self::AnnouncedFailed => -200,
        };
    }
}
