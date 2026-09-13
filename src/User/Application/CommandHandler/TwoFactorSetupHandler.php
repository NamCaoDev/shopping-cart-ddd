<?php

namespace App\User\Application\CommandHandler;

use App\User\Application\Command\EnableEmailAuth;
use App\User\Application\Command\ConfirmGoogleAuthSetup;
use App\User\Application\Port\EventBusPort;
use App\User\Application\Port\UserRepositoryPort;
use App\User\Application\Port\GoogleAuthenticatorPort;
use Ecotone\Modelling\Attribute\CommandHandler;

readonly class TwoFactorSetupHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private GoogleAuthenticatorPort $googleAuthPort,
        private EventBusPort $eventBus,
    ) {}

    #[CommandHandler]
    public function handleEmailSetup(EnableEmailAuth $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        $user->enableEmailAuthenticator();

        $user->generateAndSendEmailAuthCode();

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->publish($event);
        }
    }

    #[CommandHandler]
    public function handleGoogleSetup(ConfirmGoogleAuthSetup $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        // Verify the code BEFORE saving the secret to the user
        $isValid = $this->googleAuthPort->verifyCode($command->secret, $command->code);

        if (!$isValid) {
            throw new \DomainException('Invalid Google Authenticator code. Please try again.');
        }

        $user->enableGoogleAuthenticator($command->secret);
        $this->userRepository->save($user);
    }
}
