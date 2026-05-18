<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class CreatePlayerFormData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;
}
