<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

enum MisereType: string
{
    case Atouts = 'atouts';
    case Tete = 'tete';
}
