<?php

namespace App\User\Application\Port;

interface TwoFactorVerifierPort
{
    public function verifyGoogleCode(string $secret, string $code): bool;
}
