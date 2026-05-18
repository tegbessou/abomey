<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Player\EmptyPlayerNameException;
use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlayerTest extends TestCase
{
    #[Test]
    public function aPlayerCanBeCreatedWithAnOwnerAndAValidName(): void
    {
        $player = Player::create(
            PlayerId::fromString('01966000-0000-7000-8000-000000000001'),
            'owner-user-id',
            'Alice',
        );

        self::assertSame('owner-user-id', $player->getOwner());
        self::assertSame('Alice', $player->getName());
    }

    #[Test]
    public function aPlayerCannotBeCreatedWithAnEmptyName(): void
    {
        $this->expectException(EmptyPlayerNameException::class);

        Player::create(
            PlayerId::fromString('01966000-0000-7000-8000-000000000001'),
            'owner-user-id',
            '',
        );
    }

    #[Test]
    public function aPlayerCannotBeCreatedWithAWhitespaceOnlyName(): void
    {
        $this->expectException(EmptyPlayerNameException::class);

        Player::create(
            PlayerId::fromString('01966000-0000-7000-8000-000000000001'),
            'owner-user-id',
            '   ',
        );
    }
}
