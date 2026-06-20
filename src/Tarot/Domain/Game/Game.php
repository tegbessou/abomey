<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'games')]
final class Game
{
    /** @var Collection<int, Deal> */
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Deal::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $deals;

    /**
     * @param list<string> $participantIds
     */
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'game_id', length: 36)]
        private readonly GameId $id,
        #[ORM\Column(name: 'owner_id', type: Types::STRING, length: 36)]
        private readonly string $owner,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private readonly string $name,
        #[ORM\Column(type: Types::SMALLINT, enumType: Mode::class)]
        private readonly Mode $mode,
        #[ORM\Column(name: 'participant_ids', type: Types::JSON)]
        private readonly array $participantIds,
        #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
        private readonly \DateTimeImmutable $createdAt,
    ) {
        $this->deals = new ArrayCollection();
    }

    /**
     * @param list<string> $participantIds
     */
    public static function create(
        GameId $id,
        string $owner,
        string $name,
        Mode $mode,
        array $participantIds,
        \DateTimeImmutable $createdAt,
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

        return new self($id, $owner, $trimmedName, $mode, $participantIds, $createdAt);
    }

    /**
     * @param list<string>  $deadPlayerIds
     * @param list<Poignee> $poignees
     * @param list<Misere>  $miseres
     */
    public function recordClassicDeal(
        array $deadPlayerIds,
        ?string $partnerId,
        string $takerId,
        Contract $contract,
        Bouts $bouts,
        int $pointsScored,
        PetitAuBout $petitAuBout,
        Chelem $chelem,
        array $poignees,
        array $miseres,
    ): void {
        foreach ($deadPlayerIds as $deadId) {
            if (!in_array($deadId, $this->participantIds, true)) {
                throw new DeadPlayerNotParticipantException();
            }
        }

        $activePlayerIds = array_values(array_diff($this->participantIds, $deadPlayerIds));

        if (count($activePlayerIds) !== $this->mode->value) {
            throw new ActivePlayerCountMismatchException();
        }

        if (null !== $partnerId && !in_array($partnerId, $activePlayerIds, true)) {
            throw new PartnerMustBeActivePlayerException();
        }

        if (null !== $partnerId && $partnerId === $takerId) {
            throw new PartnerCannotBeTakerException();
        }

        $deal = Deal::createClassic(
            game: $this,
            position: $this->deals->count() + 1,
            activePlayerIds: $activePlayerIds,
            partnerId: $partnerId,
            takerId: $takerId,
            contract: $contract,
            bouts: $bouts,
            pointsScored: $pointsScored,
            petitAuBout: $petitAuBout,
            chelem: $chelem,
            poignees: $poignees,
            miseres: $miseres,
        );
        $this->deals->add($deal);
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return list<Deal> */
    public function getDeals(): array
    {
        return array_values($this->deals->toArray());
    }
}
