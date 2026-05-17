<?php

declare(strict_types=1);

namespace App\Tarot\Domain\Game;

final readonly class GameId implements \Stringable
{
    private function __construct(
        private string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
