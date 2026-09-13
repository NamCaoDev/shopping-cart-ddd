<?php

namespace App\User\Infrastructure\Security;

use App\User\Domain\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Add pre-authentication checks (e.g., account suspension)
        if (!$user instanceof User) {
            return;
        }

        // If the email is not verified, throw an exception.
        // Symfony catches this and returns the message directly to the frontend.
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'You must verify your email address before you can log in. Please check your inbox.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Add post-authentication checks (e.g., password expiration)
    }
}
