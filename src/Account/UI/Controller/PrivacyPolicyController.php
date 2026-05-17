<?php

declare(strict_types=1);

namespace App\Account\UI\Controller;

use App\Account\Domain\User\PrivacyPolicyVersion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/legal/privacy-policy', name: 'app_privacy_policy', methods: ['GET'])]
final class PrivacyPolicyController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('legal/privacy-policy.html.twig', [
            'privacy_policy_version' => PrivacyPolicyVersion::current()->value,
        ]);
    }
}
