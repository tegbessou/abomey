<?php

declare(strict_types=1);

namespace App\Tests\E2e\Account;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tarot\Domain\Player\PlayerRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccountDeletionTest extends WebTestCase
{
    #[Test]
    public function postingTheDeleteFormPurgesTheUserAndCascadeDeletesItsPlayers(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);

        $userId = UserId::fromString('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee');
        $otherUserId = UserId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff');

        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-to-delete')
                ->withEmail('to-delete@example.com')
                ->named('To Delete')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($otherUserId)
                ->withExternalIdentifier('external-to-keep')
                ->withEmail('to-keep@example.com')
                ->named('To Keep')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );

        $aliceOfTarget = PlayerBuilder::aPlayer()
            ->withId('11111111-1111-4111-8111-aaaaaaaaaaaa')
            ->ownedBy($userId->toString())
            ->named('Alice')
            ->build();
        $bobOfTarget = PlayerBuilder::aPlayer()
            ->withId('22222222-2222-4222-8222-aaaaaaaaaaaa')
            ->ownedBy($userId->toString())
            ->named('Bob')
            ->build();
        $charlieOfOther = PlayerBuilder::aPlayer()
            ->withId('33333333-3333-4333-8333-bbbbbbbbbbbb')
            ->ownedBy($otherUserId->toString())
            ->named('Charlie')
            ->build();
        $playerRepository->create($aliceOfTarget);
        $playerRepository->create($bobOfTarget);
        $playerRepository->create($charlieOfOther);

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/account');
        $client->submitForm('Supprimer définitivement mon compte', [
            'account_form[confirmed]' => '1',
        ]);

        self::assertResponseRedirects();

        self::assertNull($userRepository->ofId($userId), 'The user should have been deleted.');
        self::assertNull(
            $playerRepository->ofId($aliceOfTarget->getId(), $userId->toString()),
            'Alice (target user) should have been cascade-deleted.',
        );
        self::assertNull(
            $playerRepository->ofId($bobOfTarget->getId(), $userId->toString()),
            'Bob (target user) should have been cascade-deleted.',
        );

        self::assertNotNull(
            $userRepository->ofId($otherUserId),
            'The other user must remain untouched.',
        );
        self::assertNotNull(
            $playerRepository->ofId($charlieOfOther->getId(), $otherUserId->toString()),
            'Charlie (other user) must remain untouched.',
        );
    }
}
