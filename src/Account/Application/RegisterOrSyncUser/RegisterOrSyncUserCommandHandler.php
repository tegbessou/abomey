<?php

declare(strict_types=1);

namespace App\Account\Application\RegisterOrSyncUser;

use App\Account\Domain\User\Email;
use App\Account\Domain\User\User;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserIdGenerator;
use App\Account\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class RegisterOrSyncUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserIdGenerator $userIdGenerator,
    ) {}

    public function handle(RegisterOrSyncUserCommand $command): UserId
    {
        $email = Email::fromString($command->email);

        $existing = $this->userRepository->ofExternalIdentifier($command->externalIdentifier);
        if (null !== $existing) {
            $existing->syncFromProvider($email, $command->name);
            $this->userRepository->update($existing);

            return $existing->getId();
        }

        $id = $this->userIdGenerator->generate();
        $this->userRepository->create(User::register($id, $command->externalIdentifier, $email, $command->name));

        return $id;
    }
}
