<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Presentation\Security\AuthConstants;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;

/**
 * Lexik builds the security token from User::getRoles(), ignoring the JWT payload,
 * so without this a pre-auth token would authenticate as a fully logged-in user.
 */
#[AsEventListener]
final class TwoFactorTokenRoleListener
{
    private const RESTRICTED_CLAIM_ROLES = [
        '2fa_pending' => AuthConstants::ROLE_2FA_PENDING,
        'requires_2fa_setup' => AuthConstants::ROLE_2FA_SETUP,
    ];

    public function __invoke(AuthenticationTokenCreatedEvent $event): void
    {
        $token = $event->getAuthenticatedToken();

        if (!$token instanceof JWTPostAuthenticationToken) {
            return;
        }

        $payload = $event->getPassport()->getAttribute('payload', []);

        foreach (self::RESTRICTED_CLAIM_ROLES as $claim => $role) {
            if (true !== ($payload[$claim] ?? false)) {
                continue;
            }

            $event->setAuthenticatedToken(new JWTPostAuthenticationToken(
                $token->getUser(),
                $token->getFirewallName(),
                [$role],
                $token->getCredentials(),
            ));

            return;
        }
    }
}
