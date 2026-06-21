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

final class RecordClassicDealAtThreeTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserRecordsAThreePlayerDealWithoutPartnerStep(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('cccccccc-eeee-4fff-8ccc-cccccccccccc');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-three')
                ->withEmail('record-three@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('three-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('dddddddd-ffff-4aaa-8ddd-dddddddddddd');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée à 3')
                ->withMode(Mode::Three)
                ->withParticipants(['three-1', 'three-2', 'three-3'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        $client->waitForVisibility('h1');

        $crawler = $client->getCrawler();

        // À 3 joueurs : aucune étape Partenaire, aucun choix de Mort.
        self::assertSelectorNotExists('input[name="record_classic_deal_form[partnerId]"]');
        self::assertSelectorNotExists('input[name="record_classic_deal_form[deadPlayerIds][]"]');

        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[takerId]"][value="three-1"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[contract]"][value="garde"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[bouts]"][value="1"])')->click();
        $crawler->filter('input[name="record_classic_deal_form[pointsScored]"]')->sendKeys('60');

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        // Alice (Preneur) +68, Bob et Charlie −34.
        self::assertSelectorTextContains('.game-scoreboard', '68');
        self::assertSelectorTextContains('.game-scoreboard', '-34');
    }
}
