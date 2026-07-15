<?php

declare(strict_types=1);

namespace App\Shared\UI\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/up', name: 'app_health', methods: ['GET'])]
final class HealthController
{
    public function __invoke(): Response
    {
        return new Response('OK', Response::HTTP_OK);
    }
}
