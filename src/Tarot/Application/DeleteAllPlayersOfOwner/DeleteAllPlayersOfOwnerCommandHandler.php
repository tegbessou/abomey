<?php

declare(strict_types=1);

namespace App\Tarot\Application\DeleteAllPlayersOfOwner;

use App\Tarot\Domain\Player\PlayerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class DeleteAllPlayersOfOwnerCommandHandler
{
    public function __construct(
        private PlayerRepository $playerRepository,
    ) {}

    public function handle(DeleteAllPlayersOfOwnerCommand $command): void
    {
        $this->playerRepository->deleteAllOf($command->ownerId);
    }
}
