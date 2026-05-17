<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Messenger;

use App\Account\Domain\User\UserDeleted;
use App\Shared\Application\Bus\CommandBus;
use App\Tarot\Application\DeleteAllPlayersOfOwner\DeleteAllPlayersOfOwnerCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus', method: 'handle')]
final readonly class WhenUserIsDeletedDeleteHisPlayers
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    public function handle(UserDeleted $event): void
    {
        $this->commandBus->dispatch(new DeleteAllPlayersOfOwnerCommand(
            ownerId: $event->userId,
        ));
    }
}
