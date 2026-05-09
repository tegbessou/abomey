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
    public function itCreatesAPlayerSavesItAndReturnsItsId(): void
    {
        $expectedId = PlayerId::fromString('01966000-0000-7000-8000-000000000001');
        $repository = new InMemoryPlayerRepository();
        $handler = new CreatePlayerCommandHandler($repository, new StubPlayerIdGenerator($expectedId));

        $returnedId = $handler->handle(new CreatePlayerCommand('Alice'));

        self::assertSame('01966000-0000-7000-8000-000000000001', $returnedId->toString());
        $savedPlayer = $repository->ofId($returnedId);
        self::assertNotNull($savedPlayer);
        self::assertSame('Alice', $savedPlayer->getName());
    }
}
