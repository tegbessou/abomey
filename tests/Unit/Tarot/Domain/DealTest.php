<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\Deal;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\PointsScoredOutOfRangeException;
use App\Tarot\Domain\Game\TakerNotActiveException;
use App\Tests\Builder\Tarot\GameBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DealTest extends TestCase
{
    #[Test]
    #[DataProvider('classicDealScenariosAtFourPlayers')]
    public function aClassicDealAttributesPointsAccordingToFFT(
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
        int $takerPoints,
        int $defenderPoints,
    ): void {
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            takerId: 'alice',
            contract: $contract,
            bouts: $bouts,
            pointsScored: $pointsScored,
        );

        self::assertSame(
            [
                'alice' => $takerPoints,
                'bob' => $defenderPoints,
                'charlie' => $defenderPoints,
                'david' => $defenderPoints,
            ],
            $deal->pointsByPlayer(),
        );
    }

    #[Test]
    public function aClassicDealCannotBeCreatedWithATakerOutsideOfActivePlayers(): void
    {
        $this->expectException(TakerNotActiveException::class);

        Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            takerId: 'eve',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
        );
    }

    #[Test]
    #[DataProvider('outOfRangePointsScored')]
    public function aClassicDealCannotBeCreatedWithPointsScoredOutsideZeroToNinetyOne(int $invalid): void
    {
        $this->expectException(PointsScoredOutOfRangeException::class);

        Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: $invalid,
        );
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function outOfRangePointsScored(): iterable
    {
        yield 'negative' => [-1];
        yield 'above 91' => [92];
    }

    /**
     * @return iterable<string, array{Contract, Bouts, int, int, int}>
     */
    public static function classicDealScenariosAtFourPlayers(): iterable
    {
        yield 'Garde, 1 bout, taker scores 60 (wins)' => [
            Contract::Garde, Bouts::One, 60, +102, -34,
        ];
        yield 'Garde Sans, 0 bouts, taker scores 50 (loses)' => [
            Contract::GardeSans, Bouts::Zero, 50, -186, +62,
        ];
        yield 'Garde, 3 bouts, taker scores exactly the target (wins)' => [
            Contract::Garde, Bouts::Three, 36, +75, -25,
        ];
        yield 'Garde Contre, 2 bouts, taker scores 45 (wins largely)' => [
            Contract::GardeContre, Bouts::Two, 45, +348, -116,
        ];
    }

    private static function aGameWithAliceBobCharlieDavid(): Game
    {
        return GameBuilder::aGame()
            ->withParticipants(['alice', 'bob', 'charlie', 'david'])
            ->build();
    }
}
