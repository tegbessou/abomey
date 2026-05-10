<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EventBus
{
    public function __construct(
        #[Autowire(service: 'event.bus')]
        private MessageBusInterface $eventBus,
    ) {}

    public function publish(object $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
