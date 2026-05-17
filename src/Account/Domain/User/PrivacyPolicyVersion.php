<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

enum PrivacyPolicyVersion: string
{
    case V2026_05_15 = '2026-05-15';

    public static function current(): self
    {
        return self::V2026_05_15;
    }
}
