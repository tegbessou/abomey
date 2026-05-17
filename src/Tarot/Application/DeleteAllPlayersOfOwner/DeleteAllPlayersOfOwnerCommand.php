<?php

declare(strict_types=1);

namespace App\Tarot\Application\DeleteAllPlayersOfOwner;

final readonly class DeleteAllPlayersOfOwnerCommand
{
    public function __construct(
        public string $ownerId,
    ) {}
}
