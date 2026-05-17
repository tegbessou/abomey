<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Player;

interface PlayerRepository
{
    public function create(Player $player): void;

    public function ofId(PlayerId $id, string $owner): ?Player;

    /**
     * @param list<PlayerId> $ids
     *
     * @return list<Player>
     */
    public function ofIds(array $ids, string $owner): array;

    /** @return list<Player> */
    public function allOf(string $owner): array;

    public function deleteAllOf(string $owner): void;
}
