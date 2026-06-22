<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\RecordVachette\RecordVachetteCommand;
use App\Tarot\Application\RecordVachette\RecordVachetteCommandHandler;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\Mode;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RecordVachetteCommandHandlerTest extends TestCase
{
    #[Test]
    public function itRecordsAVachetteOnTheTargetedGame(): void
    {
        $gameId = GameId::fromString('01966000-0000-7000-8000-0000000000e1');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-user-id')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $gameRepository = new InMemoryGameRepository();
        $gameRepository->create($game);

        $handler = new RecordVachetteCommandHandler($gameRepository);

        $handler->handle(new RecordVachetteCommand(
            ownerId: 'owner-user-id',
            gameId: $gameId->toString(),
            deadPlayerIds: [],
            ranking: ['p-1', 'p-2', 'p-3', 'p-4'],
        ));

        $updated = $gameRepository->ofId($gameId, 'owner-user-id');
        self::assertNotNull($updated);
        self::assertCount(1, $updated->getDeals());
    }

    #[Test]
    public function itRejectsRecordingWhenTheGameIsNotFoundForTheOwner(): void
    {
        $handler = new RecordVachetteCommandHandler(new InMemoryGameRepository());

        $this->expectException(GameNotFoundException::class);

        $handler->handle(new RecordVachetteCommand(
            ownerId: 'owner-user-id',
            gameId: '01966000-0000-7000-8000-0000000000ff',
            deadPlayerIds: [],
            ranking: ['p-1', 'p-2', 'p-3', 'p-4'],
        ));
    }
}
