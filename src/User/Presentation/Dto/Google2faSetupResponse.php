<?php

namespace App\User\Presentation\Dto;

readonly class Google2faSetupResponse
{
    public string $otpAuthUrl;

    public function __construct(
        public string $secret,
        string $email,
        string $appName = 'YourAppName'
    ) {
        $this->otpAuthUrl = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($appName),
            rawurlencode($email),
            $secret,
            rawurlencode($appName)
        );
    }
}
