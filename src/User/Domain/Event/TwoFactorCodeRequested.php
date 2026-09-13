<?php

namespace App\User\Domain\Event;

readonly class TwoFactorCodeRequested
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $code
    ) {}
}
