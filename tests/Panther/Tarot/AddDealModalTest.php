<?php

declare(strict_types=1);

namespace App\Tests\Panther\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Player\PlayerRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Panther\AbomeyPantherTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;

final class AddDealModalTest extends AbomeyPantherTestCase
{
    #[Test]
    public function theAddDealButtonOpensAModalOfferingBothDealTypes(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('aaaa1111-bbbb-4ccc-8ddd-eeee22223333');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-add-deal-modal')
                ->withEmail('add-deal-modal@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('p-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('44445555-6666-4777-8888-9999aaaabbbb');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée modale')
                ->withMode(Mode::Four)
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString());
        $client->waitForVisibility('.deal-actions button');

        self::assertSelectorIsNotVisible('.deal-choices');

        $client->getCrawler()->filter('.deal-actions button[data-action="dialog#open"]')->click();

        $client->waitForVisibility('.deal-choices');
        self::assertSelectorTextContains('.deal-choices', 'Donne classique');
        self::assertSelectorTextContains('.deal-choices', 'Vachette');
    }
}
