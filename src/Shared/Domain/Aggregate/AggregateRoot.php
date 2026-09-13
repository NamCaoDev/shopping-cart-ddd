<?php

namespace App\Shared\Domain\Aggregate;

interface AggregateRoot
{
    /**
     * Returns the recorded events AND clears them from memory at the same time.
     */
    public function pullDomainEvents(): array;
}
