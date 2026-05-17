<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use App\Account\Application\DeleteAccount\DeleteAccountCommand;
use App\Account\UI\Form\AccountFormData;
use App\Account\UI\Form\AccountFormType;
use App\Shared\Application\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account', name: 'app_account', methods: ['GET', 'POST'])]
final class AccountController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /account.');
        }

        $form = $this->createForm(AccountFormType::class, new AccountFormData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new DeleteAccountCommand(
                userId: $user->getUserIdentifier(),
            ));

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('account.html.twig', [
            'form' => $form,
        ]);
    }
}
