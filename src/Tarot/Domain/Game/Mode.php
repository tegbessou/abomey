<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum Mode: int
{
    case Three = 3;
    case Four = 4;
    case Five = 5;
}
