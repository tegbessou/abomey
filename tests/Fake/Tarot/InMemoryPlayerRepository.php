<?php

declare(strict_types=1);

namespace App\Tests\Fake\Tarot;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\Domain\Player\PlayerRepository;

final class InMemoryPlayerRepository implements PlayerRepository
{
    /** @var array<string, Player> */
    private array $players = [];

    public function create(Player $player): void
    {
        $this->players[$player->getId()->toString()] = $player;
    }

    public function ofId(PlayerId $id, string $owner): ?Player
    {
        $player = $this->players[$id->toString()] ?? null;

        if (null === $player || $player->getOwner() !== $owner) {
            return null;
        }

        return $player;
    }

    public function ofIds(array $ids, string $owner): array
    {
        $idsAsStrings = array_map(
            static fn (PlayerId $id): string => $id->toString(),
            $ids,
        );

        return array_values(array_filter(
            $this->players,
            static fn (Player $player): bool => $player->getOwner() === $owner
                && in_array($player->getId()->toString(), $idsAsStrings, true),
        ));
    }

    public function allOf(string $owner): array
    {
        return array_values(array_filter(
            $this->players,
            static fn (Player $player): bool => $player->getOwner() === $owner,
        ));
    }

    public function deleteAllOf(string $owner): void
    {
        foreach ($this->players as $key => $player) {
            if ($player->getOwner() === $owner) {
                unset($this->players[$key]);
            }
        }
    }
}
