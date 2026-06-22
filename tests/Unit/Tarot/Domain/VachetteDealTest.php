<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\Ranking;
use App\Tarot\Domain\Game\VachetteDeal;
use App\Tests\Builder\Tarot\GameBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VachetteDealTest extends TestCase
{
    #[Test]
    public function aVachetteAtFourPlayersAppliesTheScale(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();

        $deal = VachetteDeal::create(
            game: $game,
            position: 1,
            ranking: new Ranking(['p-1', 'p-2', 'p-3', 'p-4']),
        );

        $scores = $deal->pointsByPlayer();
        self::assertSame(120, $scores['p-1']);
        self::assertSame(60, $scores['p-2']);
        self::assertSame(-60, $scores['p-3']);
        self::assertSame(-120, $scores['p-4']);
    }

    #[Test]
    public function aVachetteAtThreePlayersAppliesTheScale(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Three)
            ->withParticipants(['p-1', 'p-2', 'p-3'])
            ->build();

        $deal = VachetteDeal::create(
            game: $game,
            position: 1,
            ranking: new Ranking(['p-1', 'p-2', 'p-3']),
        );

        $scores = $deal->pointsByPlayer();
        self::assertSame(120, $scores['p-1']);
        self::assertSame(0, $scores['p-2']);
        self::assertSame(-120, $scores['p-3']);
    }

    #[Test]
    public function aVachetteAtFivePlayersAppliesTheScale(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Five)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $deal = VachetteDeal::create(
            game: $game,
            position: 1,
            ranking: new Ranking(['p-1', 'p-2', 'p-3', 'p-4', 'p-5']),
        );

        $scores = $deal->pointsByPlayer();
        self::assertSame(120, $scores['p-1']);
        self::assertSame(60, $scores['p-2']);
        self::assertSame(0, $scores['p-3']);
        self::assertSame(-60, $scores['p-4']);
        self::assertSame(-120, $scores['p-5']);
    }
}
