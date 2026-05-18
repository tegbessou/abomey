<?php

declare(strict_types=1);

namespace App\Tarot\UI\Twig\Component;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use App\Tarot\Application\CreateGame\CreateGameCommand;
use App\Tarot\Application\ListMyPlayers\ListMyPlayersQuery;
use App\Tarot\Application\ListMyPlayers\PlayerView;
use App\Tarot\Domain\Game\DuplicateParticipantsException;
use App\Tarot\Domain\Game\EmptyGameNameException;
use App\Tarot\Domain\Game\GameId;
use App\Tarot\Domain\Game\ParticipantNotOwnedException;
use App\Tarot\Domain\Game\TooFewParticipantsException;
use App\Tarot\Domain\Game\TooManyParticipantsException;
use App\Tarot\UI\Form\CreateGameFormData;
use App\Tarot\UI\Form\CreateGameFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'CreateGameForm',
    template: 'games/_create_game_form.html.twig',
)]
final class CreateGameForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $ownerId = '';

    #[LiveProp]
    public ?string $errorKey = null;

    #[LiveProp]
    public bool $isModalOpen = false;

    /** @var list<PlayerView>|null */
    private ?array $cachedPlayers = null;

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {}

    public function mount(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return list<PlayerView>
     */
    public function players(): array
    {
        if (null !== $this->cachedPlayers) {
            return $this->cachedPlayers;
        }

        /** @var list<PlayerView> $players */
        $players = $this->queryBus->ask(new ListMyPlayersQuery($this->ownerId));

        return $this->cachedPlayers = $players;
    }

    /**
     * @return FormInterface<CreateGameFormData>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CreateGameFormType::class, new CreateGameFormData(), [
            'players' => $this->players(),
        ]);
    }

    #[LiveAction]
    public function submit(): ?Response
    {
        $this->errorKey = null;
        $this->submitForm();
        $form = $this->getForm();

        if (!$form->isValid()) {
            return null;
        }

        /** @var CreateGameFormData $data */
        $data = $form->getData();

        try {
            /** @var GameId $gameId */
            $gameId = $this->commandBus->dispatch(new CreateGameCommand(
                ownerId: $this->ownerId,
                name: (string) $data->name,
                mode: (int) $data->mode,
                participantIds: $data->participants,
            ));

            return $this->redirectToRoute('app_game_show', ['id' => $gameId->toString()]);
        } catch (HandlerFailedException $e) {
            $original = $e->getPrevious() ?? $e;
            $this->errorKey = match (true) {
                $original instanceof EmptyGameNameException => 'game.create.error.empty_name',
                $original instanceof TooFewParticipantsException => 'game.create.error.too_few',
                $original instanceof TooManyParticipantsException => 'game.create.error.too_many',
                $original instanceof DuplicateParticipantsException => 'game.create.error.duplicate',
                $original instanceof ParticipantNotOwnedException => 'game.create.error.not_owned',
                default => 'game.create.error.unknown',
            };

            return null;
        }
    }

    #[LiveAction]
    public function openModal(): void
    {
        $this->isModalOpen = true;
    }

    #[LiveAction]
    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }

    #[LiveListener('player_created')]
    public function onPlayerCreated(#[LiveArg] string $playerId): void
    {
        /** @var list<string> $participants */
        $participants = $this->formValues['participants'] ?? [];

        if (!in_array($playerId, $participants, true)) {
            $participants[] = $playerId;
        }

        $this->formValues['participants'] = $participants;

        $this->isModalOpen = false;
    }

    #[LiveListener('cancelled')]
    public function onCancelled(): void
    {
        $this->isModalOpen = false;
    }
}
