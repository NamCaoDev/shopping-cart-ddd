<?php

namespace App\User\Presentation\Controller;

use App\Shared\Presentation\Response\ApiMessageResponse;
use App\User\Application\Command\ConfirmGoogleAuthSetup;
use App\User\Application\Command\EnableEmailAuth;
use App\User\Application\DTO\TwoFactorSecretResult;
use App\User\Application\Query\Generate2faSecret;
use App\User\Domain\Entity\User;
use App\User\Presentation\Dto\TwoFactorAuthResponse;
use App\User\Presentation\Security\AuthConstants;
use Ecotone\Modelling\CommandBus;
use Ecotone\Modelling\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TwoFactorSetupController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {}

    #[Route('/email/enable', methods: ['POST'])]
    public function enableEmail(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->commandBus->send(new EnableEmailAuth($user->getId()));

        return $this->json(new ApiMessageResponse('Email authentication enabled successfully'));
    }

    #[Route('/google/generate', methods: ['GET'])]
    public function generateGoogleSecret(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var TwoFactorSecretResult $result */
        $result = $this->queryBus->send(new Generate2faSecret($user->getId()));

        return $this->json($result);
    }

    #[Route('/google/confirm', methods: ['POST'])]
    public function confirmGoogleSetup(
        #[MapRequestPayload] ConfirmGoogleAuthSetup $command
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->commandBus->send($command->withUserId($user->getId()));

        return $this->json(new ApiMessageResponse('Google Authenticator setup confirmed and enabled.'));
    }
}
