<?php

declare(strict_types=1);

namespace App\Tarot\Application\CreatePlayer;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerIdGenerator;
use App\Tarot\Domain\Player\PlayerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class CreatePlayerCommandHandler
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private PlayerIdGenerator $playerIdGenerator,
    ) {}

    public function handle(CreatePlayerCommand $command): PlayerId
    {
        $id = $this->playerIdGenerator->generate();

        $this->playerRepository->create(Player::create($id, $command->ownerId, $command->name));

        return $id;
    }
}
