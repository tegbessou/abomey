<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;
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
}
