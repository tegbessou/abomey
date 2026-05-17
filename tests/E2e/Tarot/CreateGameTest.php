<?php

declare(strict_types=1);

namespace App\Tests\E2e\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tests\Builder\Account\UserBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateGameTest extends WebTestCase
{
    #[Test]
    public function theFormShowsAnEmptyStateWhenTheUserHasNoPlayers(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $userId = UserId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-empty-state')
                ->withEmail('empty@example.com')
                ->named('No Players')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/games/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.empty-state', 'pas encore de Joueurs');
    }
}
