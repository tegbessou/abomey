<?php

declare(strict_types=1);

namespace App\Tests\Panther\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Tarot\Domain\Player\PlayerRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Panther\AbomeyPantherTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;

final class CreateGameTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserCanCreateAGameWithExistingPlayers(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);

        $userId = UserId::fromString('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-create-game')
                ->withEmail('create-game@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );

        $alice = PlayerBuilder::aPlayer()->withId('11111111-1111-4111-8111-aaaaaaaaaaaa')->ownedBy($userId->toString())->named('Alice')->build();
        $bob = PlayerBuilder::aPlayer()->withId('22222222-2222-4222-8222-aaaaaaaaaaaa')->ownedBy($userId->toString())->named('Bob')->build();
        $charlie = PlayerBuilder::aPlayer()->withId('33333333-3333-4333-8333-aaaaaaaaaaaa')->ownedBy($userId->toString())->named('Charlie')->build();
        $david = PlayerBuilder::aPlayer()->withId('44444444-4444-4444-8444-aaaaaaaaaaaa')->ownedBy($userId->toString())->named('David')->build();
        $playerRepository->create($alice);
        $playerRepository->create($bob);
        $playerRepository->create($charlie);
        $playerRepository->create($david);

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/new');
        $client->waitForVisibility('h1');

        self::assertSelectorTextContains('h1', 'Créer une Partie');

        $client->submitForm('Créer la Partie', [
            'create_game_form[name]' => 'Soirée chez Paul',
            'create_game_form[mode]' => '4',
            'create_game_form[participants]' => [
                $alice->getId()->toString(),
                $bob->getId()->toString(),
                $charlie->getId()->toString(),
                $david->getId()->toString(),
            ],
        ]);

        $client->waitForVisibility('.participant-chip');

        self::assertSelectorTextContains('h1', 'Soirée chez Paul');
        self::assertSelectorTextContains('.game-mode-badge', 'TAROT À 4');
        self::assertSelectorTextContains('.participant-list', 'Alice');
        self::assertSelectorTextContains('.participant-list', 'Bob');
    }
}
