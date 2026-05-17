<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

interface UserRepository
{
    public function create(User $user): void;

    public function update(User $user): void;

    public function delete(User $user): void;

    public function ofId(UserId $id): ?User;

    public function ofExternalIdentifier(string $externalIdentifier): ?User;
}
