<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\InvalidRankingException;
use App\Tarot\Domain\Game\Ranking;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankingTest extends TestCase
{
    #[Test]
    public function aRankingExposesThePositionOfEachPlayer(): void
    {
        $ranking = new Ranking(['p-1', 'p-2', 'p-3', 'p-4']);

        self::assertSame(1, $ranking->positionOf('p-1'));
        self::assertSame(2, $ranking->positionOf('p-2'));
        self::assertSame(3, $ranking->positionOf('p-3'));
        self::assertSame(4, $ranking->positionOf('p-4'));
    }

    #[Test]
    public function aRankingRejectsADuplicatePlayer(): void
    {
        $this->expectException(InvalidRankingException::class);

        new Ranking(['p-1', 'p-2', 'p-1']);
    }
}
