<?php

declare(strict_types=1);

namespace App\User\Application\Port;

use App\User\Domain\ValueObject\TrustToken;

interface TrustTokenGeneratorPort
{
    public function generate(): TrustToken;

    public function hash(string $rawToken): string;
}
