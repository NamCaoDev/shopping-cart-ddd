<?php

namespace App\User\Application\Port;

interface PasswordHasherPort
{
    public function hash(string $plainPassword): string;
}
