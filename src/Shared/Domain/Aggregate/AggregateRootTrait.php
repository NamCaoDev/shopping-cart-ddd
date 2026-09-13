<?php

namespace App\Shared\Domain\Aggregate;

trait AggregateRootTrait
{
    private array $domainEvents = [];

    protected function recordThat(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = []; // Clear immediately after grabbing them

        return $events;
    }
}
