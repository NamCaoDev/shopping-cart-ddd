<?php

namespace App\User\Application\Port;

interface GoogleAuthenticatorPort
{
    public function generateSecret(): string;

    public function getOtpAuthUrl(string $email, string $secret, string $issuer = 'YourAppName'): string;

    public function verifyCode(string $secret, string $code): bool;
}
