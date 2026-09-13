<?php

namespace App\User\Application\Command;

use App\User\Domain\Enum\TwoFactorProvider;
use Symfony\Component\Validator\Constraints as Assert;

readonly class VerifyTwoFactorCode
{
    public function __construct(
        #[Assert\NotBlank]
        public TwoFactorProvider $provider,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 6, exactMessage: '2FA code must be exactly 6 digits.')]
        public string $code,

        public bool $rememberMe = false,

        // Not part of the request body; overwritten via withUserId() from the authenticated user.
        public string $userId = '',
    ) {}

    public function withUserId(string $userId): self
    {
        return new self($this->provider, $this->code, $this->rememberMe, $userId);
    }
}
