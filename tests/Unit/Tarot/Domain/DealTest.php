<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\Deal;
use App\Tarot\Domain\Game\DuplicateMisereException;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\Misere;
use App\Tarot\Domain\Game\MisereAnnouncerNotActiveException;
use App\Tarot\Domain\Game\MisereType;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Domain\Game\Poignee;
use App\Tarot\Domain\Game\PoigneeAnnouncerNotActiveException;
use App\Tarot\Domain\Game\PoigneeSize;
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
            partnerId: null,
            takerId: 'alice',
            contract: $contract,
            bouts: $bouts,
            pointsScored: $pointsScored,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
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
            partnerId: null,
            takerId: 'eve',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
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
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: $invalid,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
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

    #[Test]
    #[DataProvider('petitAuBoutScenariosAtFourPlayers')]
    public function aClassicDealAppliesThePetitAuBoutBonusToTheRightSide(
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
        PetitAuBout $petitAuBout,
        int $takerPoints,
        int $defenderPoints,
    ): void {
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: $contract,
            bouts: $bouts,
            pointsScored: $pointsScored,
            petitAuBout: $petitAuBout,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
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

    /**
     * @return iterable<string, array{Contract, Bouts, int, PetitAuBout, int, int}>
     */
    public static function petitAuBoutScenariosAtFourPlayers(): iterable
    {
        yield 'Garde, 1 bout, taker wins, PAB on taker side' => [
            Contract::Garde, Bouts::One, 60, PetitAuBout::Taker, +132, -44,
        ];
        yield 'Garde Sans, 0 bouts, taker loses but keeps PAB' => [
            Contract::GardeSans, Bouts::Zero, 50, PetitAuBout::Taker, -126, +42,
        ];
        yield 'Garde, 2 bouts, taker wins but loses PAB to defense' => [
            Contract::Garde, Bouts::Two, 50, PetitAuBout::Defense, +72, -24,
        ];
    }

    #[Test]
    #[DataProvider('chelemScenariosAtFourPlayers')]
    public function aClassicDealAppliesTheChelemBonusToTheTaker(
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
        PetitAuBout $petitAuBout,
        Chelem $chelem,
        int $takerPoints,
        int $defenderPoints,
    ): void {
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: $contract,
            bouts: $bouts,
            pointsScored: $pointsScored,
            petitAuBout: $petitAuBout,
            chelem: $chelem,
            poignees: [],
            miseres: [],
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

    /**
     * @return iterable<string, array{Contract, Bouts, int, PetitAuBout, Chelem, int, int}>
     */
    public static function chelemScenariosAtFourPlayers(): iterable
    {
        yield 'Garde, 3 bouts, taker scores 91, PAB taker, chelem announced and realised' => [
            Contract::Garde, Bouts::Three, 91, PetitAuBout::Taker, Chelem::AnnouncedRealised, +1470, -490,
        ];
        yield 'Garde, 3 bouts, taker fails the announced chelem' => [
            Contract::Garde, Bouts::Three, 35, PetitAuBout::None, Chelem::AnnouncedFailed, -678, +226,
        ];
    }

    #[Test]
    public function aClassicDealAppliesThePoigneesBonusToTheWinningSide(): void
    {
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [new Poignee(announcerId: 'bob', size: PoigneeSize::Single)],
            miseres: [],
        );

        self::assertSame(
            ['alice' => 162, 'bob' => -54, 'charlie' => -54, 'david' => -54],
            $deal->pointsByPlayer(),
        );
    }

    #[Test]
    public function aClassicDealSumsMultiplePoigneesBonusForTheWinningSide(): void
    {
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [
                new Poignee(announcerId: 'alice', size: PoigneeSize::Single),
                new Poignee(announcerId: 'bob', size: PoigneeSize::Double),
            ],
            miseres: [],
        );

        // base 34 + poignees (20 + 30) = 84 net per defender, taker wins
        self::assertSame(
            ['alice' => 252, 'bob' => -84, 'charlie' => -84, 'david' => -84],
            $deal->pointsByPlayer(),
        );
    }

    #[Test]
    public function aClassicDealCannotBeCreatedWhenAPoigneeAnnouncerIsNotActive(): void
    {
        $this->expectException(PoigneeAnnouncerNotActiveException::class);

        Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [new Poignee(announcerId: 'eve', size: PoigneeSize::Single)],
            miseres: [],
        );
    }

    #[Test]
    public function aClassicDealAppliesAMisereBonusAfterTheClassicRedistribution(): void
    {
        // Exemple 11 : Garde, Preneur Alice réussit (60 pts, 1 bout) + Misère Atouts annoncée par Alice
        // Base : +102 / -34. Misère : Alice +30, autres -10. Total : +132 / -44.
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [new Misere(announcerId: 'alice', type: MisereType::Atouts)],
        );

        self::assertSame(
            ['alice' => 132, 'bob' => -44, 'charlie' => -44, 'david' => -44],
            $deal->pointsByPlayer(),
        );
    }

    #[Test]
    public function aClassicDealAllowsTheSameAnnouncerToDeclareBothMisereTypes(): void
    {
        // Alice annonce Atouts ET Tête : 2 × (+30 -10 -10 -10) ajoutés.
        // Base classique : Alice +102, autres -34. Misères : Alice +60, autres -20.
        // Total : Alice +162, autres -54.
        $deal = Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [
                new Misere(announcerId: 'alice', type: MisereType::Atouts),
                new Misere(announcerId: 'alice', type: MisereType::Tete),
            ],
        );

        self::assertSame(
            ['alice' => 162, 'bob' => -54, 'charlie' => -54, 'david' => -54],
            $deal->pointsByPlayer(),
        );
    }

    #[Test]
    public function aClassicDealCannotBeCreatedWithADuplicateMisere(): void
    {
        $this->expectException(DuplicateMisereException::class);

        Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [
                new Misere(announcerId: 'alice', type: MisereType::Atouts),
                new Misere(announcerId: 'alice', type: MisereType::Atouts),
            ],
        );
    }

    #[Test]
    public function aClassicDealCannotBeCreatedWhenAMisereAnnouncerIsNotActive(): void
    {
        $this->expectException(MisereAnnouncerNotActiveException::class);

        Deal::createClassic(
            game: self::aGameWithAliceBobCharlieDavid(),
            position: 1,
            activePlayerIds: ['alice', 'bob', 'charlie', 'david'],
            partnerId: null,
            takerId: 'alice',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [new Misere(announcerId: 'eve', type: MisereType::Atouts)],
        );
    }

    private static function aGameWithAliceBobCharlieDavid(): Game
    {
        return GameBuilder::aGame()
            ->withParticipants(['alice', 'bob', 'charlie', 'david'])
            ->build();
    }
}
