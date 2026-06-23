<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\CorrectLastClassicDeal\CorrectLastClassicDealCommand;
use App\Tarot\Application\ShowGame\GameView;
use App\Tarot\Application\ShowGame\ShowGameQuery;
use App\Tarot\Domain\Game\GameNotFoundException;
use App\Tarot\UI\Form\RecordClassicDealFormData;
use App\Tarot\UI\Form\RecordClassicDealFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{id}/last-deal/correct-classic', name: 'app_game_last_deal_correct_classic', methods: ['POST'])]
final class CorrectLastClassicDealController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke(string $id, Request $request): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to correct a Donne.');
        }

        $view = $this->queryBus->ask(new ShowGameQuery(
            ownerId: $user->getUserIdentifier(),
            gameId: $id,
        ));

        if (!$view instanceof GameView) {
            throw new NotFoundHttpException();
        }

        $formData = new RecordClassicDealFormData();
        $form = $this->createForm(RecordClassicDealFormType::class, $formData, [
            'participants' => $view->participants,
            'mode' => $view->mode,
        ]);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->renderCorrectionForm($view, $form, null, $id);
        }

        try {
            $this->commandBus->dispatch(new CorrectLastClassicDealCommand(
                ownerId: $user->getUserIdentifier(),
                gameId: $id,
                deadPlayerIds: $formData->deadPlayerIds,
                partnerId: $formData->partnerId,
                takerId: (string) $formData->takerId,
                contract: (string) $formData->contract,
                bouts: (int) $formData->bouts,
                pointsScored: (int) $formData->pointsScored,
                petitAuBout: (string) $formData->petitAuBout,
                chelem: (string) $formData->chelem,
                poignees: $formData->poignees,
                miseres: $formData->miseres,
            ));
        } catch (HandlerFailedException $exception) {
            return $this->renderCorrectionForm($view, $form, $this->errorKeyForFailedDeal($exception), $id);
        }

        return $this->redirectToRoute('app_game_show', ['id' => $id]);
    }

    /**
     * @param FormInterface<RecordClassicDealFormData> $form
     */
    private function renderCorrectionForm(GameView $view, FormInterface $form, ?string $errorKey, string $id): Response
    {
        return $this->render('games/new_deal.html.twig', [
            'game' => $view,
            'form' => $form->createView(),
            'errorKey' => $errorKey,
            'page_title' => 'deal.correction.classic_title',
            'form_action' => $this->generateUrl('app_game_last_deal_correct_classic', ['id' => $id]),
        ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function errorKeyForFailedDeal(HandlerFailedException $exception): string
    {
        $original = array_first($exception->getWrappedExceptions()) ?? $exception;

        if ($original instanceof GameNotFoundException) {
            throw new NotFoundHttpException();
        }

        if (!$original instanceof \DomainException) {
            throw $exception;
        }

        return 'deal.create.error.invalid_input';
    }
}
