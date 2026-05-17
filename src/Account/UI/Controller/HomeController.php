<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use App\Account\Application\GetUserDisplayName\GetUserDisplayNameQuery;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_home', methods: ['GET'])]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(): Response
    {
        $displayName = null;
        $user = $this->getUser();

        if (null !== $user) {
            /** @var string $displayName */
            $displayName = $this->queryBus->ask(
                new GetUserDisplayNameQuery($user->getUserIdentifier()),
            );
        }

        return $this->render('home.html.twig', ['displayName' => $displayName]);
    }
}
