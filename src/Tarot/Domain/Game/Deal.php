<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'deals')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: Types::STRING, length: 20)]
#[ORM\DiscriminatorMap(['classic' => ClassicDeal::class, 'vachette' => VachetteDeal::class])]
abstract class Deal
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'deals')]
        #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly Game $game,
        #[ORM\Column(type: Types::SMALLINT)]
        private readonly int $position,
    ) {}

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return array<string, int>
     */
    abstract public function pointsByPlayer(): array;
}
