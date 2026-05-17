<?php

declare(strict_types=1);

namespace App\Tests\Builder\Tarot;

use App\Tarot\Domain\Player\Player;
use App\Tarot\Domain\Player\PlayerId;

final class PlayerBuilder
{
    private PlayerId $id;
    private string $owner = 'owner-default';
    private string $name = 'Default Player';

    private function __construct()
    {
        $this->id = PlayerId::fromString('01966000-0000-7000-8000-000000000000');
    }

    public static function aPlayer(): self
    {
        return new self();
    }

    public function withId(PlayerId|string $id): self
    {
        $this->id = $id instanceof PlayerId ? $id : PlayerId::fromString($id);

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

    public function build(): Player
    {
        return Player::create($this->id, $this->owner, $this->name);
    }
}
