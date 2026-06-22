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

final class RecordVachetteTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserRecordsAVachetteByRankingThePlayers(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('22222222-3333-4444-8555-666666666666');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-vachette')
                ->withEmail('record-vachette@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('vac-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('77777777-8888-4999-8aaa-bbbbbbbbbbbb');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée Vachette')
                ->withMode(Mode::Four)
                ->withParticipants(['vac-1', 'vac-2', 'vac-3', 'vac-4'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/vachettes/new');
        $client->waitForVisibility('h1');

        $crawler = $client->getCrawler();
        $crawler->filter('select[name="record_vachette_form[ranking][0]"] option[value="vac-1"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][1]"] option[value="vac-2"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][2]"] option[value="vac-3"]')->click();
        $crawler->filter('select[name="record_vachette_form[ranking][3]"] option[value="vac-4"]')->click();

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();
        $client->waitFor('.game-scoreboard');

        self::assertSelectorTextContains('.game-scoreboard', '120');
        self::assertSelectorTextContains('.game-scoreboard', '-120');
    }
}
