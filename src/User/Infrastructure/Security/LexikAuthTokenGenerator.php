<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Application\Port\AuthTokenGeneratorPort;
use App\User\Domain\Entity\User;
use App\User\Presentation\Security\AuthConstants;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final readonly class LexikAuthTokenGenerator implements AuthTokenGeneratorPort
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function generatePendingToken(User $user): string
    {
        return $this->jwtManager->createFromPayload($user, [
            '2fa_pending' => true,
            'roles' => [AuthConstants::ROLE_2FA_PENDING]
        ]);
    }

    public function generateSetupToken(User $user): string
    {
        return $this->jwtManager->createFromPayload($user, [
            'requires_2fa_setup' => true,
            'roles' => [AuthConstants::ROLE_2FA_SETUP]
        ]);
    }

    public function generateFullAccessToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }
}
