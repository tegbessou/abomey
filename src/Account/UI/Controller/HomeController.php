<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_home', methods: ['GET'])]
final class HomeController extends AbstractController
{
    public function __invoke(): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_games_index');
        }

        return $this->render('home.html.twig');
    }
}
