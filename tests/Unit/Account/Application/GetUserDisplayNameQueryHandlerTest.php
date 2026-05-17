<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\GetUserDisplayName\GetUserDisplayNameQuery;
use App\Account\Application\GetUserDisplayName\GetUserDisplayNameQueryHandler;
use App\Account\Domain\User\UserId;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Fake\Account\InMemoryUserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetUserDisplayNameQueryHandlerTest extends TestCase
{
    #[Test]
    public function itReturnsTheUserFullNameWhenSet(): void
    {
        $id = UserId::fromString('01900000-0000-7000-8000-000000000020');
        $userRepository = new InMemoryUserRepository();
        $userRepository->create(UserBuilder::aUser()->withId($id)->named('Hugues Gobet')->build());
        $handler = new GetUserDisplayNameQueryHandler($userRepository);

        $name = $handler->handle(new GetUserDisplayNameQuery($id->toString()));

        self::assertSame('Hugues Gobet', $name);
    }

    #[Test]
    public function itReturnsAnonymousWhenTheUserHasNoName(): void
    {
        $id = UserId::fromString('01900000-0000-7000-8000-000000000021');
        $userRepository = new InMemoryUserRepository();
        $userRepository->create(UserBuilder::aUser()->withId($id)->withoutName()->build());
        $handler = new GetUserDisplayNameQueryHandler($userRepository);

        $name = $handler->handle(new GetUserDisplayNameQuery($id->toString()));

        self::assertSame('Anonyme', $name);
    }

    #[Test]
    public function itThrowsWhenTheUserIsUnknown(): void
    {
        $handler = new GetUserDisplayNameQueryHandler(new InMemoryUserRepository());

        $this->expectException(\LogicException::class);

        $handler->handle(new GetUserDisplayNameQuery('01900000-0000-7000-8000-000000000022'));
    }
}
