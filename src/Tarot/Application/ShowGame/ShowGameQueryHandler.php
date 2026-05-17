<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus', method: 'handle')]
final readonly class ShowGameQueryHandler
{
    public function __construct(
        private GameRepository $gameRepository,
        private PlayerRepository $playerRepository,
    ) {}

    public function handle(ShowGameQuery $query): ?GameView
    {
        $game = $this->gameRepository->ofId(
            GameId::fromString($query->gameId),
            $query->ownerId,
        );

        if (null === $game) {
            return null;
        }

        $participantNames = [];
        foreach ($game->getParticipantIds() as $participantId) {
            $player = $this->playerRepository->ofId(
                PlayerId::fromString($participantId),
                $query->ownerId,
            );
            $participantNames[] = $player?->getName() ?? '?';
        }

        return new GameView(
            id: $game->getId()->toString(),
            name: $game->getName(),
            mode: $game->getMode()->value,
            participantNames: $participantNames,
        );
    }
}
