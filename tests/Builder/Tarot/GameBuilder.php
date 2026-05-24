<?php

declare(strict_types=1);

namespace App\Tests\Builder\Tarot;

use App\Tarot\Domain\Game\Game;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\Mode;

final class GameBuilder
{
    private GameId $id;
    private string $owner = 'owner-default';
    private string $name = 'Default Game';
    private Mode $mode = Mode::Four;
    /** @var list<string> */
    private array $participantIds = ['p-1', 'p-2', 'p-3', 'p-4'];
    private \DateTimeImmutable $createdAt;

    private function __construct()
    {
        $this->id = GameId::fromString('01966000-0000-7000-8000-aa00000000aa');
        $this->createdAt = new \DateTimeImmutable('2026-05-24 12:00:00');
    }

    public static function aGame(): self
    {
        return new self();
    }

    public function withId(GameId|string $id): self
    {
        $this->id = $id instanceof GameId ? $id : GameId::fromString($id);

        return $this;
    }

    public function ownedBy(string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function named(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withMode(Mode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @param list<string> $participantIds
     */
    public function withParticipants(array $participantIds): self
    {
        $this->participantIds = $participantIds;

        return $this;
    }

    public function createdAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function build(): Game
    {
        return Game::create(
            $this->id,
            $this->owner,
            $this->name,
            $this->mode,
            $this->participantIds,
            $this->createdAt,
        );
    }
}
