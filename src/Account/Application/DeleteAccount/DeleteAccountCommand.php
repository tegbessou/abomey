<?php

declare(strict_types=1);

namespace App\Account\Application\DeleteAccount;

final readonly class DeleteAccountCommand
{
    public function __construct(
        public string $userId,
    ) {}
}
