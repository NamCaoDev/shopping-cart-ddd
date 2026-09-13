<?php

namespace App\User\Application\QueryHandler;

use App\User\Application\Dto\TwoFactorSecretResult;
use App\User\Application\Port\GoogleAuthenticatorPort;
use App\User\Application\Port\UserRepositoryPort;
use App\User\Application\Query\Generate2faSecret;
use Ecotone\Modelling\Attribute\QueryHandler;

readonly class Generate2faSecretHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private GoogleAuthenticatorPort $googleAuthPort
    ) {}

    #[QueryHandler]
    public function handle(Generate2faSecret $query): TwoFactorSecretResult
    {
        $user = $this->userRepository->findById($query->userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        $secret = $this->googleAuthPort->generateSecret();
        $otpAuthUrl = $this->googleAuthPort->getOtpauthUrl($user->getEmail(), $secret);

        return new TwoFactorSecretResult($secret, $otpAuthUrl);
    }
}
