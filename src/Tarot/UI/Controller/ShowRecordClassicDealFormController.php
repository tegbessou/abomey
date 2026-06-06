<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\UI\Form\RecordClassicDealFormData;
use App\Tarot\UI\Form\RecordClassicDealFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}/deals/new', name: 'app_game_deal_new', methods: ['GET'])]
final class ShowRecordClassicDealFormController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(string $id): Response
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

        $form = $this->createForm(RecordClassicDealFormType::class, new RecordClassicDealFormData(), [
            'participants' => $view->participants,
        ]);

        return $this->render('games/new_deal.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => null,
        ]);
    }
}
