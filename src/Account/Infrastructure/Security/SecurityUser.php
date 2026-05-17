<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Security;

use App\Account\Domain\User\UserId;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SecurityUser implements UserInterface
{
    public function __construct(
        private UserId $userId,
    ) {}

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        $value = $this->userId->toString();

        if ('' === $value) {
            throw new \LogicException('Expected non-empty user identifier.');
        }

        return $value;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
}
