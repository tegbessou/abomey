<?php

declare(strict_types=1);

namespace App\Tests\Panther\Tarot;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserRepository;
use App\Tests\Builder\Account\UserBuilder;
use App\Tests\Panther\AbomeyPantherTestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;

final class CreateGameTest extends AbomeyPantherTestCase
{
    #[Test]
    public function aConnectedUserCreatesAGameByAddingPlayersOnTheFly(): void
    {
        $container = self::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

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

        $client = static::createPantherClient();
        $authPayload = json_encode(['id' => $userId->toString(), 'ctx' => []], \JSON_THROW_ON_ERROR);

        $client->request('GET', '/');
        $client->getCookieJar()->set(new Cookie('AUTH', $authPayload, null, '/'));

        $client->request('GET', '/games/new');
        $client->waitForVisibility('h1');

        self::assertSelectorTextContains('h1', 'Créer une Partie');

        foreach (['Alice', 'Bob', 'Charlie', 'David'] as $playerName) {
            $this->addPlayerThroughModal($client, $playerName);
        }

        $client->getCrawler()->filter('input[name="create_game_form[name]"]')->sendKeys('Soirée chez Paul');
        $client->getCrawler()->filter('input[name="create_game_form[mode]"][value="4"]')->click();

        $client->getCrawler()->filter('.game-form button[type="submit"]')->click();

        $client->waitForVisibility('.participant-chip');

        self::assertSelectorTextContains('h1', 'Soirée chez Paul');
        self::assertSelectorTextContains('.ab-badge', 'Tarot à 4');
        self::assertSelectorTextContains('.participant-list', 'Alice');
        self::assertSelectorTextContains('.participant-list', 'Bob');
        self::assertSelectorTextContains('.participant-list', 'Charlie');
        self::assertSelectorTextContains('.participant-list', 'David');
    }

    private function addPlayerThroughModal(Client $client, string $playerName): void
    {
        $client->getCrawler()->filter('button.participant-add')->click();

        $client->waitFor('dialog[open]');

        $input = $client->getCrawler()->filter('#create_player_form_name');
        $input->sendKeys($playerName);

        $client->getCrawler()->filter('.modal-form button[type="submit"]')->click();

        $driver = $client->getWebDriver();
        $driver->wait(5)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::cssSelector('dialog[open]'),
            ),
        );

        $driver->wait(5)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath(
                    \sprintf('//label[contains(@class, "participant-option")]//span[normalize-space(text())="%s"]', $playerName),
                ),
            ),
        );
    }
}
