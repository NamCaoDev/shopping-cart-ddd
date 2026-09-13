<?php

// src/User/Application/CommandHandler/VerifyEmailHandler.php
namespace App\User\Application\CommandHandler;

use App\User\Application\Command\VerifyEmail;
use App\User\Application\Port\EventBusPort;
use App\User\Application\Port\UserRepositoryPort;
use Ecotone\Modelling\Attribute\CommandHandler;

readonly class VerifyEmailHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private EventBusPort $eventBus
    ) {}

    #[CommandHandler]
    public function handle(VerifyEmail $command): void
    {
        // 1. Fetch the aggregate
        $user = $this->userRepository->findById($command->userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        // 2. Execute Domain Logic (The Entity protects its own invariants)
        $user->verifyEmail($command->token);

        // 3. Save state
        $this->userRepository->save($user);

        // 4. Publish Events
        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->publish($event);
        }
    }
}
