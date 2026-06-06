<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Infrastructure\Doctrine\Repository\DoctrineGameRepository;
use App\Tests\Builder\Tarot\GameBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineGameRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineGameRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
        $this->repository = new DoctrineGameRepository($this->entityManager);
    }

    #[Test]
    public function itPersistsAGameAndCanRetrieveItByIdAndOwner(): void
    {
        $id = GameId::fromString('11111111-1111-4111-8111-111111111111');
        $game = GameBuilder::aGame()
            ->withId($id)
            ->ownedBy('owner-a')
            ->named('Soirée chez Paul')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();

        $this->repository->create($game);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id, 'owner-a');

        self::assertNotNull($found);
        self::assertSame('Soirée chez Paul', $found->getName());
        self::assertSame(Mode::Four, $found->getMode());
        self::assertSame(['p-1', 'p-2', 'p-3', 'p-4'], $found->getParticipantIds());
    }

    #[Test]
    public function itReturnsNullWhenTheGameDoesNotExist(): void
    {
        $found = $this->repository->ofId(
            GameId::fromString('22222222-2222-4222-8222-222222222222'),
            'owner-a',
        );

        self::assertNull($found);
    }

    #[Test]
    public function itReturnsNullWhenTheOwnerDoesNotMatch(): void
    {
        $id = GameId::fromString('33333333-3333-4333-8333-333333333333');
        $game = GameBuilder::aGame()
            ->withId($id)
            ->ownedBy('owner-a')
            ->named('Soirée privée')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $this->repository->create($game);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id, 'owner-b');

        self::assertNull($found);
    }

    #[Test]
    public function itReturnsGamesOfTheOwnerSortedByCreationDateDescending(): void
    {
        $olderGame = GameBuilder::aGame()
            ->withId(GameId::fromString('44444444-4444-4444-8444-444444444444'))
            ->ownedBy('owner-a')
            ->named('Soirée du lundi')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->createdAt(new \DateTimeImmutable('2026-05-20 19:00:00'))
            ->build();
        $newerGame = GameBuilder::aGame()
            ->withId(GameId::fromString('55555555-5555-4555-8555-555555555555'))
            ->ownedBy('owner-a')
            ->named('Soirée du jeudi')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->createdAt(new \DateTimeImmutable('2026-05-22 19:00:00'))
            ->build();
        $this->repository->create($olderGame);
        $this->repository->create($newerGame);
        $this->entityManager->clear();

        $games = $this->repository->ofOwner('owner-a');

        self::assertCount(2, $games);
        self::assertSame('Soirée du jeudi', $games[0]->getName());
        self::assertSame('Soirée du lundi', $games[1]->getName());
    }

    #[Test]
    public function itPersistsAndReloadsRecordedDealsInOrder(): void
    {
        $gameId = GameId::fromString('88888888-8888-4888-8888-888888888888');
        $game = GameBuilder::aGame()
            ->withId($gameId)
            ->ownedBy('owner-a')
            ->named('Soirée avec donnes')
            ->withMode(Mode::Four)
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
            contract: Contract::GardeSans,
            bouts: Bouts::Zero,
            pointsScored: 50,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );
        $this->repository->create($game);
        $this->entityManager->clear();

        $reloaded = $this->repository->ofId($gameId, 'owner-a');

        self::assertNotNull($reloaded);
        $deals = $reloaded->getDeals();
        self::assertCount(2, $deals);
        self::assertSame(1, $deals[0]->getPosition());
        self::assertSame(2, $deals[1]->getPosition());
        self::assertSame(
            ['p-1' => 102, 'p-2' => -34, 'p-3' => -34, 'p-4' => -34],
            $deals[0]->pointsByPlayer(),
        );
    }

    #[Test]
    public function itDoesNotReturnGamesOfOtherOwners(): void
    {
        $ownGame = GameBuilder::aGame()
            ->withId(GameId::fromString('66666666-6666-4666-8666-666666666666'))
            ->ownedBy('owner-a')
            ->named('Ma Partie')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
        $foreignGame = GameBuilder::aGame()
            ->withId(GameId::fromString('77777777-7777-4777-8777-777777777777'))
            ->ownedBy('owner-b')
            ->named('Partie d\'un autre')
            ->withMode(Mode::Four)
            ->withParticipants(['p-9', 'p-10', 'p-11', 'p-12'])
            ->build();
        $this->repository->create($ownGame);
        $this->repository->create($foreignGame);
        $this->entityManager->clear();

        $games = $this->repository->ofOwner('owner-a');

        self::assertCount(1, $games);
        self::assertSame('Ma Partie', $games[0]->getName());
    }
}
