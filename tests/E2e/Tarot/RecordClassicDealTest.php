<?php

declare(strict_types=1);

namespace App\Tests\E2e\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Account\Infrastructure\Security\SecurityUser;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Player\PlayerRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Builder\Tarot\GameBuilder;
use App\Tests\Builder\Tarot\PlayerBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecordClassicDealTest extends WebTestCase
{
    #[Test]
    public function aClassicDealCanBeRecordedAndAppearsInTheDealsTable(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);

        $userId = UserId::fromString('aaaaaaaa-bbbb-4ccc-8ddd-aaaaaaaaaaaa');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-deal')
                ->withEmail('record-deal@example.com')
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
        $gameId = GameId::fromString('bbbbbbbb-cccc-4ddd-8eee-bbbbbbbbbbbb');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée test')
                ->withMode(Mode::Four)
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $crawler = $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('record_classic_deal_form[submit]')->form([
            'record_classic_deal_form[takerId]' => 'p-1',
            'record_classic_deal_form[contract]' => 'garde',
            'record_classic_deal_form[bouts]' => '1',
            'record_classic_deal_form[pointsScored]' => '60',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/games/'.$gameId->toString());

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.ab-donne-card');
        self::assertSelectorTextContains('.game-scoreboard', '102');
        self::assertSelectorTextContains('.ab-donnes-matrix', '102');
    }

    #[Test]
    public function aClassicDealWithPetitAuBoutOnTakerSideAddsTheBonusToTheTakerScore(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $container->get(PlayerRepository::class);

        $userId = UserId::fromString('eeeeeeee-ffff-4aaa-8bbb-eeeeeeeeeeee');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-pab-taker')
                ->withEmail('pab-taker@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $index => $name) {
            $playerRepository->create(
                PlayerBuilder::aPlayer()
                    ->withId('pab-'.($index + 1))
                    ->ownedBy($userId->toString())
                    ->named($name)
                    ->build(),
            );
        }
        $gameId = GameId::fromString('ffffffff-aaaa-4bbb-8ccc-ffffffffffff');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Soirée PAB')
                ->withMode(Mode::Four)
                ->withParticipants(['pab-1', 'pab-2', 'pab-3', 'pab-4'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $crawler = $client->request('GET', '/games/'.$gameId->toString().'/deals/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('record_classic_deal_form[submit]')->form([
            'record_classic_deal_form[takerId]' => 'pab-1',
            'record_classic_deal_form[contract]' => 'garde',
            'record_classic_deal_form[bouts]' => '1',
            'record_classic_deal_form[pointsScored]' => '60',
            'record_classic_deal_form[petitAuBout]' => 'taker',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/games/'.$gameId->toString());
        $client->followRedirect();
        self::assertSelectorExists('.ab-donne-card');
        self::assertSelectorTextContains('.game-scoreboard', '132');
        self::assertSelectorTextContains('.ab-donnes-matrix', '132');
    }

    #[Test]
    public function recordingADealIsForbiddenWhenTheTableExceedsTheMode(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var GameRepository $gameRepository */
        $gameRepository = $container->get(GameRepository::class);

        $userId = UserId::fromString('cccccccc-dddd-4eee-8fff-cccccccccccc');
        $userRepository->create(
            UserBuilder::aUser()
                ->withId($userId)
                ->withExternalIdentifier('external-record-deal-locked')
                ->withEmail('record-deal-locked@example.com')
                ->named('Tester')
                ->havingAcceptedPrivacyPolicy()
                ->build(),
        );
        $gameId = GameId::fromString('dddddddd-eeee-4fff-8aaa-dddddddddddd');
        $gameRepository->create(
            GameBuilder::aGame()
                ->withId($gameId)
                ->ownedBy($userId->toString())
                ->named('Tablée variable')
                ->withMode(Mode::Four)
                ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
                ->build(),
        );

        $client->loginUser(new SecurityUser($userId));

        $client->request('GET', '/games/'.$gameId->toString().'/deals/new');

        self::assertResponseStatusCodeSame(404);
    }
}
