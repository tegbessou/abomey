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

final class RecordClassicDealWithPartnerTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserRecordsAFivePlayerDealAndDesignatesAPartner(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('aaaaaaaa-cccc-4ddd-8aaa-aaaaaaaaaaaa');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-partner')
                ->withEmail('record-partner@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David', 'Eve'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('part-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('bbbbbbbb-dddd-4eee-8bbb-bbbbbbbbbbbb');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée à 5')
                ->withMode(Mode::Five)
                ->withParticipants(['part-1', 'part-2', 'part-3', 'part-4', 'part-5'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        $client->waitForVisibility('h1');

        $crawler = $client->getCrawler();

        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[takerId]"][value="part-1"])')->click();

        // Une fois Alice désignée Preneur, son pill disparaît du choix Partenaire.
        $client->waitFor('label.ab-segmented__option[hidden]:has(input[name="record_classic_deal_form[partnerId]"][value="part-1"])');

        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[partnerId]"][value="part-2"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[contract]"][value="garde"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[bouts]"][value="1"])')->click();
        $crawler->filter('input[name="record_classic_deal_form[pointsScored]"]')->sendKeys('60');

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        // Alice (Preneur) +68, Bob (Partenaire) +34.
        self::assertSelectorTextContains('.game-scoreboard', '68');
        self::assertSelectorTextContains('.game-scoreboard', '34');
    }
}
