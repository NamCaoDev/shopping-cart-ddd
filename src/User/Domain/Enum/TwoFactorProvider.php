<?php

namespace App\User\Domain\Enum;

enum TwoFactorProvider: string
{
    case Google = 'google';
    case Email = 'email';
}
