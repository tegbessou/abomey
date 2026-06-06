<?php

declare(strict_types=1);

namespace App\Tarot\Application\RecordClassicDeal;

use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\GameRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class RecordClassicDealCommandHandler
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {}

    public function handle(RecordClassicDealCommand $command): void
    {
        $game = $this->gameRepository->ofId(
            GameId::fromString($command->gameId),
            $command->ownerId,
        );

        if (null === $game) {
            throw new GameNotFoundException();
        }

        $game->recordClassicDeal(
            takerId: $command->takerId,
            contract: Contract::from($command->contract),
            bouts: Bouts::from($command->bouts),
            pointsScored: $command->pointsScored,
        );

        $this->gameRepository->update($game);
    }
}
