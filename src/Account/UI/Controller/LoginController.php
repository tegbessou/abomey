<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use App\Account\Application\Auth\SignInUrlProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/login', name: 'app_login', methods: ['GET'])]
final class LoginController extends AbstractController
{
    public function __construct(
        private readonly SignInUrlProvider $signInUrlProvider,
    ) {}

    public function __invoke(UrlGeneratorInterface $urlGenerator): Response
    {
        $callbackUrl = $urlGenerator->generate(
            'app_auth_callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new RedirectResponse($this->signInUrlProvider->provideSignInUrl($callbackUrl));
    }
}
