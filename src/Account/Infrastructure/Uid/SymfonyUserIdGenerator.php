<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Uid;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserIdGenerator;
use Symfony\Component\Uid\Uuid;

final readonly class SymfonyUserIdGenerator implements UserIdGenerator
{
    public function generate(): UserId
    {
        return UserId::fromString(Uuid::v4()->toRfc4122());
    }
}
