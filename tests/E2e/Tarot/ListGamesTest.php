<?php

declare(strict_types=1);

namespace App\Tests\E2e\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\GameBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListGamesTest extends WebTestCase
{
    #[Test]
    public function itShowsAnEmptyStateWhenTheUserHasNoGame(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $userId = UserId::fromString('11111111-1111-4111-8111-aaaaaaaaaaaa');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-empty-games')
                ->withEmail('empty-games@example.com')
                ->named('No Games')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/games');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mes Parties');
        self::assertSelectorTextContains('.empty-state', 'Vous n\'avez pas encore créé de Partie.');
    }

    #[Test]
    public function itShowsTheGamesOfTheUserAsCards(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('22222222-2222-4222-8222-aaaaaaaaaaaa');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-with-games')
                ->withEmail('with-games@example.com')
                ->named('Has Games')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId('33333333-3333-4333-8333-aaaaaaaaaaaa')
                ->ownedBy($userId->toString())
                ->named('Soirée chez Paul')
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/games');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.ab-game-card__title', 'Soirée chez Paul');
        self::assertSelectorTextContains('.ab-badge', 'Tarot à 4');
    }

    #[Test]
    public function itHidesGamesOfOtherUsers(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $ownUserId = UserId::fromString('44444444-4444-4444-8444-aaaaaaaaaaaa');
        $otherUserId = UserId::fromString('55555555-5555-4555-8555-aaaaaaaaaaaa');
        $userRepository->create(UserBuilder::aUser()->withId($ownUserId)->withExternalIdentifier('ext-own-list')->withEmail('own@example.com')->named('Own')->havingAcceptedPrivacyPolicy()->build());
        $userRepository->create(UserBuilder::aUser()->withId($otherUserId)->withExternalIdentifier('ext-other-list')->withEmail('other@example.com')->named('Other')->havingAcceptedPrivacyPolicy()->build());

        $gameRepository->create(
            GameBuilder::aGame()
                ->withId('66666666-6666-4666-8666-aaaaaaaaaaaa')
                ->ownedBy($otherUserId->toString())
                ->named('Partie privée de Other')
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($ownUserId));

        $client->request('GET', '/games');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('body', 'Partie privée de Other');
    }

    #[Test]
    public function itReturnsNotFoundWhenAccessingAGameOwnedByAnotherUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $ownUserId = UserId::fromString('77777777-7777-4777-8777-aaaaaaaaaaaa');
        $otherUserId = UserId::fromString('88888888-8888-4888-8888-aaaaaaaaaaaa');
        $userRepository->create(UserBuilder::aUser()->withId($ownUserId)->withExternalIdentifier('ext-own-detail')->withEmail('own-detail@example.com')->named('Own')->havingAcceptedPrivacyPolicy()->build());
        $userRepository->create(UserBuilder::aUser()->withId($otherUserId)->withExternalIdentifier('ext-other-detail')->withEmail('other-detail@example.com')->named('Other')->havingAcceptedPrivacyPolicy()->build());

        $foreignGameId = GameId::fromString('99999999-9999-4999-8999-aaaaaaaaaaaa');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($foreignGameId)
                ->ownedBy($otherUserId->toString())
                ->named('Partie d\'un autre')
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($ownUserId));

        $client->request('GET', '/games/'.$foreignGameId->toString());

        self::assertResponseStatusCodeSame(404);
    }
}
