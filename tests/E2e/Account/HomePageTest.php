<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tests\Builder\Account\UserBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomePageTest extends WebTestCase
{
    #[Test]
    public function theHomePageIsAccessibleToAnonymousVisitorsAndShowsTheLoginButton(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Abomey');
        self::assertSelectorExists('a[href$="/login"]');
    }

    #[Test]
    public function theHomePageShowsTheUserNameWhenLoggedInWithConsent(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $user = UserBuilder::aUser()
            ->withId('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
            ->withExternalIdentifier('external-home-with-consent')
            ->withEmail('with-consent@example.com')
            ->named('Hugues Gobet')
            ->havingAcceptedPrivacyPolicy()
            ->build();
        $userRepository->create($user);

        $client->loginUser(new SecurityUser($user->getId()));

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Hugues Gobet');
        self::assertSelectorExists('a[href$="/logout"]');
    }
}
