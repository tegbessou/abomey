<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Logto;

use Logto\Sdk\Constants\UserScope;
use Logto\Sdk\LogtoClient;
use Logto\Sdk\LogtoConfig as SdkLogtoConfig;

final readonly class LogtoClientFactory
{
    public function __construct(
        private LogtoSettings $settings,
    ) {}

    public function create(): LogtoClient
    {
        return new LogtoClient(new SdkLogtoConfig(
            endpoint: $this->settings->endpoint,
            appId: $this->settings->appId,
            appSecret: $this->settings->appSecret,
            scopes: [UserScope::email->value],
        ));
    }
}
