<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Uid;

use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameIdGenerator;
use Symfony\Component\Uid\Uuid;

final readonly class SymfonyGameIdGenerator implements GameIdGenerator
{
    public function generate(): GameId
    {
        return GameId::fromString(Uuid::v4()->toRfc4122());
    }
}
