<?php

declare(strict_types=1);

namespace App\Tarot\Application\CorrectLastVachette;

use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Ranking;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class CorrectLastVachetteCommandHandler
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {}

    public function handle(CorrectLastVachetteCommand $command): void
    {
        $game = $this->gameRepository->ofId(
            GameId::fromString($command->gameId),
            $command->ownerId,
        );

        if (null === $game) {
            throw new GameNotFoundException();
        }

        $game->correctLastDealAsVachette(
            deadPlayerIds: $command->deadPlayerIds,
            ranking: new Ranking($command->ranking),
        );

        $this->gameRepository->update($game);
    }
}
