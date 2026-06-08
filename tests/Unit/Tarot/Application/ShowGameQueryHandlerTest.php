<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\ShowGame\DealScoreLine;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\Application\ShowGame\ShowGameQueryHandler;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use App\Tests\Fake\Tarot\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShowGameQueryHandlerTest extends TestCase
{
    #[Test]
    public function itReturnsZeroCumulativeScoresWhenNoDealHasBeenRecorded(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000e1');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $playerRepository = new InMemoryPlayerRepository();
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('p-'.($index + 1))
                    ->ownedBy('owner-user-id')
                    ->named($name)
                    ->build(),
            );
        }

        $handler = new ShowGameQueryHandler($gameRepository, $playerRepository);

        $view = $handler->handle(new ShowGameQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertNotNull($view);
        self::assertSame([], $view->deals);
        self::assertSame(
            ['p-1' => 0, 'p-2' => 0, 'p-3' => 0, 'p-4' => 0],
            self::cumulativesById($view->participants),
        );
    }

    #[Test]
    public function itExposesRecordedDealsWithTheirPositionsAndPointsAndUpdatesCumulativeScores(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000e2');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $game->recordClassicDeal(
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $playerRepository = new InMemoryPlayerRepository();
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('p-'.($index + 1))
                    ->ownedBy('owner-user-id')
                    ->named($name)
                    ->build(),
            );
        }

        $handler = new ShowGameQueryHandler($gameRepository, $playerRepository);

        $view = $handler->handle(new ShowGameQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertNotNull($view);
        self::assertCount(1, $view->deals);
        self::assertSame(1, $view->deals[0]->position);
        self::assertSame(
            ['p-1' => 102, 'p-2' => -34, 'p-3' => -34, 'p-4' => -34],
            $view->deals[0]->pointsByPlayerId,
        );
        self::assertEquals(
            [
                new DealScoreLine('Alice', 102),
                new DealScoreLine('Bob', -34),
                new DealScoreLine('Charlie', -34),
                new DealScoreLine('David', -34),
            ],
            $view->deals[0]->scores,
        );
        self::assertSame(
            ['p-1' => 102, 'p-2' => -34, 'p-3' => -34, 'p-4' => -34],
            self::cumulativesById($view->participants),
        );
    }

    #[Test]
    public function itAccumulatesScoresAcrossMultipleRecordedDeals(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000e3');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $game->recordClassicDeal(
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );
        $game->recordClassicDeal(
            takerId: 'p-2',
            contract: Contract::Garde,
            bouts: Bouts::Zero,
            pointsScored: 50,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $playerRepository = new InMemoryPlayerRepository();
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('p-'.($index + 1))
                    ->ownedBy('owner-user-id')
                    ->named($name)
                    ->build(),
            );
        }

        $handler = new ShowGameQueryHandler($gameRepository, $playerRepository);

        $view = $handler->handle(new ShowGameQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertNotNull($view);
        self::assertCount(2, $view->deals);
        self::assertSame(
            ['p-1' => 133, 'p-2' => -127, 'p-3' => -3, 'p-4' => -3],
            self::cumulativesById($view->participants),
        );
        self::assertSame(
            ['Alice', 'Charlie', 'David', 'Bob'],
            self::namesOf($view->standings),
        );
    }

    /**
     * @param list<\App\Tarot\Application\Shared\ParticipantSummaryView> $participants
     *
     * @return array<string, int>
     */
    private static function cumulativesById(array $participants): array
    {
        $cumulativesById = [];
        foreach ($participants as $participant) {
            $cumulativesById[$participant->id] = $participant->cumulativeScore;
        }

        return $cumulativesById;
    }

    /**
     * @param list<\App\Tarot\Application\Shared\ParticipantSummaryView> $participants
     *
     * @return list<string>
     */
    private static function namesOf(array $participants): array
    {
        $names = [];
        foreach ($participants as $participant) {
            $names[] = $participant->name;
        }

        return $names;
    }
}
