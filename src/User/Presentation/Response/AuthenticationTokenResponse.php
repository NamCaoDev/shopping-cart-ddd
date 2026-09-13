<?php

namespace App\User\Presentation\Response;

readonly class AuthenticationTokenResponse
{
    public function __construct(public string $token) {}
}
