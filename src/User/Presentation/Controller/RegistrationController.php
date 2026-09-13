<?php

namespace App\User\Presentation\Controller;

use App\Shared\Presentation\Response\ResourceCreatedResponse;
use App\User\Application\Command\RegisterUser;
use Ecotone\Modelling\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterUser $command
    ): JsonResponse
    {
        $command = $command->withId(Uuid::v7()->toRfc4122());

        // Ecotone will find the User::register() method, create the object, and automatically save it via Doctrine!
        $this->commandBus->send($command);

        return $this->json(
            new ResourceCreatedResponse(
                id: $command->id,
                message: 'User created successfully!' // Optional override
            ),
            Response::HTTP_CREATED // HTTP 201
        );
    }
}
