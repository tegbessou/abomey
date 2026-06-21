<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class VachetteDeal extends Deal
{
    /**
     * @var array<int, list<int>>
     */
    private const array SCALES = [
        3 => [120, 0, -120],
        4 => [120, 60, -60, -120],
        5 => [120, 60, 0, -60, -120],
    ];

    private function __construct(
        Game $game,
        int $position,
        #[ORM\Column(name: 'ranking', type: 'ranking', nullable: true)]
        private readonly Ranking $ranking,
    ) {
        parent::__construct($game, $position);
    }

    public static function create(Game $game, int $position, Ranking $ranking): self
    {
        return new self($game, $position, $ranking);
    }

    /**
     * @return array<string, int>
     */
    public function pointsByPlayer(): array
    {
        $players = $this->ranking->players();
        $scale = self::SCALES[count($players)];

        $pointsByPlayer = [];
        foreach ($players as $index => $playerId) {
            $pointsByPlayer[$playerId] = $scale[$index];
        }

        return $pointsByPlayer;
    }
}
