<?php

declare(strict_types=1);

namespace App\Tests\Fake\Account;

use App\Account\Domain\User\User;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function create(User $user): void
    {
        $this->users[$user->getId()->toString()] = $user;
    }

    public function update(User $user): void
    {
        $this->users[$user->getId()->toString()] = $user;
    }

    public function delete(User $user): void
    {
        unset($this->users[$user->getId()->toString()]);
    }

    public function ofId(UserId $id): ?User
    {
        return $this->users[$id->toString()] ?? null;
    }

    public function ofExternalIdentifier(string $externalIdentifier): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getExternalIdentifier() === $externalIdentifier) {
                return $user;
            }
        }

        return null;
    }
}
