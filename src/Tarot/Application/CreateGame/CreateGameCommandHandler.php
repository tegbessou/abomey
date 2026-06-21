<?php

declare(strict_types=1);

namespace App\Tarot\Application\CreateGame;

use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameIdGenerator;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\ParticipantNotOwnedException;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class CreateGameCommandHandler
{
    public function __construct(
        private GameRepository $gameRepository,
        private GameIdGenerator $gameIdGenerator,
        private PlayerRepository $playerRepository,
        private ClockInterface $clock,
    ) {}

    public function handle(CreateGameCommand $command): GameId
    {
        $uniqueIds = array_values(array_unique($command->participantIds));

        $playerIds = [];
        foreach ($uniqueIds as $uniqueId) {
            $playerIds[] = PlayerId::fromString($uniqueId);
        }

        $owned = $this->playerRepository->ofIds($playerIds, $command->ownerId);

        if (count($owned) !== count($uniqueIds)) {
            throw new ParticipantNotOwnedException();
        }

        $id = $this->gameIdGenerator->generate();
        $this->gameRepository->create(Game::create(
            $id,
            $command->ownerId,
            $command->name,
            Mode::from($command->mode),
            $command->participantIds,
            $this->clock->now(),
        ));

        return $id;
    }
}
