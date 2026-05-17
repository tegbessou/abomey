<?php

declare(strict_types=1);

namespace App\Tarot\Application\CreateGame;

final readonly class CreateGameCommand
{
    /**
     * @param list<string> $participantIds
     */
    public function __construct(
        public string $ownerId,
        public string $name,
        public int $mode,
        public array $participantIds,
    ) {}
}
