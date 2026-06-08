<?php

declare(strict_types=1);

namespace App\Tests\Integration\UI\Component;

use App\Tarot\Application\ListMyGames\GameSummaryView;
use App\Tarot\Application\Shared\ParticipantSummaryView;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

final class GameCardTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    #[Test]
    public function itShowsRankedScoresAndTheDealCountWhenDealsHaveBeenPlayed(): void
    {
        $rendered = $this->renderTwigComponent('game_card', [
            'game' => new GameSummaryView(
                id: '01966000-0000-7000-8000-0000000000aa',
                name: 'Soirée chez Paul',
                mode: 4,
                dealCount: 3,
                participants: [
                    new ParticipantSummaryView('p-1', 'Alice', 133),
                    new ParticipantSummaryView('p-2', 'Bob', -127),
                ],
                standings: [
                    new ParticipantSummaryView('p-1', 'Alice', 133),
                    new ParticipantSummaryView('p-2', 'Bob', -127),
                ],
            ),
        ]);

        $html = (string) $rendered;
        self::assertStringContainsString('Soirée chez Paul', $html);
        self::assertStringContainsString('Tarot à 4', $html);
        self::assertStringContainsString('3 Donnes jouées', $html);

        $rows = $rendered->crawler()->filter('.ab-score-row');
        self::assertCount(2, $rows);
        self::assertStringContainsString('Alice', $rows->first()->text());

        $emphasised = $rendered->crawler()->filter('.ab-score-row__value--emphasis');
        self::assertCount(1, $emphasised);
        self::assertStringContainsString('133', $emphasised->text());
    }

    #[Test]
    public function itShowsTheRosterAndAnEmptyHintWhenNoDealHasBeenPlayed(): void
    {
        $rendered = $this->renderTwigComponent('game_card', [
            'game' => new GameSummaryView(
                id: '01966000-0000-7000-8000-0000000000bb',
                name: 'Nouvelle partie',
                mode: 5,
                dealCount: 0,
                participants: [
                    new ParticipantSummaryView('p-1', 'Alice', 0),
                    new ParticipantSummaryView('p-2', 'Bob', 0),
                ],
                standings: [
                    new ParticipantSummaryView('p-1', 'Alice', 0),
                    new ParticipantSummaryView('p-2', 'Bob', 0),
                ],
            ),
        ]);

        self::assertStringContainsString('Pas encore de manche jouée', (string) $rendered);
        self::assertCount(2, $rendered->crawler()->filter('.participant-chip'));
        self::assertCount(0, $rendered->crawler()->filter('.ab-score-row'));
    }
}
