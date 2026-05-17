<?php

declare(strict_types=1);

namespace App\Account\Domain\User;

interface UserIdGenerator
{
    public function generate(): UserId;
}
