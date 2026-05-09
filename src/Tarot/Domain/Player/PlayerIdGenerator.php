<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Player;

interface PlayerIdGenerator
{
    public function generate(): PlayerId;
}
