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

final class RecordClassicDealRejectsInvalidInputTest extends AbomeyPantherTestCase
{
    #[Test]
    public function anInvalidDealIsRejectedWithABusinessMessageNotAServerError(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('11111111-2222-4333-8444-555555555555');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-invalid-input')
                ->withEmail('invalid-input@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('inv-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('66666666-7777-4888-8999-aaaaaaaaaaaa');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée saisie invalide')
                ->withMode(Mode::Five)
                ->withParticipants(['inv-1', 'inv-2', 'inv-3', 'inv-4', 'inv-5', 'inv-6'])
                ->build(),
        );

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        $client->waitForVisibility('h1');

        $crawler = $client->getCrawler();

        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[takerId]"][value="inv-1"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[contract]"][value="garde"])')->click();
        $crawler->filter('label.ab-segmented__option:has(input[name="record_classic_deal_form[bouts]"][value="1"])')->click();
        $crawler->filter('input[name="record_classic_deal_form[pointsScored]"]')->sendKeys('60');

        $client->getCrawler()->filter('.deal-form button[type="submit"]')->click();

        // La page se re-render avec un message métier — pas une 500.
        $client->waitFor('.flash--error');
        self::assertSelectorTextContains('.flash--error', 'invalide');
    }
}
