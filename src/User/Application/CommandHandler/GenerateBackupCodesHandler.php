<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\User\Application\Command\GenerateBackupCodesCommand;
use App\User\Application\Port\UserRepositoryPort;
use Ecotone\Modelling\Attribute\CommandHandler;

final readonly class GenerateBackupCodesHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository
    ) {}

    #[CommandHandler]
    public function handle(GenerateBackupCodesCommand $command): array
    {
        $user = $this->userRepository->findById($command->userId);

        $backupCodes = [];

        // Generate 8 codes formatted as "xxxx-xxxx"
        for ($i = 0; $i < 8; $i++) {
            $backupCodes[] = bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2));
        }

        // The entity accepts the plain array.
        // Your DBAL EncryptType automatically encrypts it before hitting the database.
        $user->setBackupCodes($backupCodes);
        $this->userRepository->save($user);

        // Return the plain codes precisely once so the controller can display them
        return $backupCodes;
    }
}
