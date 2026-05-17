<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Application;

use App\Tarot\Application\CreatePlayer\CreatePlayerCommand;
use App\Tarot\Application\CreatePlayer\CreatePlayerCommandHandler;
use App\Tarot\Domain\Player\PlayerId;
use App\Tests\Fake\Tarot\InMemoryPlayerRepository;
use App\Tests\Stub\Tarot\StubPlayerIdGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreatePlayerCommandHandlerTest extends TestCase
{
    #[Test]
    public function itCreatesAPlayerForTheGivenOwnerAndReturnsItsId(): void
    {
        $expectedId = PlayerId::fromString('01966000-0000-7000-8000-000000000001');
        $playerRepository = new InMemoryPlayerRepository();
        $handler = new CreatePlayerCommandHandler($playerRepository, new StubPlayerIdGenerator($expectedId));

        $returnedId = $handler->handle(new CreatePlayerCommand(
            ownerId: 'owner-user-id',
            name: 'Alice',
        ));

        self::assertSame('01966000-0000-7000-8000-000000000001', $returnedId->toString());
        $savedPlayer = $playerRepository->ofId($returnedId, 'owner-user-id');
        self::assertNotNull($savedPlayer);
        self::assertSame('Alice', $savedPlayer->getName());
        self::assertSame('owner-user-id', $savedPlayer->getOwner());
    }

    #[Test]
    public function aPlayerIsNotVisibleToADifferentOwner(): void
    {
        $expectedId = PlayerId::fromString('01966000-0000-7000-8000-000000000002');
        $playerRepository = new InMemoryPlayerRepository();
        $handler = new CreatePlayerCommandHandler($playerRepository, new StubPlayerIdGenerator($expectedId));

        $returnedId = $handler->handle(new CreatePlayerCommand(
            ownerId: 'owner-user-id',
            name: 'Alice',
        ));

        $accessAttempt = $playerRepository->ofId($returnedId, 'another-user-id');

        self::assertNull($accessAttempt);
    }
}
