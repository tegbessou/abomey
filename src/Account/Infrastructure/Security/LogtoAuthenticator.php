<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Security;

use App\Account\Application\RegisterOrSyncUser\RegisterOrSyncUserCommand;
use App\Account\Domain\User\UserId;
use App\Account\Infrastructure\Logto\LogtoClientFactory;
use App\Shared\Application\Bus\CommandBus;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LogtoAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly LogtoClientFactory $logtoClientFactory,
        private readonly CommandBus $commandBus,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function supports(Request $request): bool
    {
        return 'app_auth_callback' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->logtoClientFactory->create();
        $client->handleSignInCallback();

        $claims = $client->getIdTokenClaims();

        /** @var UserId $userId */
        $userId = $this->commandBus->dispatch(new RegisterOrSyncUserCommand(
            externalIdentifier: $claims->sub,
            email: $claims->email ?? '',
            name: $claims->name,
        ));

        return new SelfValidatingPassport(new UserBadge($userId->toString()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        if ($request->hasSession()) {
            $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
            if (null !== $targetPath) {
                $this->removeTargetPath($request->getSession(), $firewallName);

                return new RedirectResponse($targetPath);
            }
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if ($request->hasSession() && 'GET' === $request->getMethod()) {
            $this->saveTargetPath($request->getSession(), 'main', $request->getUri());
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
}
