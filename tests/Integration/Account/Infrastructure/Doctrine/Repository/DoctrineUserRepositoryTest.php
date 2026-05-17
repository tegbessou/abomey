<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\Doctrine\Repository;

use App\Account\Domain\User\Email;
use App\Account\Domain\User\UserId;
use App\Account\Infrastructure\Doctrine\Repository\DoctrineUserRepository;
use App\Tests\Builder\Account\UserBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineUserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineUserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
        $this->repository = new DoctrineUserRepository($this->entityManager);
    }

    #[Test]
    public function itPersistsAUserAndCanRetrieveItByExternalIdentifier(): void
    {
        $user = UserBuilder::aUser()
            ->withId('11111111-1111-4111-8111-111111111111')
            ->withExternalIdentifier('external-identifier-persisted')
            ->withEmail('hugues@example.com')
            ->named('Hugues Gobet')
            ->build();

        $this->repository->create($user);
        $this->entityManager->clear();

        $found = $this->repository->ofExternalIdentifier('external-identifier-persisted');

        self::assertNotNull($found);
        self::assertSame('hugues@example.com', $found->getEmail()->toString());
        self::assertSame('Hugues Gobet', $found->getName());
    }

    #[Test]
    public function itReturnsNullWhenNoUserMatchesTheExternalIdentifier(): void
    {
        $found = $this->repository->ofExternalIdentifier('unknown-external-identifier');

        self::assertNull($found);
    }

    #[Test]
    public function itPersistsSyncedChangesWhenUpdating(): void
    {
        $user = UserBuilder::aUser()
            ->withId('22222222-2222-4222-8222-222222222222')
            ->withExternalIdentifier('external-identifier-to-be-synced')
            ->withEmail('old@example.com')
            ->named('Old Name')
            ->build();
        $this->repository->create($user);

        $user->syncFromProvider(Email::fromString('new@example.com'), 'New Name');
        $this->repository->update($user);
        $this->entityManager->clear();

        $reloaded = $this->repository->ofExternalIdentifier('external-identifier-to-be-synced');

        self::assertNotNull($reloaded);
        self::assertSame('new@example.com', $reloaded->getEmail()->toString());
        self::assertSame('New Name', $reloaded->getName());
    }

    #[Test]
    public function itCanRetrieveAUserByItsInternalIdentifier(): void
    {
        $id = UserId::fromString('55555555-5555-4555-8555-555555555555');
        $user = UserBuilder::aUser()
            ->withId($id)
            ->withExternalIdentifier('external-identifier-lookup-by-id')
            ->withEmail('lookup@example.com')
            ->named('Lookup User')
            ->build();
        $this->repository->create($user);
        $this->entityManager->clear();

        $found = $this->repository->ofId($id);

        self::assertNotNull($found);
        self::assertSame('lookup@example.com', $found->getEmail()->toString());
    }

    #[Test]
    public function itReturnsNullWhenLookingUpAnUnknownInternalIdentifier(): void
    {
        $found = $this->repository->ofId(
            UserId::fromString('66666666-6666-4666-8666-666666666666'),
        );

        self::assertNull($found);
    }

    #[Test]
    public function itDeletesAUserPermanently(): void
    {
        $id = UserId::fromString('77777777-7777-4777-8777-777777777777');
        $user = UserBuilder::aUser()
            ->withId($id)
            ->withExternalIdentifier('external-identifier-to-delete')
            ->withEmail('delete@example.com')
            ->named('To Delete')
            ->build();
        $this->repository->create($user);
        $this->entityManager->clear();

        $reloaded = $this->repository->ofId($id);
        self::assertNotNull($reloaded);
        $this->repository->delete($reloaded);
        $this->entityManager->clear();

        self::assertNull($this->repository->ofId($id));
    }
}
