<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\User\Application\Command\VerifyBackupCodeCommand;
use App\User\Application\Port\AuthTokenGeneratorPort;
use App\User\Application\Port\UserRepositoryPort;
use Ecotone\Modelling\Attribute\CommandHandler;

final readonly class VerifyBackupCodeHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private AuthTokenGeneratorPort $tokenGenerator,
    ) {}

    #[CommandHandler]
    public function handle(VerifyBackupCodeCommand $command): string
    {
        $user = $this->userRepository->findById($command->userId);

        // Attempt to consume the code. If it returns false, the code is invalid.
        if (!$user->consumeBackupCode($command->code)) {
            throw new \DomainException('Invalid or expired backup code.');
        }

        // Save the user immediately so the consumed code is wiped from the database
        $this->userRepository->save($user);

        // Issue the full access JWT (promoting them from ROLE_2FA_PENDING to ROLE_USER)
        return $this->tokenGenerator->generateFullAccessToken($user);
    }
}
