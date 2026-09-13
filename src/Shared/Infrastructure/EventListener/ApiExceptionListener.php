<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Presentation\Response\ApiMessageResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Standardize Domain Exceptions as HTTP 400 Bad Request
        if ($exception instanceof \DomainException) {
            $response = new JsonResponse(
                new ApiMessageResponse($exception->getMessage()),
                Response::HTTP_BAD_REQUEST
            );

            $event->setResponse($response);
        }
    }
}
