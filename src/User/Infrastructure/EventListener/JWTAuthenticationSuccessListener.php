<?php

// src/User/Infrastructure/EventListener/JWTAuthenticationSuccessListener.php
declare(strict_types=1);

namespace App\User\Infrastructure\EventListener;

use App\User\Application\Port\AuthTokenGeneratorPort;
use App\User\Application\Port\EventBusPort;
use App\User\Application\Port\TrustedDeviceRepositoryPort;
use App\User\Application\Port\TrustTokenGeneratorPort;
use App\User\Application\Port\UserRepositoryPort;// Note: Assuming you named it as a Port
use App\User\Domain\Entity\User;
use App\User\Presentation\Dto\TwoFactorAuthResponse;
use App\User\Presentation\Security\AuthConstants;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class JWTAuthenticationSuccessListener
{
    public function __construct(
        private AuthTokenGeneratorPort $authTokenGenerator,
        private UserRepositoryPort $userRepository,
        private EventBusPort $eventBus,
        private RequestStack $requestStack,
        private TrustedDeviceRepositoryPort $trustedDeviceRepository, // Inject the repository port
        private TrustTokenGeneratorPort $trustTokenGenerator,
    ) {}

    #[AsEventListener(event: Events::AUTHENTICATION_SUCCESS, priority: 10)]
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        // 1. Force 2FA Setup if nothing is configured
        if (!$user->isGoogleAuthenticatorEnabled() && !$user->isEmailAuthEnabled()) {
            $token = $this->authTokenGenerator->generateSetupToken($user);
            $response = TwoFactorAuthResponse::setupRequired($token);
            $event->setData($response->toArray());
            return;
        }

        // 2. CHECK FOR TRUSTED DEVICE (Bypass 2FA)
        $request = $this->requestStack->getCurrentRequest();
        $rawCookie = $request?->cookies->get(AuthConstants::TRUSTED_DEVICE_COOKIE_NAME);

        if ($rawCookie) {
            $tokenHash = $this->trustTokenGenerator->hash($rawCookie);
            $device = $this->trustedDeviceRepository->findByHash($tokenHash);

            if ($device && !$device->isExpired() && $device->getUserId()->equals($user->getId())) {
                // The device is trusted!
                // Stop execution here. LexikJWT will keep its default payload: {"token": "eyJhbG..."}
                // They are now fully logged in.
                return;
            }
        }

        // 3. Fallback to 2FA Flow (Device is new, missing, or expired)
        if ($user->isEmailAuthEnabled()) {
            $user->generateAndSendEmailAuthCode();
            $this->userRepository->save($user);
            foreach ($user->pullDomainEvents() as $domainEvent) {
                $this->eventBus->publish($domainEvent);
            }
        }

        $preAuthToken = $this->authTokenGenerator->generatePendingToken($user);
        $providers = [
            $user->isGoogleAuthenticatorEnabled() ? 'google' : null,
            $user->isEmailAuthEnabled() ? 'email' : null,
        ];

        $response = TwoFactorAuthResponse::verificationRequired($preAuthToken, $providers);;

        $event->setData($response->toArray());
    }
}
