<?php

// src/User/Presentation/Controller/GenerateBackupCodesController.php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\User\Application\Command\GenerateBackupCodesCommand;
use Ecotone\Modelling\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa/backup-codes', name: 'api_2fa_generate_backup_codes', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class GenerateBackupCodesController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly Security $security
    ) {}

    public function __invoke(): JsonResponse
    {
        // Assuming your Symfony UserInterface implementation uses string IDs
        $userId = (string) $this->security->getUser()->getId();

        /** @var array<string> $plainCodes */
        $plainCodes = $this->commandBus->send(new GenerateBackupCodesCommand($userId));

        return $this->json([
            'message' => 'Backup codes generated. Store them safely—they will not be shown again.',
            'codes' => $plainCodes,
        ]);
    }
}
