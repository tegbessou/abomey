<?php

declare(strict_types=1);

namespace App\Tarot\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/new', name: 'app_game_new', methods: ['GET'])]
final class CreateGameController extends AbstractController
{
    public function __invoke(): Response
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \LogicException('User must be authenticated to access /games/new.');
        }

        return $this->render('games/new.html.twig', [
            'ownerId' => $user->getUserIdentifier(),
        ]);
    }
}
