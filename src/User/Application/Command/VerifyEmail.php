<?php

namespace App\User\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyEmail
{
    public function __construct(
        #[Assert\NotBlank(message: 'The verification token is required.')]
        #[Assert\Type('string')]
        public string $token,

        // Not part of the request body; overwritten via withUserId() from the {userId} route parameter.
        public string $userId = '',
    ) {}

    public function withUserId(string $userId): self
    {
        return new self($this->token, $userId);
    }
}
