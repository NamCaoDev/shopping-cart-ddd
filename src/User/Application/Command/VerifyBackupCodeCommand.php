<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyBackupCodeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $code,

        // Not part of the request body; overwritten via withUserId() from the authenticated user.
        public string $userId = '',
    ) {}

    public function withUserId(string $userId): self
    {
        return new self($this->code, $userId);
    }
}
