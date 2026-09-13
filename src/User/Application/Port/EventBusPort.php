<?php

namespace App\User\Application\Port;

interface EventBusPort
{
    public function publish(object $event): void;
}
