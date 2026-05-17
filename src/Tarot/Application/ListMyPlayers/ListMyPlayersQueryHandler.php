<?php

declare(strict_types=1);

namespace App\Tarot\Application\ListMyPlayers;

use App\Tarot\Domain\Player\PlayerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus', method: 'handle')]
final readonly class ListMyPlayersQueryHandler
{
    public function __construct(
        private PlayerRepository $playerRepository,
    ) {}

    /** @return list<PlayerView> */
    public function handle(ListMyPlayersQuery $query): array
    {
        return array_map(
            static fn ($player): PlayerView => new PlayerView(
                id: $player->getId()->toString(),
                name: $player->getName(),
            ),
            $this->playerRepository->allOf($query->ownerId),
        );
    }
}
