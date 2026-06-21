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
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;

final class RecordClassicDealWithPoigneeTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserAddsAPoigneeViaTheModalAndSeesItsBonusInTheCumulativeScore(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('cccccccc-aaaa-4bbb-8ccc-cccccccccccc');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-poignee')
                ->withEmail('record-poignee@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('poig-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('dddddddd-bbbb-4ccc-8ddd-dddddddddddd');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée avec Poignée')
                ->withMode(Mode::Four)
                ->withParticipants(['poig-1', 'poig-2', 'poig-3', 'poig-4'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        $client->waitForVisibility('h1');

        $crawler = $client->getCrawler();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[takerId]"][value="poig-1"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[contract]"][value="garde"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[bouts]"][value="1"])')->click();
        $crawler->filter('input[name="record_classic_deal_form[pointsScored]"]')->sendKeys('60');

        $crawler->filter('button.form-collection-add')->click();
        $client->waitFor('dialog[open]');

        $client->executeScript("
            const announcer = document.querySelector('dialog[open] [data-field-name=\"announcerId\"]');
            announcer.value = 'poig-2';
            announcer.dispatchEvent(new Event('change'));
            const size = document.querySelector('dialog[open] [data-field-name=\"size\"]');
            size.value = 'single';
            size.dispatchEvent(new Event('change'));
        ");

        $client->getCrawler()->filter('dialog[open] [data-action*="confirmAdd"]')->click();

        $driver = $client->getWebDriver();
        $driver->wait(5)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::cssSelector('dialog[open]'),
            ),
        );
        $driver->wait(5)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.form-collection-line'),
            ),
        );

        self::assertSelectorTextContains('.form-collection-line__label', 'Bob — Simple');

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        self::assertSelectorTextContains('.game-scoreboard', '162');
    }
}
