<?php

declare(strict_types=1);

namespace App\Tarot\Application\ShowGame;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use App\Tarot\Domain\Game\Deal;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
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

        $playerNamesById = $this->loadPlayerNamesByIdOf($query->ownerId);

        return new GameView(
            id: $game->getId()->toString(),
            name: $game->getName(),
            mode: $game->getMode()->value,
            participants: $this->participantsOf($game, $playerNamesById),
            deals: $this->dealViewsOf($game),
        );
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
     * @return list<ParticipantSummaryView>
     */
    private function participantsOf(Game $game, array $playerNamesById): array
    {
        $participants = [];
        foreach ($game->getParticipantIds() as $participantId) {
            $participants[] = new ParticipantSummaryView(
                id: $participantId,
                name: $playerNamesById[$participantId] ?? '?',
                cumulativeScore: $this->cumulativeScoreOf($participantId, $game->getDeals()),
            );
        }

        return $participants;
    }

    /** @return list<DealView> */
    private function dealViewsOf(Game $game): array
    {
        $views = [];
        $position = 0;
        foreach ($game->getDeals() as $deal) {
            ++$position;
            $views[] = new DealView(
                position: $position,
                pointsByPlayerId: $deal->pointsByPlayer(),
            );
        }

        return $views;
    }

    /**
     * @param list<Deal> $deals
     */
    private function cumulativeScoreOf(string $playerId, array $deals): int
    {
        $total = 0;
        foreach ($deals as $deal) {
            $total += $deal->pointsByPlayer()[$playerId] ?? 0;
        }

        return $total;
    }
}
