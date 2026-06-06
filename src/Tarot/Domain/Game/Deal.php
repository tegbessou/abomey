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
     * @param list<string> $activePlayerIds
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
    ) {}

    /**
     * @param list<string> $activePlayerIds
     */
    public static function createClassic(
        Game $game,
        int $position,
        array $activePlayerIds,
        string $takerId,
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
    ): self {
        if (!in_array($takerId, $activePlayerIds, true)) {
            throw new TakerNotActiveException();
        }

        if ($pointsScored < 0 || $pointsScored > 91) {
            throw new PointsScoredOutOfRangeException();
        }

        return new self($game, $position, $activePlayerIds, $takerId, $contract, $bouts, $pointsScored);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /** @return array<string, int> */
    public function pointsByPlayer(): array
    {
        $target = $this->bouts->target();
        $gap = $this->pointsScored - $target;
        $score = (25 + abs($gap)) * $this->contract->multiplier();
        $takerWins = $gap >= 0;

        $defendersCount = count($this->activePlayerIds) - 1;
        $takerSign = $takerWins ? 1 : -1;

        $pointsByPlayer = [];
        foreach ($this->activePlayerIds as $playerId) {
            if ($playerId === $this->takerId) {
                $pointsByPlayer[$playerId] = $takerSign * $defendersCount * $score;
            } else {
                $pointsByPlayer[$playerId] = -$takerSign * $score;
            }
        }

        return $pointsByPlayer;
    }
}
