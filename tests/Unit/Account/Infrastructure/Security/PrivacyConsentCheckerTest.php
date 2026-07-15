<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Security;

use App\Account\Domain\User\UserId;
use App\Account\Infrastructure\Security\PrivacyConsentChecker;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Fake\Account\InMemoryUserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrivacyConsentCheckerTest extends TestCase
{
    #[Test]
    public function aUserWhoGaveConsentHasConsented(): void
    {
        $userId = UserId::fromString('01966000-0000-7000-8000-0000000000c1');
        $user = UserBuilder::aUser()
            ->withId($userId)
            ->havingAcceptedPrivacyPolicy()
            ->build();
        $userRepository = new InMemoryUserRepository();
        $userRepository->create($user);

        $checker = new PrivacyConsentChecker($userRepository);

        self::assertTrue($checker->hasConsented(new SecurityUser($userId)));
    }

    #[Test]
    public function aUserWhoDidNotGiveConsentHasNotConsented(): void
    {
        $userId = UserId::fromString('01966000-0000-7000-8000-0000000000c2');
        $user = UserBuilder::aUser()
            ->withId($userId)
            ->build();
        $userRepository = new InMemoryUserRepository();
        $userRepository->create($user);

        $checker = new PrivacyConsentChecker($userRepository);

        self::assertFalse($checker->hasConsented(new SecurityUser($userId)));
    }

    #[Test]
    public function anUnknownUserHasNotConsented(): void
    {
        $checker = new PrivacyConsentChecker(new InMemoryUserRepository());

        $unknownUser = new SecurityUser(UserId::fromString('01966000-0000-7000-8000-0000000000c3'));

        self::assertFalse($checker->hasConsented($unknownUser));
    }
}
