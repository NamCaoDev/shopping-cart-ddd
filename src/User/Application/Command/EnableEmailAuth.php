<?php

namespace App\User\Application\Command;

readonly class EnableEmailAuth
{
    public function __construct(public string $userId) {}
}
