<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LegalPagesTest extends WebTestCase
{
    #[Test]
    public function theLegalNoticePageIsAccessibleToAnonymousVisitors(): void
    {
        $client = static::createClient();

        $client->request('GET', '/legal/notice');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mentions légales');
    }

    #[Test]
    public function thePrivacyPolicyPageIsAccessibleToAnonymousVisitors(): void
    {
        $client = static::createClient();

        $client->request('GET', '/legal/privacy-policy');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Politique de confidentialité');
        self::assertSelectorTextContains('body', 'Version du document');
    }
}
