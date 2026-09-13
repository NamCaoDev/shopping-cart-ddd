<?php

namespace App\User\Application\Command;

readonly class AuthenticateUser
{
    public function __construct(
        public string $identifier,
        public string $password
    ) {}
}
