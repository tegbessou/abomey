<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Security;

use App\Account\Domain\User\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class PrivacyConsentVoter extends Voter
{
    public const string ATTRIBUTE = 'PRIVACY_CONSENT_GIVEN';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ATTRIBUTE === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $authenticated = $token->getUser();

        if (!$authenticated instanceof SecurityUser) {
            return false;
        }

        $user = $this->userRepository->ofId($authenticated->getUserId());

        return null !== $user && null !== $user->getPrivacyConsent();
    }
}
