<?php

declare(strict_types=1);

namespace App\Account\Application\GetUserDisplayName;

final readonly class GetUserDisplayNameQuery
{
    public function __construct(
        public string $userId,
    ) {}
}
