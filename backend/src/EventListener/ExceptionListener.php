<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = null;

        if ($exception instanceof ValidationException) {
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'status' => $exception->getStatusCode(),
            ], $exception->getStatusCode());
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = 'Internal server error';

            if (Environment === 'dev') {
                $message = $exception->getMessage();
            }

            $response = new JsonResponse([
                'error' => $message,
            ], $statusCode);
        }

        $event->setResponse($response);
    }
}
