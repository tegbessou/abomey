<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateGameFormData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: [3, 4, 5])]
    public ?int $mode = null;

    /** @var list<string> */
    #[Assert\Count(min: 1)]
    public array $participants = [];
}
