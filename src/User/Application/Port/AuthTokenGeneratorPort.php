<?php

declare(strict_types=1);

namespace App\User\Application\Port;

use App\User\Domain\Entity\User;

interface AuthTokenGeneratorPort
{
    public function generatePendingToken(User $user): string;

    public function generateSetupToken(User $user): string;

    public function generateFullAccessToken(User $user): string;
}
