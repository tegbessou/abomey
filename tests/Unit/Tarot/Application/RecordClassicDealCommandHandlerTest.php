<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\RecordClassicDeal\RecordClassicDealCommand;
use App\Tarot\Application\RecordClassicDeal\RecordClassicDealCommandHandler;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\Mode;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RecordClassicDealCommandHandlerTest extends TestCase
{
    #[Test]
    public function itRecordsAClassicDealOnTheTargetedGame(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000d1');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $handler = new RecordClassicDealCommandHandler($gameRepository);

        $handler->handle(new RecordClassicDealCommand(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: 'garde',
            bouts: 1,
            pointsScored: 60,
            petitAuBout: 'none',
            chelem: 'none',
            poignees: [],
            miseres: [],
        ));

        $updated = $gameRepository->ofId($gameId, 'owner-user-id');
        self::assertNotNull($updated);
        self::assertCount(1, $updated->getDeals());
    }

    #[Test]
    public function itRejectsRecordingWhenTheGameIsNotFoundForTheOwner(): void
    {
        $handler = new RecordClassicDealCommandHandler(new InMemoryGameRepository());

        $this->expectException(GameNotFoundException::class);

        $handler->handle(new RecordClassicDealCommand(
            ownerId: 'owner-user-id',
            gameId: '01966000-0000-7000-8000-0000000000ff',
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: 'garde',
            bouts: 1,
            pointsScored: 60,
            petitAuBout: 'none',
            chelem: 'none',
            poignees: [],
            miseres: [],
        ));
    }
}
