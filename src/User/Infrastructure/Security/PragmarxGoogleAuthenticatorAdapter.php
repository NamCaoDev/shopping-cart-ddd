<?php

namespace App\User\Infrastructure\Security;

use App\User\Application\Port\GoogleAuthenticatorPort;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

readonly class PragmarxGoogleAuthenticatorAdapter implements GoogleAuthenticatorPort
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getOtpAuthUrl(string $email, string $secret, string $issuer = 'YourAppName'): string
    {
        // Pragmarx handles standard OTP URL encoding natively
        return $this->google2fa->getQRCodeUrl($issuer, $email, $secret);
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }
}
