<?php

// src/User/Application/CommandHandler/RegisterUserHandler.php
namespace App\User\Application\CommandHandler;

use App\User\Application\Command\RegisterUser;
use App\User\Application\Port\EventBusPort;
use App\User\Application\Port\PasswordHasherPort;
use App\User\Application\Port\UserRepositoryPort;
use App\User\Domain\Entity\User;
use Ecotone\Modelling\Attribute\CommandHandler;

readonly class RegisterUserHandler
{
    public function __construct(
        private PasswordHasherPort $passwordHasher,
        private UserRepositoryPort $userRepository,
        private EventBusPort $eventBus,
    ) {}

    #[CommandHandler]
    public function handle(RegisterUser $command): void
    {
        if ($this->userRepository->isEmailTaken($command->email)) {
            throw new \DomainException('Email is already registered.');
        }

        if ($this->userRepository->isUsernameTaken($command->username)) {
            throw new \DomainException('Username is already taken.');
        }

        $hashedPassword = $this->passwordHasher->hash($command->plainPassword);

        // Create and return the pure Domain Aggregate
        // Ecotone will intercept the returned User and save it automatically.
        $user = User::register(
            $command->id,
            $command->username,
            $command->email,
            $hashedPassword
        );

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->publish($event);
        }
    }
}
