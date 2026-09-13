<?php

namespace App\User\Application\Dto;

readonly class VerifyTwoFactorCodeResult
{
    public function __construct(
        public string $jwt,
        public ?string $trustedToken
    ) {}
}
