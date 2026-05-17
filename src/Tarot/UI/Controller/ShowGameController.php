<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}', name: 'app_game_show', methods: ['GET'])]
final class ShowGameController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(string $id): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games/{id}.');
        }

        $view = $this->queryBus->ask(new ShowGameQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (!$view instanceof GameView) {
            throw new NotFoundHttpException();
        }

        return $this->render('games/show.html.twig', [
            'game' => $view,
        ]);
    }
}
