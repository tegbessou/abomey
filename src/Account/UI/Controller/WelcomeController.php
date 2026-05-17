<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use App\Account\Application\AcceptPrivacyPolicy\AcceptPrivacyPolicyCommand;
use App\Account\Domain\User\PrivacyPolicyVersion;
use App\Account\UI\Form\WelcomeFormData;
use App\Account\UI\Form\WelcomeFormType;
use App\Shared\Application\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/welcome', name: 'app_welcome', methods: ['GET', 'POST'])]
final class WelcomeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /welcome.');
        }

        $version = PrivacyPolicyVersion::current();

        $form = $this->createForm(WelcomeFormType::class, new WelcomeFormData(), [
            'version' => $version->value,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new AcceptPrivacyPolicyCommand(
                userId: $user->getUserIdentifier(),
                version: $version->value,
            ));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('welcome.html.twig', [
            'form' => $form,
            'privacy_policy_version' => $version->value,
        ]);
    }
}
