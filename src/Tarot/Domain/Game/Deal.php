<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'deals')]
final class Deal
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    /**
     * @param list<string>                                   $activePlayerIds
     * @param list<array{announcerId: string, size: string}> $poigneesData
     * @param list<array{announcerId: string, type: string}> $miseresData
     */
    private function __construct(
        #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'deals')]
        #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly Game $game,
        #[ORM\Column(type: Types::SMALLINT)]
        private readonly int $position,
        #[ORM\Column(name: 'active_player_ids', type: Types::JSON)]
        private readonly array $activePlayerIds,
        #[ORM\Column(name: 'taker_id', type: Types::STRING, length: 36)]
        private readonly string $takerId,
        #[ORM\Column(type: Types::STRING, length: 20, enumType: Contract::class)]
        private readonly Contract $contract,
        #[ORM\Column(type: Types::SMALLINT, enumType: Bouts::class)]
        private readonly Bouts $bouts,
        #[ORM\Column(name: 'points_scored', type: Types::SMALLINT)]
        private readonly int $pointsScored,
        #[ORM\Column(name: 'petit_au_bout', type: Types::STRING, length: 10, enumType: PetitAuBout::class)]
        private readonly PetitAuBout $petitAuBout,
        #[ORM\Column(name: 'chelem', type: Types::STRING, length: 20, enumType: Chelem::class)]
        private readonly Chelem $chelem,
        #[ORM\Column(name: 'poignees', type: Types::JSON)]
        private readonly array $poigneesData,
        #[ORM\Column(name: 'miseres', type: Types::JSON)]
        private readonly array $miseresData,
    ) {}

    /**
     * @param list<string>  $activePlayerIds
     * @param list<Poignee> $poignees
     * @param list<Misere>  $miseres
     */
    public static function createClassic(
        Game $game,
        int $position,
        array $activePlayerIds,
        string $takerId,
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
        PetitAuBout $petitAuBout,
        Chelem $chelem,
        array $poignees,
        array $miseres,
    ): self {
        if (!in_array($takerId, $activePlayerIds, true)) {
            throw new TakerNotActiveException();
        }

        if ($pointsScored < 0 || $pointsScored > 91) {
            throw new PointsScoredOutOfRangeException();
        }

        $poigneesData = [];
        foreach ($poignees as $poignee) {
            if (!in_array($poignee->announcerId, $activePlayerIds, true)) {
                throw new PoigneeAnnouncerNotActiveException();
            }
            $poigneesData[] = [
                'announcerId' => $poignee->announcerId,
                'size' => $poignee->size->value,
            ];
        }

        $miseresData = [];
        $seenMiseres = [];
        foreach ($miseres as $misere) {
            if (!in_array($misere->announcerId, $activePlayerIds, true)) {
                throw new MisereAnnouncerNotActiveException();
            }
            $signature = $misere->announcerId.'|'.$misere->type->value;
            if (in_array($signature, $seenMiseres, true)) {
                throw new DuplicateMisereException();
            }
            $seenMiseres[] = $signature;
            $miseresData[] = [
                'announcerId' => $misere->announcerId,
                'type' => $misere->type->value,
            ];
        }

        return new self($game, $position, $activePlayerIds, $takerId, $contract, $bouts, $pointsScored, $petitAuBout, $chelem, $poigneesData, $miseresData);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /** @return array<string, int> */
    public function pointsByPlayer(): array
    {
        $multiplier = $this->contract->multiplier();
        $target = $this->bouts->target();
        $gap = $this->pointsScored - $target;
        $baseScore = (25 + abs($gap)) * $multiplier;
        $takerWins = $gap >= 0;
        $signedBaseScore = $takerWins ? $baseScore : -$baseScore;
        $signedPoigneesBonus = $takerWins ? $this->poigneesBonus() : -$this->poigneesBonus();

        $netScore = $signedBaseScore
            + $this->petitAuBout->bonus($multiplier)
            + $this->chelem->bonus()
            + $signedPoigneesBonus;
        $defendersCount = count($this->activePlayerIds) - 1;

        $pointsByPlayer = [];
        foreach ($this->activePlayerIds as $playerId) {
            if ($playerId === $this->takerId) {
                $pointsByPlayer[$playerId] = $defendersCount * $netScore;
            } else {
                $pointsByPlayer[$playerId] = -$netScore;
            }
        }

        return $this->withMiseresApplied($pointsByPlayer);
    }

    private function poigneesBonus(): int
    {
        $total = 0;
        foreach ($this->poigneesData as $poignee) {
            $total += PoigneeSize::from($poignee['size'])->bonus();
        }

        return $total;
    }

    /**
     * @param array<string, int> $pointsByPlayer
     *
     * @return array<string, int>
     */
    private function withMiseresApplied(array $pointsByPlayer): array
    {
        $othersCount = count($this->activePlayerIds) - 1;
        foreach ($this->miseresData as $misere) {
            $announcerId = $misere['announcerId'];
            $pointsByPlayer[$announcerId] += 10 * $othersCount;
            foreach ($this->activePlayerIds as $playerId) {
                if ($playerId !== $announcerId) {
                    $pointsByPlayer[$playerId] -= 10;
                }
            }
        }

        return $pointsByPlayer;
    }
}
