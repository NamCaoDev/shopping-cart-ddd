<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

final readonly class TrustToken
{
    public function __construct(
        public string $raw,
        public string $hash
    ) {}
}
