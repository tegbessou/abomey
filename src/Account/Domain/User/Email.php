<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

final readonly class Email
{
    private function __construct(
        private string $value,
    ) {}

    public static function fromString(string $value): self
    {
        $normalized = mb_strtolower(trim($value));

        if (false === filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException();
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
