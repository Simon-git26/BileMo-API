<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        // Recuperer l'exception lié à l'evenement
        $exception = $event->getThrowable();

        // Si exception est une instance de HttpException
        if ($exception instanceof HttpException) {
            // Si oui, créer un objet data → mettre un status → code de l'erreur et le message de l'erreur
            
            $statusCode = $exception->getStatusCode();

            if ( $statusCode == 404) {
                $data = [
                    'status' =>  $statusCode,
                    'message' => 'ressource non trouvée'
                ];
            } else if ( $statusCode == 403) {
                $data = [
                    'status' =>  $statusCode,
                    'message' => 'accès refusé'
                ];
            } else {
                $data = [
                    'status' =>  $statusCode,
                    'message' => $exception->getMessage()
                ];
            }
            // Remplacer la reponse de levenement pour lui mettre une nouvelle JsonResponse
            $event->setResponse(new JsonResponse($data));
      } else {
            // Si ce n'est pas une instance alors quelque chose s'est mal passé donc code 500 serveur
            $data = [
                'status' => 500, // Le status n'existe pas car ce n'est pas une exception HTTP, donc on met 500 par défaut.
                // 'message' => $exception->getMessage()
                'message' => "Une erreur serveur est survenue"
            ];

            $event->setResponse(new JsonResponse($data));
      }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
