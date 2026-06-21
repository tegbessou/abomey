<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\UI\Form\RecordVachetteFormData;
use App\Tarot\UI\Form\RecordVachetteFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}/vachettes/new', name: 'app_game_vachette_new', methods: ['GET'])]
final class ShowRecordVachetteFormController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(string $id): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games/{id}/vachettes/new.');
        }

        $view = $this->queryBus->ask(new ShowGameQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (!$view instanceof GameView) {
            throw new NotFoundHttpException();
        }

        $formData = new RecordVachetteFormData();
        $formData->ranking = array_fill(0, $view->mode, null);

        $form = $this->createForm(RecordVachetteFormType::class, $formData, [
            'participants' => $view->participants,
            'mode' => $view->mode,
        ]);

        return $this->render('games/new_vachette.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => null,
        ]);
    }
}
