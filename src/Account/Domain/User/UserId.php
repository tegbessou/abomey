<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

final readonly class UserId implements \Stringable
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
