<?php

namespace App\User\Domain\Event;

final readonly class EmailWasVerified
{
    public function __construct(public string $userId) {}
}
