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
        $participants = $this->participantsOf($game, $playerNamesById);

        return new GameView(
            id: $game->getId()->toString(),
            name: $game->getName(),
            mode: $game->getMode()->value,
            participants: $participants,
            standings: $this->standingsOf($participants),
            deals: $this->dealViewsOf($game, $playerNamesById),
        );
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
                name: $this->participantNameOf($participantId, $playerNamesById),
                cumulativeScore: $this->cumulativeScoreOf($participantId, $game->getDeals()),
            );
        }

        return $participants;
    }

    /**
     * @param array<string, string> $playerNamesById
     *
     * @return list<DealView>
     */
    private function dealViewsOf(Game $game, array $playerNamesById): array
    {
        $views = [];
        $position = 0;
        foreach ($game->getDeals() as $deal) {
            ++$position;
            $views[] = new DealView(
                position: $position,
                pointsByPlayerId: $deal->pointsByPlayer(),
                scores: $this->dealScoreLinesOf($deal, $game->getParticipantIds(), $playerNamesById),
            );
        }

        return array_reverse($views);
    }

    /**
     * @param list<string>          $participantIds
     * @param array<string, string> $playerNamesById
     *
     * @return list<DealScoreLine>
     */
    private function dealScoreLinesOf(Deal $deal, array $participantIds, array $playerNamesById): array
    {
        $pointsByPlayerId = $deal->pointsByPlayer();

        $lines = [];
        foreach ($participantIds as $participantId) {
            $lines[] = new DealScoreLine(
                name: $this->participantNameOf($participantId, $playerNamesById),
                points: $pointsByPlayerId[$participantId] ?? 0,
            );
        }

        return $lines;
    }

    /**
     * @param array<string, string> $playerNamesById
     */
    private function participantNameOf(string $participantId, array $playerNamesById): string
    {
        if (!isset($playerNamesById[$participantId])) {
            throw new \LogicException(sprintf('No player name resolved for participant "%s".', $participantId));
        }

        return $playerNamesById[$participantId];
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
