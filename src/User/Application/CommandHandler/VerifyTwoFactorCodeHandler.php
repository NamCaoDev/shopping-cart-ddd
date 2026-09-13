<?php

namespace App\User\Application\CommandHandler;

use App\User\Application\Command\VerifyTwoFactorCode;
use App\User\Application\Dto\VerifyTwoFactorCodeResult;
use App\User\Application\Port\AuthTokenGeneratorPort;
use App\User\Application\Port\GoogleAuthenticatorPort;
use App\User\Application\Port\TrustedDeviceRepositoryPort;
use App\User\Application\Port\TrustTokenGeneratorPort;
use App\User\Application\Port\UserRepositoryPort;
use App\User\Domain\Entity\TrustedDevice;
use App\User\Domain\Enum\TwoFactorProvider;
use Ecotone\Modelling\Attribute\CommandHandler;
use Symfony\Component\Uid\Uuid;

readonly class VerifyTwoFactorCodeHandler
{
    public function __construct(
        private UserRepositoryPort          $userRepository,
        private GoogleAuthenticatorPort     $googleAuthPort,
        private AuthTokenGeneratorPort          $tokenGenerator,
        private TrustedDeviceRepositoryPort $trustedDeviceRepository,
        private TrustTokenGeneratorPort     $trustTokenGenerator,
    ) {}

    #[CommandHandler]
    public function handle(VerifyTwoFactorCode $command): VerifyTwoFactorCodeResult
    {
        $user = $this->userRepository->findById($command->userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        // Delegate logic based on provider
        $isValid = match ($command->provider) {
            TwoFactorProvider::Google => $user->isGoogleAuthenticatorEnabled()
                && $this->googleAuthPort->verifyCode($user->getGoogleAuthenticatorSecret(), $command->code),

            TwoFactorProvider::Email => $user->isEmailAuthEnabled()
                && $user->verifyEmailAuthCode($command->code),
        };

        if (!$isValid) {
            throw new \DomainException('Invalid 2FA code.');
        }

        // Optionally clear email code after successful use
        if ($command->provider === TwoFactorProvider::Email) {
            $user->clearEmailAuthCode();
            $this->userRepository->save($user);
        }

        $response = new VerifyTwoFactorCodeResult(
            jwt: $this->tokenGenerator->generateFullAccessToken($user),
            trustedToken: null
        );

        if ($command->rememberMe) {
            $trustedToken = $this->trustTokenGenerator->generate();

            $trustedDevice = new TrustedDevice(
                Uuid::v7(),
                Uuid::fromString($command->userId),
                $trustedToken->hash, // Save the hash to the DB
                (new \DateTimeImmutable())->modify('+30 days')
            );

            $this->trustedDeviceRepository->save($trustedDevice);

            // Pass the raw token to the Controller to become a Cookie
            $response['trusted_token'] = $trustedToken->raw;
        }

        return $response;
    }
}
