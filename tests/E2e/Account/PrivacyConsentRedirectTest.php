<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tests\Builder\Account\UserBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PrivacyConsentRedirectTest extends WebTestCase
{
    #[Test]
    public function anAuthenticatedUserWithoutConsentIsRedirectedToWelcome(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $userId = UserId::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd');
        $user = UserBuilder::aUser()
            ->withId($userId)
            ->withExternalIdentifier('external-no-consent')
            ->withEmail('no-consent@example.com')
            ->named('No Consent User')
            ->build();
        $userRepository->create($user);

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/games');

        self::assertResponseRedirects('/welcome');
    }
}
