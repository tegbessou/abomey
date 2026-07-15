<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final readonly class PrivacyConsentAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private Security $security,
        private PrivacyConsentChecker $privacyConsentChecker,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $authenticated = $this->security->getUser();

        if (!$authenticated instanceof SecurityUser) {
            return null;
        }

        if ($this->privacyConsentChecker->hasConsented($authenticated)) {
            return null;
        }

        return new RedirectResponse($this->urlGenerator->generate('app_welcome'));
    }
}
