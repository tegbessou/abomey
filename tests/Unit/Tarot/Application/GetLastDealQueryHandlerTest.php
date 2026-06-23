<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\GetLastDeal\ClassicLastDealView;
use App\Tarot\Application\GetLastDeal\GetLastDealQuery;
use App\Tarot\Application\GetLastDeal\GetLastDealQueryHandler;
use App\Tarot\Application\GetLastDeal\VachetteLastDealView;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Domain\Game\Ranking;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetLastDealQueryHandlerTest extends TestCase
{
    #[Test]
    public function itExposesTheLastClassicDealForEditing(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-000000000071');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
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

        $handler = new GetLastDealQueryHandler($gameRepository);

        $view = $handler->handle(new GetLastDealQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertInstanceOf(ClassicLastDealView::class, $view);
        self::assertSame([], $view->deadPlayerIds);
        self::assertNull($view->partnerId);
        self::assertSame('p-1', $view->takerId);
        self::assertSame('garde', $view->contract);
        self::assertSame(1, $view->bouts);
        self::assertSame(60, $view->pointsScored);
        self::assertSame('none', $view->petitAuBout);
        self::assertSame('none', $view->chelem);
    }

    #[Test]
    public function itExposesTheLastVachetteForEditing(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-000000000072');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $game->recordVachette(
            deadPlayerIds: [],
            ranking: new Ranking(['p-1', 'p-2', 'p-3', 'p-4']),
        );
        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $handler = new GetLastDealQueryHandler($gameRepository);

        $view = $handler->handle(new GetLastDealQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertInstanceOf(VachetteLastDealView::class, $view);
        self::assertSame([], $view->deadPlayerIds);
        self::assertSame(['p-1', 'p-2', 'p-3', 'p-4'], $view->ranking);
    }

    #[Test]
    public function itReturnsNullWhenThereIsNoDealToEdit(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-000000000073');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $handler = new GetLastDealQueryHandler($gameRepository);

        $view = $handler->handle(new GetLastDealQuery(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
        ));

        self::assertNull($view);
    }
}
