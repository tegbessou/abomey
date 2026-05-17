<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Infrastructure\Doctrine\Repository\DoctrinePlayerRepository;
use App\Tests\Builder\Tarot\PlayerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrinePlayerRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrinePlayerRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
        $this->repository = new DoctrinePlayerRepository($this->entityManager);
    }

    #[Test]
    public function itPersistsAPlayerAndCanRetrieveItByIdAndOwner(): void
    {
        $id = PlayerId::fromString('11111111-1111-4111-8111-111111111111');
        $player = PlayerBuilder::aPlayer()->withId($id)->ownedBy('owner-user-a')->named('Alice')->build();

        $this->repository->create($player);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id, 'owner-user-a');

        self::assertNotNull($found);
        self::assertSame('Alice', $found->getName());
        self::assertSame('owner-user-a', $found->getOwner());
    }

    #[Test]
    public function itReturnsNullWhenPlayerDoesNotExist(): void
    {
        $found = $this->repository->ofId(
            PlayerId::fromString('22222222-2222-4222-8222-222222222222'),
            'owner-user-a',
        );

        self::assertNull($found);
    }

    #[Test]
    public function itReturnsNullWhenTheOwnerDoesNotMatch(): void
    {
        $id = PlayerId::fromString('33333333-3333-4333-8333-333333333333');
        $player = PlayerBuilder::aPlayer()->withId($id)->ownedBy('owner-user-a')->named('Alice')->build();
        $this->repository->create($player);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id, 'owner-user-b');

        self::assertNull($found);
    }

    #[Test]
    public function itDeletesAllPlayersOfTheGivenOwnerLeavingOthersUntouched(): void
    {
        $alice = PlayerBuilder::aPlayer()
            ->withId('66666666-6666-4666-8666-666666666666')
            ->ownedBy('owner-to-purge')
            ->named('Alice')
            ->build();
        $bob = PlayerBuilder::aPlayer()
            ->withId('77777777-7777-4777-8777-777777777777')
            ->ownedBy('owner-to-purge')
            ->named('Bob')
            ->build();
        $charlie = PlayerBuilder::aPlayer()
            ->withId('88888888-8888-4888-8888-888888888888')
            ->ownedBy('owner-to-keep')
            ->named('Charlie')
            ->build();
        $this->repository->create($alice);
        $this->repository->create($bob);
        $this->repository->create($charlie);
        $this->entityManager->clear();

        $this->repository->deleteAllOf('owner-to-purge');
        $this->entityManager->clear();

        self::assertNull($this->repository->ofId($alice->getId(), 'owner-to-purge'));
        self::assertNull($this->repository->ofId($bob->getId(), 'owner-to-purge'));
        self::assertNotNull($this->repository->ofId($charlie->getId(), 'owner-to-keep'));
    }

    #[Test]
    public function twoPlayersWithTheSameIdAcrossDifferentOwnersAreFullyIsolated(): void
    {
        $aliceFromA = PlayerBuilder::aPlayer()
            ->withId('44444444-4444-4444-8444-444444444444')
            ->ownedBy('owner-user-a')
            ->named('Alice (A)')
            ->build();
        $bobFromB = PlayerBuilder::aPlayer()
            ->withId('55555555-5555-4555-8555-555555555555')
            ->ownedBy('owner-user-b')
            ->named('Bob (B)')
            ->build();
        $this->repository->create($aliceFromA);
        $this->repository->create($bobFromB);
        $this->entityManager->clear();

        $aliceLookup = $this->repository->ofId($aliceFromA->getId(), 'owner-user-a');
        $bobLookup = $this->repository->ofId($bobFromB->getId(), 'owner-user-b');
        $crossLookup = $this->repository->ofId($aliceFromA->getId(), 'owner-user-b');

        self::assertNotNull($aliceLookup);
        self::assertSame('Alice (A)', $aliceLookup->getName());
        self::assertNotNull($bobLookup);
        self::assertSame('Bob (B)', $bobLookup->getName());
        self::assertNull($crossLookup);
    }
}
