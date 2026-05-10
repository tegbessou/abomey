<?php

declare(strict_types=1);

namespace App\Tests\Stub\Tarot;

use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerIdGenerator;

final readonly class StubPlayerIdGenerator implements PlayerIdGenerator
{
    public function __construct(
        private PlayerId $id,
    ) {}

    public function generate(): PlayerId
    {
        return $this->id;
    }
}
