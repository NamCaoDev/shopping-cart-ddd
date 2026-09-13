<?php

namespace App\User\Infrastructure\Messaging;

use App\User\Application\Port\EventBusPort;
use Ecotone\Modelling\EventBus;

readonly class EcotoneEventBusAdapter implements EventBusPort
{
    public function __construct(private EventBus $ecotoneEventBus) {}

    public function publish(object $event): void
    {
        $this->ecotoneEventBus->publish($event);
    }
}
