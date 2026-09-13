<?php

namespace App\User\Application\Port;

interface UserUniquenessCheckerPort
{
    public function isEmailTaken(string $email): bool;
    public function isUsernameTaken(string $username): bool;
}
