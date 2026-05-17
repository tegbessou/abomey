<?php

declare(strict_types=1);

namespace App\Account\Application\AcceptPrivacyPolicy;

final readonly class AcceptPrivacyPolicyCommand
{
    public function __construct(
        public string $userId,
        public string $version,
    ) {}
}
