<?php

declare(strict_types=1);

namespace App\Shared\UI\Twig\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Modal', template: 'components/modal.html.twig')]
final class Modal
{
    public string $title = '';
    public bool $open = false;
}
