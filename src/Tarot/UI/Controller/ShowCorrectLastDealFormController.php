<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\GetLastDeal\ClassicLastDealView;
use App\Tarot\Application\GetLastDeal\GetLastDealQuery;
use App\Tarot\Application\GetLastDeal\VachetteLastDealView;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\UI\Form\RecordClassicDealFormData;
use App\Tarot\UI\Form\RecordClassicDealFormType;
use App\Tarot\UI\Form\RecordVachetteFormData;
use App\Tarot\UI\Form\RecordVachetteFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}/last-deal/edit', name: 'app_game_last_deal_edit', methods: ['GET'])]
final class ShowCorrectLastDealFormController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(string $id, Request $request): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games/{id}/last-deal/edit.');
        }

        $view = $this->queryBus->ask(new ShowGameQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (!$view instanceof GameView) {
            throw new NotFoundHttpException();
        }

        $lastDeal = $this->queryBus->ask(new GetLastDealQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (null === $lastDeal) {
            throw new NotFoundHttpException();
        }

        if (!$lastDeal instanceof ClassicLastDealView && !$lastDeal instanceof VachetteLastDealView) {
            throw new \LogicException('GetLastDealQuery returned an unexpected view type.');
        }

        $requestedType = $request->query->get('as');
        $editAsVachette = 'vachette' === $requestedType
            || (null === $requestedType && $lastDeal instanceof VachetteLastDealView);

        if ($editAsVachette) {
            return $this->renderVachetteCorrection($view, $lastDeal, $id);
        }

        return $this->renderClassicCorrection($view, $lastDeal, $id);
    }

    private function renderClassicCorrection(GameView $view, ClassicLastDealView|VachetteLastDealView $lastDeal, string $id): Response
    {
        $formData = new RecordClassicDealFormData();
        if ($lastDeal instanceof ClassicLastDealView) {
            $formData->deadPlayerIds = $lastDeal->deadPlayerIds;
            $formData->partnerId = $lastDeal->partnerId;
            $formData->takerId = $lastDeal->takerId;
            $formData->contract = $lastDeal->contract;
            $formData->bouts = $lastDeal->bouts;
            $formData->pointsScored = $lastDeal->pointsScored;
            $formData->petitAuBout = $lastDeal->petitAuBout;
            $formData->chelem = $lastDeal->chelem;
            $formData->poignees = $lastDeal->poignees;
            $formData->miseres = $lastDeal->miseres;
        }

        $form = $this->createForm(RecordClassicDealFormType::class, $formData, [
            'participants' => $view->participants,
            'mode' => $view->mode,
        ]);

        return $this->render('games/new_deal.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => null,
            'page_title' => 'deal.correction.classic_title',
            'form_action' => $this->generateUrl('app_game_last_deal_correct_classic', ['id' => $id]),
            'correction_switch' => [
                'href' => $this->generateUrl('app_game_last_deal_edit', ['id' => $id, 'as' => 'vachette']),
                'label' => 'deal.correction.switch_to_vachette',
            ],
        ]);
    }

    private function renderVachetteCorrection(GameView $view, ClassicLastDealView|VachetteLastDealView $lastDeal, string $id): Response
    {
        $formData = new RecordVachetteFormData();
        $formData->ranking = array_fill(0, $view->mode, null);
        if ($lastDeal instanceof VachetteLastDealView) {
            $formData->deadPlayerIds = $lastDeal->deadPlayerIds;
            $formData->ranking = $lastDeal->ranking;
        }

        $form = $this->createForm(RecordVachetteFormType::class, $formData, [
            'participants' => $view->participants,
            'mode' => $view->mode,
        ]);

        return $this->render('games/new_vachette.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => null,
            'page_title' => 'deal.correction.vachette_title',
            'form_action' => $this->generateUrl('app_game_last_deal_correct_vachette', ['id' => $id]),
            'correction_switch' => [
                'href' => $this->generateUrl('app_game_last_deal_edit', ['id' => $id, 'as' => 'classic']),
                'label' => 'deal.correction.switch_to_classic',
            ],
        ]);
    }
}
