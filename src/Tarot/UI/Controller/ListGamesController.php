<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\ListMyGames\ListMyGamesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games', name: 'app_games_index', methods: ['GET'])]
final class ListGamesController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games.');
        }

        $games = $this->queryBus->ask(new ListMyGamesQuery(
            ownerId: $user->getUserIdentifier(),
        ));

        return $this->render('games/index.html.twig', [
            'games' => $games,
        ]);
    }
}
