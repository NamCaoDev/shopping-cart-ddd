<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\User\Application\Command\VerifyBackupCodeCommand;
use Ecotone\Modelling\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api/2fa/verify-backup', methods: ['POST'])]
#[IsGranted('ROLE_2FA_PENDING')]
final class VerifyBackupCodeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly Security $security
    ) {}

    public function __invoke(
        #[MapRequestPayload] VerifyBackupCodeCommand $command
    ): JsonResponse
    {
        $userId = (string) $this->security->getUser()->getId();

        /** @var string $token */
        $token = $this->commandBus->send($command->withUserId($userId));

        return $this->json([
            'message' => 'Backup code accepted.',
            'token' => $token
        ]);

    }
}
