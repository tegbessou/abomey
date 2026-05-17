<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\User\Email;
use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\Domain\User\User;
use App\Account\Domain\User\UserDeleted;
use App\Account\Domain\User\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function itIsRegisteredWithItsExternalIdentifierEmailAndName(): void
    {
        $id = UserId::fromString('01900000-0000-7000-8000-000000000001');
        $email = Email::fromString('hugues@example.com');

        $user = User::register($id, 'external-identifier-001', $email, 'Hugues Gobet');

        self::assertSame($id, $user->getId());
        self::assertSame('external-identifier-001', $user->getExternalIdentifier());
        self::assertSame($email, $user->getEmail());
        self::assertSame('Hugues Gobet', $user->getName());
    }

    #[Test]
    public function itAcceptsBeingRegisteredWithoutAName(): void
    {
        $user = User::register(
            UserId::fromString('01900000-0000-7000-8000-000000000002'),
            'external-identifier-002',
            Email::fromString('user@privaterelay.appleid.com'),
            null,
        );

        self::assertNull($user->getName());
    }

    #[Test]
    public function itUpdatesEmailAndNameWhenSyncedFromProvider(): void
    {
        $user = User::register(
            UserId::fromString('01900000-0000-7000-8000-000000000003'),
            'external-identifier-003',
            Email::fromString('old@example.com'),
            'Old Name',
        );

        $user->syncFromProvider(Email::fromString('new@example.com'), 'New Name');

        self::assertSame('new@example.com', $user->getEmail()->toString());
        self::assertSame('New Name', $user->getName());
    }

    #[Test]
    public function aNewlyRegisteredUserHasNoPrivacyConsent(): void
    {
        $user = User::register(
            UserId::fromString('01900000-0000-7000-8000-000000000004'),
            'external-identifier-004',
            Email::fromString('user@example.com'),
            'Some User',
        );

        self::assertNull($user->getPrivacyConsent());
    }

    #[Test]
    public function aUserCanAcceptThePrivacyPolicy(): void
    {
        $user = User::register(
            UserId::fromString('01900000-0000-7000-8000-000000000005'),
            'external-identifier-005',
            Email::fromString('user@example.com'),
            'Some User',
        );
        $acceptedAt = new \DateTimeImmutable('2026-05-15T12:00:00+00:00');

        $user->acceptPrivacyPolicy(PrivacyPolicyVersion::V2026_05_15, $acceptedAt);

        $consent = $user->getPrivacyConsent();
        self::assertNotNull($consent);
        self::assertSame(PrivacyPolicyVersion::V2026_05_15, $consent->version);
        self::assertEquals($acceptedAt, $consent->acceptedAt);
    }

    #[Test]
    public function aDeletedUserRecordsAUserDeletedEvent(): void
    {
        $user = User::register(
            UserId::fromString('01900000-0000-7000-8000-000000000006'),
            'external-identifier-006',
            Email::fromString('user@example.com'),
            'Some User',
        );

        $user->delete();

        $events = $user->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(UserDeleted::class, $events[0]);
        self::assertSame('01900000-0000-7000-8000-000000000006', $events[0]->userId);
    }
}
