<?php

// src/User/Presentation/DTO/TwoFactorAuthResponse.php
declare(strict_types=1);

namespace App\User\Presentation\Dto;

final readonly class TwoFactorAuthResponse
{
    private function __construct(private array $payload) {}

    public static function setupRequired(string $setupToken): self
    {
        return new self([
            'token' => $setupToken,
            '2fa_setup_required' => true,
            'message' => 'You must configure Google Authenticator or Email Auth to continue.'
        ]);
    }

    public static function verificationRequired(string $preAuthToken, array $availableProviders): self
    {
        return new self([
            'pre_auth_token' => $preAuthToken,
            '2fa_required' => true,
            // array_values ensures JSON encodes it as a clean array [] rather than an object {}
            'available_providers' => array_values(array_filter($availableProviders))
        ]);
    }

    public function toArray(): array
    {
        return $this->payload;
    }
}
