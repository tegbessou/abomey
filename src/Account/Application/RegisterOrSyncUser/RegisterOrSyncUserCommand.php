<?php

declare(strict_types=1);

namespace App\Account\Application\RegisterOrSyncUser;

final readonly class RegisterOrSyncUserCommand
{
    public function __construct(
        public string $externalIdentifier,
        public string $email,
        public ?string $name,
    ) {}
}
