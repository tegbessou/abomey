<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\DeleteAccount\DeleteAccountCommand;
use App\Account\Application\DeleteAccount\DeleteAccountCommandHandler;
use App\Account\Domain\User\UserNotFoundException;
use App\Shared\Application\Bus\EventBus;
use App\Tests\Fake\Account\InMemoryUserRepository;
use App\Tests\Stub\Shared\StubMessageBus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteAccountCommandHandlerTest extends TestCase
{
    #[Test]
    public function itThrowsWhenTheUserIsUnknown(): void
    {
        $handler = new DeleteAccountCommandHandler(
            new InMemoryUserRepository(),
            new EventBus(new StubMessageBus()),
        );

        $this->expectException(UserNotFoundException::class);

        $handler->handle(new DeleteAccountCommand(
            userId: '01900000-0000-7000-8000-000000000099',
        ));
    }
}
