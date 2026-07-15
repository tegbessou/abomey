<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Security;

use App\Account\Domain\User\UserRepository;

final readonly class PrivacyConsentChecker
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function hasConsented(SecurityUser $securityUser): bool
    {
        $user = $this->userRepository->ofId($securityUser->getUserId());

        return null !== $user && null !== $user->getPrivacyConsent();
    }
}
