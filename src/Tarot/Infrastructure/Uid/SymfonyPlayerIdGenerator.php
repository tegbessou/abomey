<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Uid;

use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerIdGenerator;
use Symfony\Component\Uid\Uuid;

final readonly class SymfonyPlayerIdGenerator implements PlayerIdGenerator
{
    public function generate(): PlayerId
    {
        return PlayerId::fromString(Uuid::v4()->toRfc4122());
    }
}
