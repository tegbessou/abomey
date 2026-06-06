<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\ListMyGames\ListMyGamesQuery;
use App\Tarot\Application\ListMyGames\ListMyGamesQueryHandler;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Fake\Tarot\InMemoryGameRepository;
use App\Tests\Fake\Tarot\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListMyGamesQueryHandlerTest extends TestCase
{
    #[Test]
    public function itReturnsAnEmptyListWhenTheOwnerHasNoGame(): void
    {
        $handler = new ListMyGamesQueryHandler(
            new InMemoryGameRepository(),
            new InMemoryPlayerRepository(),
        );

        $result = $handler->handle(new ListMyGamesQuery('owner-user-id'));

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsAViewPerGameOwnedByTheUserAndIgnoresGamesOfOtherOwners(): void
    {
        $gameRepository = new InMemoryGameRepository();
        $ownedGame = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000aa')
            ->ownedBy('owner-user-id')
            ->named('Soirée chez Paul')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $foreignGame = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000bb')
            ->ownedBy('another-user-id')
            ->named('Partie d\'un autre Utilisateur')
            ->withMode(Mode::Four)
            ->withParticipants(['x-1', 'x-2', 'x-3', 'x-4'])
            ->build();
        $gameRepository->create($ownedGame);
        $gameRepository->create($foreignGame);

        $handler = new ListMyGamesQueryHandler($gameRepository, new InMemoryPlayerRepository());

        $result = $handler->handle(new ListMyGamesQuery('owner-user-id'));

        self::assertCount(1, $result);
        self::assertSame('01966000-0000-7000-8000-0000000000aa', $result[0]->id);
        self::assertSame('Soirée chez Paul', $result[0]->name);
        self::assertSame(4, $result[0]->mode);
    }

    #[Test]
    public function itResolvesParticipantNamesInTheOrderOfTheGameParticipantIds(): void
    {
        $playerRepository = new InMemoryPlayerRepository();
        $alice = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000a01')->ownedBy('owner-user-id')->named('Alice')->build();
        $bob = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000a02')->ownedBy('owner-user-id')->named('Bob')->build();
        $charlie = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000a03')->ownedBy('owner-user-id')->named('Charlie')->build();
        $david = PlayerBuilder::aPlayer()->withId('01966000-0000-7000-8000-000000000a04')->ownedBy('owner-user-id')->named('David')->build();
        $playerRepository->create($alice);
        $playerRepository->create($bob);
        $playerRepository->create($charlie);
        $playerRepository->create($david);

        $gameRepository = new InMemoryGameRepository();
        $game = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000aa')
            ->ownedBy('owner-user-id')
            ->withParticipants([
                $alice->getId()->toString(),
                $bob->getId()->toString(),
                $charlie->getId()->toString(),
                $david->getId()->toString(),
            ])
            ->build();
        $gameRepository->create($game);

        $handler = new ListMyGamesQueryHandler($gameRepository, $playerRepository);

        $result = $handler->handle(new ListMyGamesQuery('owner-user-id'));

        self::assertCount(1, $result);
        self::assertSame(
            ['Alice', 'Bob', 'Charlie', 'David'],
            array_map(static fn ($p): string => $p->name, $result[0]->participants),
        );
    }

    #[Test]
    public function itReturnsTheGamesSortedByCreationDateDescending(): void
    {
        $gameRepository = new InMemoryGameRepository();
        $olderGame = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000a1')
            ->ownedBy('owner-user-id')
            ->named('Soirée du lundi')
            ->createdAt(new \DateTimeImmutable('2026-05-20 19:00:00'))
            ->build();
        $newerGame = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000a2')
            ->ownedBy('owner-user-id')
            ->named('Soirée du jeudi')
            ->createdAt(new \DateTimeImmutable('2026-05-22 19:00:00'))
            ->build();
        $gameRepository->create($olderGame);
        $gameRepository->create($newerGame);

        $handler = new ListMyGamesQueryHandler($gameRepository, new InMemoryPlayerRepository());

        $result = $handler->handle(new ListMyGamesQuery('owner-user-id'));

        self::assertCount(2, $result);
        self::assertSame('Soirée du jeudi', $result[0]->name);
        self::assertSame('Soirée du lundi', $result[1]->name);
    }

    #[Test]
    public function aGameSummaryExposesItsDealCountAndCumulativeScoresByPlayerId(): void
    {
        $game = GameBuilder::aGame()
            ->withId('01966000-0000-7000-8000-0000000000f1')
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

        $handler = new ListMyGamesQueryHandler($gameRepository, new InMemoryPlayerRepository());

        $result = $handler->handle(new ListMyGamesQuery('owner-user-id'));

        self::assertCount(1, $result);
        self::assertSame(1, $result[0]->dealCount);

        $cumulativesById = [];
        foreach ($result[0]->participants as $participant) {
            $cumulativesById[$participant->id] = $participant->cumulativeScore;
        }
        self::assertSame(
            ['p-1' => 102, 'p-2' => -34, 'p-3' => -34, 'p-4' => -34],
            $cumulativesById,
        );
    }
}
