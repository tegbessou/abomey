<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\CreateGame\CreateGameCommand;
use App\Tarot\Application\CreateGame\CreateGameCommandHandler;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\ParticipantNotOwnedException;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use App\Tests\Fake\Tarot\InMemoryPlayerRepository;
use App\Tests\Stub\Tarot\StubGameIdGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateGameCommandHandlerTest extends TestCase
{
    #[Test]
    public function itCreatesAGameWhenAllParticipantsBelongToTheOwner(): void
    {
        $playerRepository = new InMemoryPlayerRepository();
        $alice = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000001')->ownedBy('owner-user-id')->named('Alice')->build();
        $bob = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000002')->ownedBy('owner-user-id')->named('Bob')->build();
        $charlie = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000003')->ownedBy('owner-user-id')->named('Charlie')->build();
        $david = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000004')->ownedBy('owner-user-id')->named('David')->build();
        $playerRepository->create($alice);
        $playerRepository->create($bob);
        $playerRepository->create($charlie);
        $playerRepository->create($david);

        $gameRepository = new InMemoryGameRepository();
        $expectedGameId = GameId::fromString('01966000-0000-7000-8000-0000000000aa');
        $handler = new CreateGameCommandHandler(
            $gameRepository,
            new StubGameIdGenerator($expectedGameId),
            $playerRepository,
        );

        $returnedId = $handler->handle(new CreateGameCommand(
            ownerId: 'owner-user-id',
            name: 'Soirée chez Paul',
            mode: 4,
            participantIds: [
                $alice->getId()->toString(),
                $bob->getId()->toString(),
                $charlie->getId()->toString(),
                $david->getId()->toString(),
            ],
        ));

        self::assertSame($expectedGameId, $returnedId);
        $persisted = $gameRepository->ofId($expectedGameId, 'owner-user-id');
        self::assertNotNull($persisted);
        self::assertSame('Soirée chez Paul', $persisted->getName());
        self::assertSame(Mode::Four, $persisted->getMode());
        self::assertCount(4, $persisted->getParticipantIds());
    }

    #[Test]
    public function itRejectsCreationWhenAParticipantBelongsToAnotherOwner(): void
    {
        $playerRepository = new InMemoryPlayerRepository();
        $alice = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000005')->ownedBy('owner-user-id')->named('Alice')->build();
        $bob = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000006')->ownedBy('owner-user-id')->named('Bob')->build();
        $charlie = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000007')->ownedBy('owner-user-id')->named('Charlie')->build();
        $hostile = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000008')->ownedBy('another-user-id')->named('Hostile')->build();
        $playerRepository->create($alice);
        $playerRepository->create($bob);
        $playerRepository->create($charlie);
        $playerRepository->create($hostile);

        $handler = new CreateGameCommandHandler(
            new InMemoryGameRepository(),
            new StubGameIdGenerator(GameId::fromString('01966000-0000-7000-8000-0000000000bb')),
            $playerRepository,
        );

        $this->expectException(ParticipantNotOwnedException::class);

        $handler->handle(new CreateGameCommand(
            ownerId: 'owner-user-id',
            name: 'Tentative malveillante',
            mode: 4,
            participantIds: [
                $alice->getId()->toString(),
                $bob->getId()->toString(),
                $charlie->getId()->toString(),
                $hostile->getId()->toString(),
            ],
        ));
    }
}
