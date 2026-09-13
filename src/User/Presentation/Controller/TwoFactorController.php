<?php

namespace App\User\Presentation\Controller;

use App\User\Application\Command\VerifyTwoFactorCode;
use App\User\Application\Dto\VerifyTwoFactorCodeResult;
use App\User\Domain\Entity\User;
use App\User\Presentation\Response\AuthenticationTokenResponse;
use App\User\Presentation\Security\AuthConstants;
use Ecotone\Modelling\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TwoFactorController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {}

    #[Route('/api/2fa/verify', name: 'api_2fa_verify', methods: ['POST'])]
    public function verify(
        #[MapRequestPayload] VerifyTwoFactorCode $command,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser(); // User extracted from pre_auth_token

        /** @var VerifyTwoFactorCodeResult $result */
        $result = $this->commandBus->send($command->withUserId($user->getId()));

        $response = $this->json(new AuthenticationTokenResponse($result->jwt));

        if (null !== $result->trustedToken) {
            $response->headers->setCookie(
                Cookie::create(AuthConstants::TRUSTED_DEVICE_COOKIE_NAME)
                    ->withValue($result->trustedToken)
                    ->withExpires((new \DateTimeImmutable())->modify('+30 days'))
                    ->withSecure()
                    ->withHttpOnly()
                    ->withSameSite('Strict')
            );
        }

        return $response;
    }
}
