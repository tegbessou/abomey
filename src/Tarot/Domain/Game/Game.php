<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'games')]
final readonly class Game
{
    /**
     * @param list<string> $participantIds
     */
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'game_id', length: 36)]
        private GameId $id,
        #[ORM\Column(name: 'owner_id', type: Types::STRING, length: 36)]
        private string $owner,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $name,
        #[ORM\Column(type: Types::SMALLINT, enumType: Mode::class)]
        private Mode $mode,
        #[ORM\Column(name: 'participant_ids', type: Types::JSON)]
        private array $participantIds,
    ) {}

    /**
     * @param list<string> $participantIds
     */
    public static function create(
        GameId $id,
        string $owner,
        string $name,
        Mode $mode,
        array $participantIds,
    ): self {
        $trimmedName = trim($name);

        if ('' === $trimmedName) {
            throw new EmptyGameNameException();
        }

        if (count($participantIds) !== count(array_unique($participantIds))) {
            throw new DuplicateParticipantsException();
        }

        if (count($participantIds) < $mode->value) {
            throw new TooFewParticipantsException();
        }

        if (count($participantIds) > $mode->value + 2) {
            throw new TooManyParticipantsException();
        }

        return new self($id, $owner, $trimmedName, $mode, $participantIds);
    }

    public function getId(): GameId
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

    public function getMode(): Mode
    {
        return $this->mode;
    }

    /** @return list<string> */
    public function getParticipantIds(): array
    {
        return $this->participantIds;
    }
}
