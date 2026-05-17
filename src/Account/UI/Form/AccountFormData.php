<?php

declare(strict_types=1);

namespace App\Account\UI\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class AccountFormData
{
    #[Assert\IsTrue(message: 'account.must_confirm')]
    public bool $confirmed = false;
}
