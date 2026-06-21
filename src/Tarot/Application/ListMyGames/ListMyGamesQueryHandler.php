<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyGames;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use App\Tarot\Domain\Game\Deal;
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
            $participants = $this->participantsOf($game, $playerNamesById);
            $views[] = new GameSummaryView(
                id: $game->getId()->toString(),
                name: $game->getName(),
                mode: $game->getMode()->value,
                dealCount: count($game->getDeals()),
                participants: $participants,
                standings: $this->standingsOf($participants),
            );
        }

        return $views;
    }

    /**
     * @param list<ParticipantSummaryView> $participants
     *
     * @return list<ParticipantSummaryView>
     */
    private function standingsOf(array $participants): array
    {
        $standings = $participants;
        usort(
            $standings,
            static fn (ParticipantSummaryView $a, ParticipantSummaryView $b): int => $b->cumulativeScore <=> $a->cumulativeScore,
        );

        return $standings;
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
