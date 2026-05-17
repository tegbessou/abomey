<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

final readonly class UserDeleted
{
    public function __construct(
        public string $userId,
    ) {}
}
