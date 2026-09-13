<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Application\Port\TrustTokenGeneratorPort;
use App\User\Domain\ValueObject\TrustToken;
use Exception;

final class StandardTrustTokenGenerator implements TrustTokenGeneratorPort
{
    /**
     * @throws Exception If random_bytes fails
     */
    public function generate(): TrustToken
    {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        return new TrustToken($raw, $this->hash($raw));
    }

    public function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
