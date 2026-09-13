<?php

namespace App\User\Application\Dto;

readonly class TwoFactorSecretResult
{
    public function __construct(
        public string $secret,
        public string $otpAuthUrl
    ) {}
}
