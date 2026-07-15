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

final class DealTakerExcludesDeadPlayersTest extends AbomeyPantherTestCase
{
    #[Test]
    public function designatingADeadPlayerRemovesItFromTheTakerChoices(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('11112222-3333-4444-8555-666677778888');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-taker-excludes-dead')
                ->withEmail('taker-excludes-dead@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David', 'Eve'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('excl-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('99998888-7777-4666-8555-444433332222');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Tablée avec un Mort')
                ->withMode(Mode::Four)
                ->withParticipants(['excl-1', 'excl-2', 'excl-3', 'excl-4', 'excl-5'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        $client->waitForVisibility('h1');

        $takerChoice = 'label.ab-segmented__option:has(input[name="record_classic_deal_form[takerId]"][value="excl-5"])';

        self::assertSelectorIsVisible($takerChoice);

        $client->getCrawler()
            ->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[deadPlayerIds][]"][value="excl-5"])')
            ->click();

        $client->waitForInvisibility($takerChoice);
        self::assertSelectorIsNotVisible($takerChoice);
    }
}
