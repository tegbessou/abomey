<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\RecordClassicDeal\RecordClassicDealCommand;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\Domain\Game\DeadPlayersNotYetSupportedException;
use App\Tarot\UI\Form\RecordClassicDealFormData;
use App\Tarot\UI\Form\RecordClassicDealFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}/deals/new', name: 'app_game_deal_new', methods: ['GET', 'POST'])]
final class RecordClassicDealController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke(string $id, Request $request): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games/{id}/deals/new.');
        }

        $view = $this->queryBus->ask(new ShowGameQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (!$view instanceof GameView) {
            throw new NotFoundHttpException();
        }

        if (count($view->participants) > $view->mode) {
            throw new NotFoundHttpException();
        }

        $formData = new RecordClassicDealFormData();
        $form = $this->createForm(RecordClassicDealFormType::class, $formData, [
            'participants' => $view->participants,
        ]);

        $form->handleRequest($request);
        $errorKey = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->dispatch(new RecordClassicDealCommand(
                    ownerId: $user->getUserIdentifier(),
                    gameId: $id,
                    takerId: (string) $formData->takerId,
                    contract: (string) $formData->contract,
                    bouts: (int) $formData->bouts,
                    pointsScored: (int) $formData->pointsScored,
                ));

                return $this->redirectToRoute('app_game_show', ['id' => $id]);
            } catch (HandlerFailedException $e) {
                $original = $e->getPrevious() ?? $e;
                $errorKey = $original instanceof DeadPlayersNotYetSupportedException
                    ? 'deal.create.error.dead_players_not_supported'
                    : 'deal.create.error.unknown';
            }
        }

        return $this->render('games/new_deal.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => $errorKey,
        ]);
    }
}
