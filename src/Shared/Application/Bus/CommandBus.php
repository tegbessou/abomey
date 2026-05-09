<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus
{
    use HandleTrait;

    public function __construct(
        #[Autowire(service: 'command.bus')]
        MessageBusInterface $commandBus,
    ) {
        $this->messageBus = $commandBus;
    }

    public function dispatch(object $command): mixed
    {
        return $this->handle($command);
    }
}
