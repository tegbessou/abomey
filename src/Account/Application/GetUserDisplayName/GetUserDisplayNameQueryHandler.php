<?php

declare(strict_types=1);

namespace App\Account\Application\GetUserDisplayName;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus', method: 'handle')]
final readonly class GetUserDisplayNameQueryHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function handle(GetUserDisplayNameQuery $query): string
    {
        $user = $this->userRepository->ofId(UserId::fromString($query->userId));

        if (null === $user) {
            throw new \LogicException(sprintf('User "%s" not found.', $query->userId));
        }

        return $user->getName() ?? 'Anonyme';
    }
}
