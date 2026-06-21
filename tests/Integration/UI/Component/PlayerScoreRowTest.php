<?php

declare(strict_types=1);

namespace App\Tests\Integration\UI\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Symfony\UX\TwigComponent\Test\RenderedComponent;

final class PlayerScoreRowTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    #[Test]
    public function itRendersTheNameAndTheRawValueByDefault(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Alice',
            'value' => -34,
        ]);

        self::assertStringContainsString('Alice', (string) $rendered);
        self::assertSame('-34', $this->valueText($rendered));
    }

    #[Test]
    public function itPrefixesPositiveValuesWithAPlusWhenSigned(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Alice',
            'value' => 34,
            'signed' => true,
        ]);

        self::assertSame('+34', $this->valueText($rendered));
    }

    #[Test]
    public function itUsesATypographicMinusForNegativeValuesWhenSigned(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Bob',
            'value' => -68,
            'signed' => true,
        ]);

        self::assertSame('−68', $this->valueText($rendered));
    }

    #[Test]
    public function itShowsZeroWithoutASignWhenSigned(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Carole',
            'value' => 0,
            'signed' => true,
        ]);

        self::assertSame('0', $this->valueText($rendered));
    }

    #[Test]
    public function itMarksTheValueAsEmphasisedWhenLeading(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Alice',
            'value' => 133,
            'emphasis' => true,
        ]);

        self::assertCount(1, $rendered->crawler()->filter('.ab-score-row__value--emphasis'));
    }

    #[Test]
    public function itDoesNotEmphasiseTheValueByDefault(): void
    {
        $rendered = $this->renderTwigComponent('player_score_row', [
            'name' => 'Bob',
            'value' => -42,
        ]);

        self::assertCount(0, $rendered->crawler()->filter('.ab-score-row__value--emphasis'));
    }

    private function valueText(RenderedComponent $rendered): string
    {
        return trim($rendered->crawler()->filter('.ab-score-row__value')->text());
    }
}
