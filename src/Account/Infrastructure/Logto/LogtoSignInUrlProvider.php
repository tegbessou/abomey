<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Logto;

use App\Account\Application\Auth\SignInUrlProvider;

final readonly class LogtoSignInUrlProvider implements SignInUrlProvider
{
    public function __construct(
        private LogtoClientFactory $logtoClientFactory,
    ) {}

    public function provideSignInUrl(string $callbackUrl): string
    {
        return $this->logtoClientFactory->create()->signIn($callbackUrl);
    }
}
