<?php

declare(strict_types=1);

namespace App\Tarot\Application\GetLastDeal;

use App\Tarot\Domain\Game\ClassicDeal;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\VachetteDeal;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus', method: 'handle')]
final readonly class GetLastDealQueryHandler
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {}

    public function handle(GetLastDealQuery $query): ClassicLastDealView|VachetteLastDealView|null
    {
        $game = $this->gameRepository->ofId(
            GameId::fromString($query->gameId),
            $query->ownerId,
        );

        if (null === $game) {
            return null;
        }

        $last = array_last($game->getDeals());

        if ($last instanceof ClassicDeal) {
            $snapshot = $last->editSnapshot();

            return new ClassicLastDealView(
                deadPlayerIds: $this->deadPlayerIdsOf($game, $snapshot['activePlayerIds']),
                partnerId: $snapshot['partnerId'],
                takerId: $snapshot['takerId'],
                contract: $snapshot['contract'],
                bouts: $snapshot['bouts'],
                pointsScored: $snapshot['pointsScored'],
                petitAuBout: $snapshot['petitAuBout'],
                chelem: $snapshot['chelem'],
                poignees: $snapshot['poignees'],
                miseres: $snapshot['miseres'],
            );
        }

        if ($last instanceof VachetteDeal) {
            $snapshot = $last->editSnapshot();

            return new VachetteLastDealView(
                deadPlayerIds: $this->deadPlayerIdsOf($game, $snapshot['ranking']),
                ranking: $snapshot['ranking'],
            );
        }

        return null;
    }

    /**
     * @param list<string> $activePlayerIds
     *
     * @return list<string>
     */
    private function deadPlayerIdsOf(Game $game, array $activePlayerIds): array
    {
        return array_values(array_diff($game->getParticipantIds(), $activePlayerIds));
    }
}
