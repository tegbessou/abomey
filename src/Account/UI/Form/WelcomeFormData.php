<?php

declare(strict_types=1);

namespace App\Account\UI\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class WelcomeFormData
{
    #[Assert\IsTrue(message: 'welcome.must_accept')]
    public bool $accepted = false;
}
