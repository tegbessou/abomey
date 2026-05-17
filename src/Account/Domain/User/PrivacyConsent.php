<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

final readonly class PrivacyConsent
{
    private function __construct(
        public PrivacyPolicyVersion $version,
        public \DateTimeImmutable $acceptedAt,
    ) {}

    public static function for(PrivacyPolicyVersion $version, \DateTimeImmutable $acceptedAt): self
    {
        return new self($version, $acceptedAt);
    }
}
