<?php

declare(strict_types=1);

namespace App\Account\Application\DeleteAccount;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserNotFoundException;
use App\Account\Domain\User\UserRepository;
use App\Shared\Application\Bus\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class DeleteAccountCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EventBus $eventBus,
    ) {}

    public function handle(DeleteAccountCommand $command): void
    {
        $user = $this->userRepository->ofId(UserId::fromString($command->userId));

        if (null === $user) {
            throw new UserNotFoundException();
        }

        $user->delete();
        $events = $user->pullDomainEvents();
        $this->userRepository->delete($user);

        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }
    }
}
