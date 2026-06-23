<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\CorrectLastVachette\CorrectLastVachetteCommand;
use App\Tarot\Application\CorrectLastVachette\CorrectLastVachetteCommandHandler;
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

final class CorrectLastVachetteCommandHandlerTest extends TestCase
{
    #[Test]
    public function itReplacesTheLastDealWithACorrectedVachette(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000f2');
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

        $handler = new CorrectLastVachetteCommandHandler($gameRepository);

        $handler->handle(new CorrectLastVachetteCommand(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
            deadPlayerIds: [],
            ranking: ['p-1', 'p-2', 'p-3', 'p-4'],
        ));

        $updated = $gameRepository->ofId($gameId, 'owner-user-id');
        self::assertNotNull($updated);
        self::assertCount(1, $updated->getDeals());
        self::assertSame(120, $updated->getDeals()[0]->pointsByPlayer()['p-1']);
    }

    #[Test]
    public function itRejectsCorrectionWhenTheGameIsNotFoundForTheOwner(): void
    {
        $handler = new CorrectLastVachetteCommandHandler(new InMemoryGameRepository());

        $this->expectException(GameNotFoundException::class);

        $handler->handle(new CorrectLastVachetteCommand(
            ownerId: 'owner-user-id',
            gameId: '01966000-0000-7000-8000-0000000000ff',
            deadPlayerIds: [],
            ranking: ['p-1', 'p-2', 'p-3', 'p-4'],
        ));
    }
}
