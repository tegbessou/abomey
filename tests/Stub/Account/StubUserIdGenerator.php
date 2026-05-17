<?php

declare(strict_types=1);

namespace App\Tests\Stub\Account;

use App\Account\Domain\User\UserId;
use App\Account\Domain\User\UserIdGenerator;

final readonly class StubUserIdGenerator implements UserIdGenerator
{
    public function __construct(
        private UserId $id,
    ) {}

    public function generate(): UserId
    {
        return $this->id;
    }
}
