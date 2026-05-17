<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\User\PrivacyConsent;
use App\Account\Domain\User\PrivacyPolicyVersion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrivacyConsentTest extends TestCase
{
    #[Test]
    public function itHoldsAVersionAndAnAcceptanceDate(): void
    {
        $acceptedAt = new \DateTimeImmutable('2026-05-15T12:00:00+00:00');
        $consent = PrivacyConsent::for(PrivacyPolicyVersion::V2026_05_15, $acceptedAt);

        self::assertSame(PrivacyPolicyVersion::V2026_05_15, $consent->version);
        self::assertSame($acceptedAt, $consent->acceptedAt);
    }
}
