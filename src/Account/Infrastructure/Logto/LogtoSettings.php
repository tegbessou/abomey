<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Logto;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LogtoSettings
{
    public function __construct(
        #[Autowire(env: 'LOGTO_ENDPOINT')]
        public string $endpoint,
        #[Autowire(env: 'LOGTO_APP_ID')]
        public string $appId,
        #[Autowire(env: 'LOGTO_APP_SECRET')]
        public string $appSecret,
    ) {}
}
