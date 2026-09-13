<?php

declare(strict_types=1);

namespace App\User\Presentation\Security;

final class AuthConstants
{
    public const TRUSTED_DEVICE_COOKIE_NAME = 'trusted_device';
    public const ROLE_2FA_PENDING = 'ROLE_2FA_PENDING';
    public const ROLE_2FA_SETUP = 'ROLE_2FA_SETUP';

    private function __construct()
    {
        // Prevent instantiation
    }
}
