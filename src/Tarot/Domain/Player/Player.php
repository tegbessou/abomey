<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Player;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'players')]
class Player
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'player_id', length: 36)]
        private PlayerId $id,
        #[ORM\Column(type: 'string', length: 255)]
        private string $name,
    ) {}

    public static function create(PlayerId $id, string $name): self
    {
        if ('' === trim($name)) {
            throw new \InvalidArgumentException('Player name cannot be empty.');
        }

        return new self($id, $name);
    }

    public function getId(): PlayerId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
