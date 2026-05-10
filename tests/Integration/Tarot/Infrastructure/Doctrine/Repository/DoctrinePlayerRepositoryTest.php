<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Repository;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Infrastructure\Doctrine\Repository\DoctrinePlayerRepository;
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
    public function itPersistsAPlayerAndCanRetrieveItById(): void
    {
        $id = PlayerId::fromString('11111111-1111-4111-8111-111111111111');
        $player = Player::create($id, 'Alice');

        $this->repository->create($player);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id);

        self::assertNotNull($found);
        self::assertSame('Alice', $found->getName());
    }

    #[Test]
    public function itReturnsNullWhenPlayerDoesNotExist(): void
    {
        $found = $this->repository->ofId(PlayerId::fromString('22222222-2222-4222-8222-222222222222'));

        self::assertNull($found);
    }
}
