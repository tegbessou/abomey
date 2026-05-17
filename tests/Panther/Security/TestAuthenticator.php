<?php

declare(strict_types=1);

namespace App\Tests\Panther\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class TestAuthenticator extends AbstractAuthenticator
{
    private const string COOKIE_NAME = 'AUTH';

    public function supports(Request $request): bool
    {
        return $request->cookies->has(self::COOKIE_NAME);
    }

    public function authenticate(Request $request): Passport
    {
        $raw = $request->cookies->get(self::COOKIE_NAME);

        if (!is_string($raw) || '' === $raw) {
            throw new AuthenticationException('Missing AUTH cookie payload.');
        }

        try {
            $payload = json_decode($raw, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new AuthenticationException('Malformed AUTH cookie payload.');
        }

        if (!is_array($payload) || !isset($payload['id']) || !is_string($payload['id']) || '' === $payload['id']) {
            throw new AuthenticationException('AUTH cookie payload must contain a non-empty "id".');
        }

        return new SelfValidatingPassport(new UserBadge($payload['id']));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new Response('Test authentication failed: '.$exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }
}
