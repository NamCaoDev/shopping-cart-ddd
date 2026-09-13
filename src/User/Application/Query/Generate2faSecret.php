<?php

namespace App\User\Application\Query;

readonly class Generate2faSecret
{
    public function __construct(public string $userId) {}
}
