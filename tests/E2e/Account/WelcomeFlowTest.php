<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tests\Builder\Account\UserBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WelcomeFlowTest extends WebTestCase
{
    #[Test]
    public function theWelcomePageRedirectsAnonymousVisitorsToTheHome(): void
    {
        $client = static::createClient();

        $client->request('GET', '/welcome');

        self::assertResponseRedirects('/');
    }

    #[Test]
    public function theWelcomePageShowsTheConsentFormToAuthenticatedUsersWithoutConsent(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $user = UserBuilder::aUser()
            ->withId('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
            ->withExternalIdentifier('external-welcome-form')
            ->withEmail('welcome-form@example.com')
            ->named('Welcome User')
            ->build();
        $userRepository->create($user);

        $client->loginUser(new SecurityUser($user->getId()));

        $client->request('GET', '/welcome');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[method="post"]');
        self::assertSelectorExists('input[name="welcome_form[accepted]"]');
        self::assertSelectorExists('input[type="checkbox"][required]');
    }

    #[Test]
    public function submittingTheWelcomeFormRecordsTheConsentAndRedirectsHome(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $userId = UserId::fromString('cccccccc-cccc-4ccc-8ccc-cccccccccccc');
        $user = UserBuilder::aUser()
            ->withId($userId)
            ->withExternalIdentifier('external-welcome-submit')
            ->withEmail('welcome-submit@example.com')
            ->named('Submit User')
            ->build();
        $userRepository->create($user);

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/welcome');
        $client->submitForm('Accepter et continuer', [
            'welcome_form[accepted]' => '1',
        ]);

        self::assertResponseRedirects('/');

        $reloaded = $userRepository->ofId($userId);
        self::assertNotNull($reloaded);
        $consent = $reloaded->getPrivacyConsent();
        self::assertNotNull($consent);
        self::assertSame(PrivacyPolicyVersion::V2026_05_15, $consent->version);
    }
}
