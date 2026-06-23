<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\CorrectLastClassicDeal\CorrectLastClassicDealCommand;
use App\Tarot\Application\CorrectLastClassicDeal\CorrectLastClassicDealCommandHandler;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CorrectLastClassicDealCommandHandlerTest extends TestCase
{
    #[Test]
    public function itReplacesTheLastDealWithACorrectedClassicDeal(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000f1');
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

        $handler = new CorrectLastClassicDealCommandHandler($gameRepository);

        $handler->handle(new CorrectLastClassicDealCommand(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: 'garde',
            bouts: 1,
            pointsScored: 50,
            petitAuBout: 'none',
            chelem: 'none',
            poignees: [],
            miseres: [],
        ));

        $updated = $gameRepository->ofId($gameId, 'owner-user-id');
        self::assertNotNull($updated);
        self::assertCount(1, $updated->getDeals());
        self::assertSame(-78, $updated->getDeals()[0]->pointsByPlayer()['p-1']);
    }

    #[Test]
    public function itRejectsCorrectionWhenTheGameIsNotFoundForTheOwner(): void
    {
        $handler = new CorrectLastClassicDealCommandHandler(new InMemoryGameRepository());

        $this->expectException(GameNotFoundException::class);

        $handler->handle(new CorrectLastClassicDealCommand(
            ownerId: 'owner-user-id',
            gameId: '01966000-0000-7000-8000-0000000000ff',
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: 'garde',
            bouts: 1,
            pointsScored: 50,
            petitAuBout: 'none',
            chelem: 'none',
            poignees: [],
            miseres: [],
        ));
    }
}
