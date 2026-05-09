<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus
{
    use HandleTrait;

    public function __construct(
        #[Autowire(service: 'query.bus')]
        MessageBusInterface $queryBus,
    ) {
        $this->messageBus = $queryBus;
    }

    public function ask(object $query): mixed
    {
        return $this->handle($query);
    }
}
