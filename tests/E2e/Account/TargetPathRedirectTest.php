<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TargetPathRedirectTest extends WebTestCase
{
    #[Test]
    public function anAnonymousVisitorRequestingAProtectedRouteIsRedirectedToHome(): void
    {
        $client = static::createClient();

        $client->request('GET', '/account');

        self::assertResponseRedirects('/');
    }

    #[Test]
    public function theInitialTargetUrlIsMemorizedInSessionForTheAnonymousVisitor(): void
    {
        $client = static::createClient();

        $client->request('GET', '/account');

        self::assertResponseRedirects('/');

        $session = $client->getRequest()->getSession();
        $targetPath = $session->get('_security.main.target_path');
        self::assertIsString($targetPath);
        self::assertStringEndsWith('/account', $targetPath);
    }
}
