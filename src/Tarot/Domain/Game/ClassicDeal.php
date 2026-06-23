<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class ClassicDeal extends Deal
{
    /**
     * @param list<string>  $activePlayerIds
     * @param list<Poignee> $poignees
     * @param list<Misere>  $miseres
     */
    private function __construct(
        Game $game,
        int $position,
        #[ORM\Column(name: 'active_player_ids', type: Types::JSON, nullable: true)]
        private readonly array $activePlayerIds,
        #[ORM\Column(name: 'partner_id', type: Types::STRING, length: 36, nullable: true)]
        private readonly ?string $partnerId,
        #[ORM\Column(name: 'taker_id', type: Types::STRING, length: 36, nullable: true)]
        private readonly string $takerId,
        #[ORM\Column(type: Types::STRING, length: 20, enumType: Contract::class, nullable: true)]
        private readonly Contract $contract,
        #[ORM\Column(type: Types::SMALLINT, enumType: Bouts::class, nullable: true)]
        private readonly Bouts $bouts,
        #[ORM\Column(name: 'points_scored', type: Types::SMALLINT, nullable: true)]
        private readonly int $pointsScored,
        #[ORM\Column(name: 'petit_au_bout', type: Types::STRING, length: 10, enumType: PetitAuBout::class, nullable: true)]
        private readonly PetitAuBout $petitAuBout,
        #[ORM\Column(name: 'chelem', type: Types::STRING, length: 20, enumType: Chelem::class, nullable: true)]
        private readonly Chelem $chelem,
        #[ORM\Column(name: 'poignees', type: 'poignee_list', nullable: true)]
        private readonly array $poignees,
        #[ORM\Column(name: 'miseres', type: 'misere_list', nullable: true)]
        private readonly array $miseres,
    ) {
        parent::__construct($game, $position);
    }

    /**
     * @param list<string>  $activePlayerIds
     * @param list<Poignee> $poignees
     * @param list<Misere>  $miseres
     */
    public static function createClassic(
        Game $game,
        int $position,
        array $activePlayerIds,
        ?string $partnerId,
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

        foreach ($poignees as $poignee) {
            if (!in_array($poignee->announcerId, $activePlayerIds, true)) {
                throw new PoigneeAnnouncerNotActiveException();
            }
        }

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
        }

        return new self($game, $position, $activePlayerIds, $partnerId, $takerId, $contract, $bouts, $pointsScored, $petitAuBout, $chelem, $poignees, $miseres);
    }

    /**
     * @return array<string, int>
     */
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

        $hasPartner = null !== $this->partnerId;
        $defendersCount = count($this->activePlayerIds) - ($hasPartner ? 2 : 1);
        $takerShare = $hasPartner ? $defendersCount - 1 : $defendersCount;

        $pointsByPlayer = [];
        foreach ($this->activePlayerIds as $playerId) {
            if ($playerId === $this->takerId) {
                $pointsByPlayer[$playerId] = $takerShare * $netScore;
            } elseif ($playerId === $this->partnerId) {
                $pointsByPlayer[$playerId] = $netScore;
            } else {
                $pointsByPlayer[$playerId] = -$netScore;
            }
        }

        return $this->withMiseresApplied($pointsByPlayer);
    }

    /**
     * @return array{activePlayerIds: list<string>, partnerId: ?string, takerId: string, contract: string, bouts: int, pointsScored: int, petitAuBout: string, chelem: string, poignees: list<array{announcerId: string, size: string}>, miseres: list<array{announcerId: string, type: string}>}
     */
    public function editSnapshot(): array
    {
        $poignees = [];
        foreach ($this->poignees as $poignee) {
            $poignees[] = ['announcerId' => $poignee->announcerId, 'size' => $poignee->size->value];
        }

        $miseres = [];
        foreach ($this->miseres as $misere) {
            $miseres[] = ['announcerId' => $misere->announcerId, 'type' => $misere->type->value];
        }

        return [
            'activePlayerIds' => $this->activePlayerIds,
            'partnerId' => $this->partnerId,
            'takerId' => $this->takerId,
            'contract' => $this->contract->value,
            'bouts' => $this->bouts->value,
            'pointsScored' => $this->pointsScored,
            'petitAuBout' => $this->petitAuBout->value,
            'chelem' => $this->chelem->value,
            'poignees' => $poignees,
            'miseres' => $miseres,
        ];
    }

    private function poigneesBonus(): int
    {
        $total = 0;
        foreach ($this->poignees as $poignee) {
            $total += $poignee->size->bonus();
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
        foreach ($this->miseres as $misere) {
            $pointsByPlayer = $this->withMisereApplied($misere, $pointsByPlayer);
        }

        return $pointsByPlayer;
    }

    /**
     * @param array<string, int> $pointsByPlayer
     *
     * @return array<string, int>
     */
    private function withMisereApplied(Misere $misere, array $pointsByPlayer): array
    {
        $othersCount = count($this->activePlayerIds) - 1;
        $pointsByPlayer[$misere->announcerId] += 10 * $othersCount;

        foreach ($this->activePlayerIds as $playerId) {
            if ($playerId === $misere->announcerId) {
                continue;
            }
            $pointsByPlayer[$playerId] -= 10;
        }

        return $pointsByPlayer;
    }
}
