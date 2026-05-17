<?php

declare(strict_types=1);

namespace App\Tests\Stub\Tarot;

use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameIdGenerator;

final readonly class StubGameIdGenerator implements GameIdGenerator
{
    public function __construct(
        private GameId $id,
    ) {}

    public function generate(): GameId
    {
        return $this->id;
    }
}
