<?php

declare(strict_types=1);

namespace App\Account\Application\AcceptPrivacyPolicy;

use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserNotFoundException;
use App\Account\Domain\User\UserRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class AcceptPrivacyPolicyCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ClockInterface $clock,
    ) {}

    public function handle(AcceptPrivacyPolicyCommand $command): void
    {
        $user = $this->userRepository->ofId(UserId::fromString($command->userId));

        if (null === $user) {
            throw new UserNotFoundException();
        }

        $user->acceptPrivacyPolicy(PrivacyPolicyVersion::from($command->version), $this->clock->now());
        $this->userRepository->update($user);
    }
}
