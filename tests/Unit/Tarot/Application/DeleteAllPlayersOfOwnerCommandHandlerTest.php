<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\DeleteAllPlayersOfOwner\DeleteAllPlayersOfOwnerCommand;
use App\Tarot\Application\DeleteAllPlayersOfOwner\DeleteAllPlayersOfOwnerCommandHandler;
use App\Tarot\Domain\Player\PlayerId;
use App\Tests\Builder\Tarot\PlayerBuilder;
use App\Tests\Fake\Tarot\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteAllPlayersOfOwnerCommandHandlerTest extends TestCase
{
    #[Test]
    public function itDeletesAllPlayersOfTheGivenOwnerLeavingOthersUntouched(): void
    {
        $playerRepository = new InMemoryPlayerRepository();
        $aliceFromA = PlayerBuilder::aPlayer()
            ->withId(PlayerId::fromString('01966000-0000-7000-8000-000000000001'))
            ->ownedBy('user-a')
            ->named('Alice')
            ->build();
        $bobFromA = PlayerBuilder::aPlayer()
            ->withId(PlayerId::fromString('01966000-0000-7000-8000-000000000002'))
            ->ownedBy('user-a')
            ->named('Bob')
            ->build();
        $charlieFromB = PlayerBuilder::aPlayer()
            ->withId(PlayerId::fromString('01966000-0000-7000-8000-000000000003'))
            ->ownedBy('user-b')
            ->named('Charlie')
            ->build();
        $playerRepository->create($aliceFromA);
        $playerRepository->create($bobFromA);
        $playerRepository->create($charlieFromB);

        $handler = new DeleteAllPlayersOfOwnerCommandHandler($playerRepository);

        $handler->handle(new DeleteAllPlayersOfOwnerCommand(ownerId: 'user-a'));

        self::assertNull($playerRepository->ofId($aliceFromA->getId(), 'user-a'));
        self::assertNull($playerRepository->ofId($bobFromA->getId(), 'user-a'));
        self::assertNotNull($playerRepository->ofId($charlieFromB->getId(), 'user-b'));
    }
}
