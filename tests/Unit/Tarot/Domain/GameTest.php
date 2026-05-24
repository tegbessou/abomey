<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\DuplicateParticipantsException;
use App\Tarot\Domain\Game\EmptyGameNameException;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\TooFewParticipantsException;
use App\Tarot\Domain\Game\TooManyParticipantsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    private const string CREATED_AT_FIXTURE = '2026-05-24 12:00:00';

    #[Test]
    public function aGameCanBeCreatedWithAValidNameModeAndParticipants(): void
    {
        $id = GameId::fromString('01966000-0000-7000-8000-000000000001');

        $game = Game::create(
            $id,
            'owner-user-id',
            'Soirée chez Paul',
            Mode::Four,
            ['p-1', 'p-2', 'p-3', 'p-4'],
            self::aCreatedAt(),
        );

        self::assertSame($id, $game->getId());
        self::assertSame('owner-user-id', $game->getOwner());
        self::assertSame('Soirée chez Paul', $game->getName());
        self::assertSame(Mode::Four, $game->getMode());
        self::assertSame(['p-1', 'p-2', 'p-3', 'p-4'], $game->getParticipantIds());
    }

    #[Test]
    #[DataProvider('emptyOrWhitespaceNames')]
    public function aGameCannotBeCreatedWithAnEmptyOrWhitespaceOnlyName(string $invalidName): void
    {
        $this->expectException(EmptyGameNameException::class);

        Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000002'),
            'owner-user-id',
            $invalidName,
            Mode::Four,
            ['p-1', 'p-2', 'p-3', 'p-4'],
            self::aCreatedAt(),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyOrWhitespaceNames(): iterable
    {
        yield 'empty string' => [''];
        yield 'single space' => [' '];
        yield 'multiple spaces' => ['   '];
        yield 'tab and newline' => ["\t\n"];
    }

    #[Test]
    public function aGameNameIsTrimmedAtCreation(): void
    {
        $game = Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000003'),
            'owner-user-id',
            '  Soirée du 15 mai  ',
            Mode::Four,
            ['p-1', 'p-2', 'p-3', 'p-4'],
            self::aCreatedAt(),
        );

        self::assertSame('Soirée du 15 mai', $game->getName());
    }

    #[Test]
    public function aGameCannotHaveDuplicateParticipants(): void
    {
        $this->expectException(DuplicateParticipantsException::class);

        Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000004'),
            'owner-user-id',
            'Soirée',
            Mode::Four,
            ['p-1', 'p-2', 'p-1', 'p-3'],
            self::aCreatedAt(),
        );
    }

    #[Test]
    #[DataProvider('tooFewParticipantsCombinations')]
    public function aGameCannotBeCreatedWithFewerParticipantsThanTheMode(
        Mode $mode,
        int $participantsCount,
    ): void {
        $participants = array_map(
            static fn (int $index): string => 'p-'.$index,
            range(1, $participantsCount),
        );

        $this->expectException(TooFewParticipantsException::class);

        Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000005'),
            'owner-user-id',
            'Soirée',
            $mode,
            $participants,
            self::aCreatedAt(),
        );
    }

    /**
     * @return iterable<string, array{Mode, int}>
     */
    public static function tooFewParticipantsCombinations(): iterable
    {
        yield 'Three with 2' => [Mode::Three, 2];
        yield 'Four with 3' => [Mode::Four, 3];
        yield 'Five with 4' => [Mode::Five, 4];
    }

    #[Test]
    #[DataProvider('tooManyParticipantsCombinations')]
    public function aGameCannotBeCreatedWithMoreThanModePlusTwoParticipants(
        Mode $mode,
        int $participantsCount,
    ): void {
        $participants = array_map(
            static fn (int $index): string => 'p-'.$index,
            range(1, $participantsCount),
        );

        $this->expectException(TooManyParticipantsException::class);

        Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000006'),
            'owner-user-id',
            'Soirée',
            $mode,
            $participants,
            self::aCreatedAt(),
        );
    }

    /**
     * @return iterable<string, array{Mode, int}>
     */
    public static function tooManyParticipantsCombinations(): iterable
    {
        yield 'Three with 6' => [Mode::Three, 6];
        yield 'Four with 7' => [Mode::Four, 7];
        yield 'Five with 8' => [Mode::Five, 8];
    }

    #[Test]
    #[DataProvider('boundaryParticipantsCombinations')]
    public function aGameCanBeCreatedAtBothBoundariesOfTheValidParticipantsRange(
        Mode $mode,
        int $participantsCount,
    ): void {
        $participants = array_map(
            static fn (int $index): string => 'p-'.$index,
            range(1, $participantsCount),
        );

        $game = Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000007'),
            'owner-user-id',
            'Soirée',
            $mode,
            $participants,
            self::aCreatedAt(),
        );

        self::assertCount($participantsCount, $game->getParticipantIds());
    }

    /**
     * @return iterable<string, array{Mode, int}>
     */
    public static function boundaryParticipantsCombinations(): iterable
    {
        yield 'Three at min (3)' => [Mode::Three, 3];
        yield 'Three at max (5)' => [Mode::Three, 5];
        yield 'Four at min (4)' => [Mode::Four, 4];
        yield 'Four at max (6)' => [Mode::Four, 6];
        yield 'Five at min (5)' => [Mode::Five, 5];
        yield 'Five at max (7)' => [Mode::Five, 7];
    }

    #[Test]
    public function aGameRemembersItsCreationDate(): void
    {
        $createdAt = new \DateTimeImmutable('2026-05-24 18:30:00');

        $game = Game::create(
            GameId::fromString('01966000-0000-7000-8000-000000000008'),
            'owner-user-id',
            'Soirée chez Paul',
            Mode::Four,
            ['p-1', 'p-2', 'p-3', 'p-4'],
            $createdAt,
        );

        self::assertSame($createdAt, $game->getCreatedAt());
    }

    private static function aCreatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::CREATED_AT_FIXTURE);
    }
}
