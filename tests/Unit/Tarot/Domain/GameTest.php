<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tarot\Domain;

use App\Tarot\Domain\Game\ActivePlayerCountMismatchException;
use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\DeadPlayerNotParticipantException;
use App\Tarot\Domain\Game\DuplicateParticipantsException;
use App\Tarot\Domain\Game\EmptyGameNameException;
use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\InvalidRankingException;
use App\Tarot\Domain\Game\Mode;
use App\Tarot\Domain\Game\PartnerCannotBeTakerException;
use App\Tarot\Domain\Game\PartnerMustBeActivePlayerException;
use App\Tarot\Domain\Game\PartnerRequiresFivePlayerModeException;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Domain\Game\Ranking;
use App\Tarot\Domain\Game\TooFewParticipantsException;
use App\Tarot\Domain\Game\TooManyParticipantsException;
use App\Tests\Builder\Tarot\GameBuilder;
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

    #[Test]
    public function aClassicDealCanBeRecordedOnAGameWithMatchingTableSize(): void
    {
        $game = GameBuilder::aGame()->build();

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        self::assertCount(1, $game->getDeals());
    }

    #[Test]
    public function aClassicDealCanBeRecordedAtFivePlayersWithPreneurSeul(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Five)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $scores = $game->getDeals()[0]->pointsByPlayer();
        self::assertSame(136, $scores['p-1']);
        self::assertSame(-34, $scores['p-2']);
        self::assertSame(-34, $scores['p-3']);
        self::assertSame(-34, $scores['p-4']);
        self::assertSame(-34, $scores['p-5']);
    }

    #[Test]
    public function aClassicDealCanBeRecordedAtFivePlayersWithAPartner(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Five)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: 'p-2',
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $scores = $game->getDeals()[0]->pointsByPlayer();
        self::assertSame(68, $scores['p-1']);
        self::assertSame(34, $scores['p-2']);
        self::assertSame(-34, $scores['p-3']);
        self::assertSame(-34, $scores['p-4']);
        self::assertSame(-34, $scores['p-5']);
    }

    #[Test]
    public function aPartnerMustBeAnActivePlayerOfTheDeal(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Five)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6'])
            ->build();

        $this->expectException(PartnerMustBeActivePlayerException::class);

        $game->recordClassicDeal(
            deadPlayerIds: ['p-6'],
            partnerId: 'p-6',
            takerId: 'p-1',
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
    public function aPartnerCanOnlyBeDesignatedInFivePlayerMode(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();

        $this->expectException(PartnerRequiresFivePlayerModeException::class);

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: 'p-2',
            takerId: 'p-1',
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
    public function aPartnerCannotBeTheTaker(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Five)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $this->expectException(PartnerCannotBeTakerException::class);

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: 'p-1',
            takerId: 'p-1',
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
    public function aClassicDealAtThreePlayersHasTheTakerAloneAgainstTwoDefenders(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Three)
            ->withParticipants(['p-1', 'p-2', 'p-3'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $scores = $game->getDeals()[0]->pointsByPlayer();
        self::assertSame(68, $scores['p-1']);
        self::assertSame(-34, $scores['p-2']);
        self::assertSame(-34, $scores['p-3']);
    }

    #[Test]
    public function aFailedClassicDealAtThreePlayersCreditsTheTwoDefenders(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Three)
            ->withParticipants(['p-1', 'p-2', 'p-3'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::GardeSans,
            bouts: Bouts::Zero,
            pointsScored: 50,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $scores = $game->getDeals()[0]->pointsByPlayer();
        self::assertSame(-124, $scores['p-1']);
        self::assertSame(62, $scores['p-2']);
        self::assertSame(62, $scores['p-3']);
    }

    #[Test]
    public function aVachetteCanBeRecordedOnAGame(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();

        $game->recordVachette(
            deadPlayerIds: [],
            ranking: new Ranking(['p-1', 'p-2', 'p-3', 'p-4']),
        );

        $deals = $game->getDeals();
        self::assertCount(1, $deals);
        $scores = $deals[0]->pointsByPlayer();
        self::assertSame(120, $scores['p-1']);
        self::assertSame(60, $scores['p-2']);
        self::assertSame(-60, $scores['p-3']);
        self::assertSame(-120, $scores['p-4']);
    }

    #[Test]
    public function aVachetteRejectsARankingThatDoesNotCoverTheActivePlayers(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4'])
            ->build();

        $this->expectException(InvalidRankingException::class);

        $game->recordVachette(
            deadPlayerIds: [],
            ranking: new Ranking(['p-1', 'p-2', 'p-3', 'intrus']),
        );
    }

    #[Test]
    public function theNumberOfActivePlayersMustMatchTheMode(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $this->expectException(ActivePlayerCountMismatchException::class);

        $game->recordClassicDeal(
            deadPlayerIds: [],
            partnerId: null,
            takerId: 'p-1',
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
    public function aDeadPlayerMustBeAParticipantOfTheGame(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $this->expectException(DeadPlayerNotParticipantException::class);

        $game->recordClassicDeal(
            deadPlayerIds: ['unknown'],
            partnerId: null,
            takerId: 'p-1',
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
    public function aClassicDealCanBeRecordedWhenTwoDeadPlayersAreDesignated(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: ['p-5', 'p-6'],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $deals = $game->getDeals();
        self::assertCount(1, $deals);
        $scores = $deals[0]->pointsByPlayer();
        self::assertArrayNotHasKey('p-5', $scores);
        self::assertArrayNotHasKey('p-6', $scores);
        self::assertCount(4, $scores);
    }

    #[Test]
    public function aClassicDealCanBeRecordedWhenADeadPlayerIsDesignated(): void
    {
        $game = GameBuilder::aGame()
            ->withMode(Mode::Four)
            ->withParticipants(['p-1', 'p-2', 'p-3', 'p-4', 'p-5'])
            ->build();

        $game->recordClassicDeal(
            deadPlayerIds: ['p-5'],
            partnerId: null,
            takerId: 'p-1',
            contract: Contract::Garde,
            bouts: Bouts::One,
            pointsScored: 60,
            petitAuBout: PetitAuBout::None,
            chelem: Chelem::None,
            poignees: [],
            miseres: [],
        );

        $deals = $game->getDeals();
        self::assertCount(1, $deals);
        self::assertArrayNotHasKey('p-5', $deals[0]->pointsByPlayer());
    }

    private static function aCreatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::CREATED_AT_FIXTURE);
    }
}
