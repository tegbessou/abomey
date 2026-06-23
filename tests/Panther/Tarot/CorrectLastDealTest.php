<?php

declare(strict_types=1);

namespace App\Tests\Panther\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Domain\Player\PlayerRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Panther\AbomeyPantherTestCase;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;

final class CorrectLastDealTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserCorrectsThePointsOfTheLastClassicDeal(): void
    {
        $gameId = $this->aGameWithAClassicDeal('aaaaaaaa-0000-4000-8000-000000000001', 'corr-classic');

        $client = $this->authenticatedClient('corr-classic');

        $client->request('GET', '/games/'.$gameId.'/last-deal/edit');
        $client->waitForVisibility('h1');

        $points = $client->getWebDriver()->findElement(WebDriverBy::name('record_classic_deal_form[pointsScored]'));
        $points->clear();
        $points->sendKeys('50');

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        self::assertSelectorTextContains('.game-scoreboard', '-78');
    }

    #[Test]
    public function aConnectedUserSwitchesTheLastDealFromClassicToVachette(): void
    {
        $gameId = $this->aGameWithAClassicDeal('bbbbbbbb-0000-4000-8000-000000000002', 'corr-switch');

        $client = $this->authenticatedClient('corr-switch');

        $client->request('GET', '/games/'.$gameId.'/last-deal/edit');
        $client->waitForVisibility('h1');

        $client->getCrawler()->filter('a[href*="as=vachette"]')->click();
        $client->waitForVisibility('select[name="record_vachette_form[ranking][0]"]');

        $crawler = $client->getCrawler();
        $crawler->filter('select[name="record_vachette_form[ranking][0]"] option[value="p-1"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][1]"] option[value="p-2"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][2]"] option[value="p-3"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][3]"] option[value="p-4"]')->click();

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        self::assertSelectorTextContains('.game-scoreboard', '120');
    }

    #[Test]
    public function theEditButtonIsHiddenWhenTheGameHasNoDeal(): void
    {
        $gameId = $this->aGameWithoutDeal('cccccccc-0000-4000-8000-000000000003', 'corr-empty');

        $client = $this->authenticatedClient('corr-empty');

        $client->request('GET', '/games/'.$gameId);
        $client->waitForVisibility('h1');

        self::assertSelectorNotExists('a[href*="last-deal/edit"]');
    }

    private function aGameWithAClassicDeal(string $gameUuid, string $slug): string
    {
        $game = $this->buildGame($gameUuid, $slug);
        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );
        /** @var GameRepository $gameRepository */
        $gameRepository = self::getContainer()->get(GameRepository::class);
        $gameRepository->create($game);

        return $gameUuid;
    }

    private function aGameWithoutDeal(string $gameUuid, string $slug): string
    {
        $game = $this->buildGame($gameUuid, $slug);
        /** @var GameRepository $gameRepository */
        $gameRepository = self::getContainer()->get(GameRepository::class);
        $gameRepository->create($game);

        return $gameUuid;
    }

    private function buildGame(string $gameUuid, string $slug): Game
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);

        $userId = UserId::fromString('99999999-0000-4000-8000-'.substr(md5($slug), 0, 12));
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-'.$slug)
                ->withEmail($slug.'@example.com')
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

        return GameBuilder::aGame()
            ->withId(GameId::fromString($gameUuid))
            ->ownedBy($userId->toString())
            ->named('Soirée correction')
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();
    }

    private function authenticatedClient(string $slug): \Symfony\Component\Panther\Client
    {
        $userId = UserId::fromString('99999999-0000-4000-8000-'.substr(md5($slug), 0, 12));

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        return $client;
    }
}
