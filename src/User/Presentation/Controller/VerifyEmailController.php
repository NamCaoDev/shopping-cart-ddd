<?php

namespace App\User\Presentation\Controller;

use App\Shared\Presentation\Response\ApiMessageResponse;
use App\User\Application\Command\VerifyEmail;
use Ecotone\Modelling\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class VerifyEmailController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus) {}

    #[Route(
        '/api/users/{userId}/verify',
        name: 'api_verify_email',
        requirements: ['userId' => Requirement::UUID],
        methods: ['POST']
    )]
    public function verify(
        string $userId,
        #[MapRequestPayload] VerifyEmail $command
    ): JsonResponse {
        $this->commandBus->send($command->withUserId($userId));

        return $this->json(new ApiMessageResponse('Email verified successfully!'));
    }
}
