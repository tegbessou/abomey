<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/legal/notice', name: 'app_legal_notice', methods: ['GET'])]
final class LegalNoticeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('legal/notice.html.twig');
    }
}
