<?php

declare(strict_types=1);

namespace App\Tarot\UI\Twig\Component;

use App\Shared\Application\Bus\CommandBus;
use App\Tarot\Application\CreatePlayer\CreatePlayerCommand;
use App\Tarot\Domain\Player\PlayerId;
use App\Tarot\UI\Form\CreatePlayerFormData;
use App\Tarot\UI\Form\CreatePlayerFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'CreatePlayerForm',
    template: 'players/_create_player_form.html.twig',
)]
final class CreatePlayerForm extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $ownerId = '';

    #[LiveProp]
    public ?string $errorKey = null;

    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    public function mount(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return FormInterface<CreatePlayerFormData>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CreatePlayerFormType::class, new CreatePlayerFormData());
    }

    #[LiveAction]
    public function submit(): void
    {
        $this->errorKey = null;
        $this->submitForm();
        $form = $this->getForm();

        if (!$form->isValid()) {
            return;
        }

        /** @var CreatePlayerFormData $data */
        $data = $form->getData();

        try {
            /** @var PlayerId $playerId */
            $playerId = $this->commandBus->dispatch(new CreatePlayerCommand(
                ownerId: $this->ownerId,
                name: (string) $data->name,
            ));
        } catch (HandlerFailedException) {
            $this->errorKey = 'player.create.error.unknown';

            return;
        }

        $this->resetForm();
        $this->emit('player_created', ['playerId' => $playerId->toString()]);
    }

    #[LiveAction]
    public function cancel(): void
    {
        $this->errorKey = null;
        $this->resetForm();
        $this->emit('cancelled');
    }
}
