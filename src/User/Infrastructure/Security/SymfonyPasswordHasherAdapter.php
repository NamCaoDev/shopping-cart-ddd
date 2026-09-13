<?php

namespace App\User\Infrastructure\Security;

use App\User\Application\Port\PasswordHasherPort;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

readonly class SymfonyPasswordHasherAdapter implements PasswordHasherPort
{
    public function __construct(
        private PasswordHasherFactoryInterface $hasherFactory
    ) {}

    public function hash(string $plainPassword): string
    {
        // Using the factory avoids needing a dummy User entity object here
        $hasher = $this->hasherFactory->getPasswordHasher('common');
        return $hasher->hash($plainPassword);
    }
}
