<?php

declare(strict_types=1);

namespace App\Tests\Integration\UI\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

final class DonneCardTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    #[Test]
    public function itRendersOneSignedScoreRowPerLineUnderTheDealHeader(): void
    {
        $rendered = $this->renderTwigComponent('donne_card', [
            'position' => 3,
            'scores' => [
                ['name' => 'Alice', 'points' => 34],
                ['name' => 'Bob', 'points' => -68],
            ],
        ]);

        $html = (string) $rendered;
        self::assertStringContainsString('Donne 3', $html);
        self::assertStringContainsString('Alice', $html);
        self::assertStringContainsString('+34', $html);
        self::assertStringContainsString('−68', $html);
        self::assertCount(2, $rendered->crawler()->filter('.ab-score-row'));
    }
}
