<?php

namespace App\User\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ConfirmGoogleAuthSetup
{
    public function __construct(
        #[Assert\NotBlank(message: 'The secret parameter is required.')]
        public string $secret, // The secret generated in step 1

        #[Assert\NotBlank(message: 'The 6-digit 2FA code is required.')]
        #[Assert\Length(exactly: 6, exactMessage: 'The code must be exactly {{ limit }} digits.')]
        public string $code,    // The 6-digit code the user typed

        // Not part of the request body; overwritten via withUserId() from the authenticated user.
        public string $userId = '',
    ) {}

    public function withUserId(string $userId): self
    {
        return new self($this->secret, $this->code, $userId);
    }
}
