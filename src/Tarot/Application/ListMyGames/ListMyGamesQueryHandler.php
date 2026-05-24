<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Player\PlayerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus', method: 'handle')]
final readonly class ListMyGamesQueryHandler
{
    public function __construct(
        private GameRepository $gameRepository,
        private PlayerRepository $playerRepository,
    ) {}

    /** @return list<GameSummaryView> */
    public function handle(ListMyGamesQuery $query): array
    {
        $games = $this->gameRepository->ofOwner($query->ownerId);
        $playerNamesById = $this->loadPlayerNamesByIdOf($query->ownerId);

        $views = [];
        foreach ($games as $game) {
            $views[] = new GameSummaryView(
                id: $game->getId()->toString(),
                name: $game->getName(),
                mode: $game->getMode()->value,
                participantNames: $this->participantNamesOf($game, $playerNamesById),
            );
        }

        return $views;
    }

    /** @return array<string, string> */
    private function loadPlayerNamesByIdOf(string $ownerId): array
    {
        $playerNamesById = [];
        foreach ($this->playerRepository->allOf($ownerId) as $player) {
            $playerNamesById[$player->getId()->toString()] = $player->getName();
        }

        return $playerNamesById;
    }

    /**
     * @param array<string, string> $playerNamesById
     *
     * @return list<string>
     */
    private function participantNamesOf(Game $game, array $playerNamesById): array
    {
        $participantNames = [];
        foreach ($game->getParticipantIds() as $participantId) {
            $participantNames[] = $playerNamesById[$participantId] ?? '?';
        }

        return $participantNames;
    }
}
