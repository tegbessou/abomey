<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\RegisterOrSyncUser\RegisterOrSyncUserCommand;
use App\Account\Application\RegisterOrSyncUser\RegisterOrSyncUserCommandHandler;
use App\Account\Domain\User\UserId;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Fake\Account\InMemoryUserRepository;
use App\Tests\Stub\Account\StubUserIdGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegisterOrSyncUserCommandHandlerTest extends TestCase
{
    #[Test]
    public function itRegistersANewUserWhenNoneExistsForTheGivenExternalIdentifier(): void
    {
        $expectedId = UserId::fromString('01900000-0000-7000-8000-000000000010');
        $userRepository = new InMemoryUserRepository();
        $handler = new RegisterOrSyncUserCommandHandler(
            $userRepository,
            new StubUserIdGenerator($expectedId),
        );

        $returnedId = $handler->handle(new RegisterOrSyncUserCommand(
            externalIdentifier: 'external-identifier-new',
            email: 'hugues@example.com',
            name: 'Hugues Gobet',
        ));

        self::assertSame($expectedId, $returnedId);

        $created = $userRepository->ofExternalIdentifier('external-identifier-new');
        self::assertNotNull($created);
        self::assertSame('hugues@example.com', $created->getEmail()->toString());
        self::assertSame('Hugues Gobet', $created->getName());
    }

    #[Test]
    public function itSyncsAnExistingUserInsteadOfRegisteringWhenOneExistsForTheGivenExternalIdentifier(): void
    {
        $existingId = UserId::fromString('01900000-0000-7000-8000-000000000011');
        $userRepository = new InMemoryUserRepository();
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($existingId)
                ->withExternalIdentifier('external-identifier-existing')
                ->withEmail('old@example.com')
                ->named('Old Name')
                ->build(),
        );

        $handler = new RegisterOrSyncUserCommandHandler(
            $userRepository,
            new StubUserIdGenerator(UserId::fromString('01900000-0000-7000-8000-0000000000ff')),
        );

        $returnedId = $handler->handle(new RegisterOrSyncUserCommand(
            externalIdentifier: 'external-identifier-existing',
            email: 'new@example.com',
            name: 'New Name',
        ));

        self::assertSame($existingId, $returnedId);

        $synced = $userRepository->ofExternalIdentifier('external-identifier-existing');
        self::assertNotNull($synced);
        self::assertSame('new@example.com', $synced->getEmail()->toString());
        self::assertSame('New Name', $synced->getName());
    }
}
