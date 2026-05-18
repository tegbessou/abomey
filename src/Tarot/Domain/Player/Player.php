<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Player;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'players')]
final class Player
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'player_id', length: 36)]
        private PlayerId $id,
        #[ORM\Column(name: 'owner_id', type: Types::STRING, length: 36)]
        private string $owner,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $name,
    ) {}

    public static function create(PlayerId $id, string $owner, string $name): self
    {
        if ('' === trim($name)) {
            throw new EmptyPlayerNameException();
        }

        return new self($id, $owner, $name);
    }

    public function getId(): PlayerId
    {
        return $this->id;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
