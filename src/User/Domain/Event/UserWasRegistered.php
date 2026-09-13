<?php

namespace App\User\Domain\Event;

final readonly class UserWasRegistered
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $verificationToken
    ) {}
}
