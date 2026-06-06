<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\AcceptPrivacyPolicy\AcceptPrivacyPolicyCommand;
use App\Account\Application\AcceptPrivacyPolicy\AcceptPrivacyPolicyCommandHandler;
use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserNotFoundException;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Fake\Account\InMemoryUserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class AcceptPrivacyPolicyCommandHandlerTest extends TestCase
{
    #[Test]
    public function itRecordsTheUserConsentWithTheGivenVersionAndCurrentTime(): void
    {
        $userId = UserId::fromString('01900000-0000-7000-8000-000000000030');
        $userRepository = new InMemoryUserRepository();
        $userRepository->create(UserBuilder::aUser()->withId($userId)->build());
        $now = new \DateTimeImmutable('2026-05-15T12:00:00+00:00');
        $handler = new AcceptPrivacyPolicyCommandHandler($userRepository, new MockClock($now));

        $handler->handle(new AcceptPrivacyPolicyCommand(
            userId: $userId->toString(),
            version: PrivacyPolicyVersion::V2026_05_15->value,
        ));

        $reloaded = $userRepository->ofId($userId);
        self::assertNotNull($reloaded);
        $consent = $reloaded->getPrivacyConsent();
        self::assertNotNull($consent);
        self::assertSame(PrivacyPolicyVersion::V2026_05_15, $consent->version);
        self::assertEquals($now, $consent->acceptedAt);
    }

    #[Test]
    public function itThrowsWhenTheUserIsUnknown(): void
    {
        $handler = new AcceptPrivacyPolicyCommandHandler(
            new InMemoryUserRepository(),
            new MockClock(new \DateTimeImmutable('2026-05-15T12:00:00+00:00')),
        );

        $this->expectException(UserNotFoundException::class);

        $handler->handle(new AcceptPrivacyPolicyCommand(
            userId: '01900000-0000-7000-8000-000000000031',
            version: PrivacyPolicyVersion::V2026_05_15->value,
        ));
    }
}
