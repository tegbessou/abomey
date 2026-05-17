<?php

declare(strict_types=1);

namespace App\Account\Application\Auth;

interface SignInUrlProvider
{
    public function provideSignInUrl(string $callbackUrl): string;
}
