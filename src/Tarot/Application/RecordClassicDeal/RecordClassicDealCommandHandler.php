<?php

declare(strict_types=1);

namespace App\Tarot\Application\RecordClassicDeal;

use App\Tarot\Domain\Game\Bouts;
use App\Tarot\Domain\Game\Chelem;
use App\Tarot\Domain\Game\Contract;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\Domain\Game\GameRepository;
use App\Tarot\Domain\Game\Misere;
use App\Tarot\Domain\Game\MisereType;
use App\Tarot\Domain\Game\PetitAuBout;
use App\Tarot\Domain\Game\Poignee;
use App\Tarot\Domain\Game\PoigneeSize;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus', method: 'handle')]
final readonly class RecordClassicDealCommandHandler
{
    public function __construct(
        private GameRepository $gameRepository,
    ) {}

    public function handle(RecordClassicDealCommand $command): void
    {
        $game = $this->gameRepository->ofId(
            GameId::fromString($command->gameId),
            $command->ownerId,
        );

        if (null === $game) {
            throw new GameNotFoundException();
        }

        $poignees = [];
        foreach ($command->poignees as $serialized) {
            $poignees[] = new Poignee(
                announcerId: $serialized['announcerId'],
                size: PoigneeSize::from($serialized['size']),
            );
        }

        $miseres = [];
        foreach ($command->miseres as $serialized) {
            $miseres[] = new Misere(
                announcerId: $serialized['announcerId'],
                type: MisereType::from($serialized['type']),
            );
        }

        $game->recordClassicDeal(
            takerId: $command->takerId,
            contract: Contract::from($command->contract),
            bouts: Bouts::from($command->bouts),
            pointsScored: $command->pointsScored,
            petitAuBout: PetitAuBout::from($command->petitAuBout),
            chelem: Chelem::from($command->chelem),
            poignees: $poignees,
            miseres: $miseres,
        );

        $this->gameRepository->update($game);
    }
}
