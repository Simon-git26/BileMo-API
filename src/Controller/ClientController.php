<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
// Serializer
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
  
    /**
     * @Route("/api/clients", name="app_client", methods={"GET"})
     * 
     * ********************************** Retourne la liste de tous les Clients et leur Users liés ***********************************************
    */
    public function getAllClients(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        // Recuperer tous mes clients
        $clientsList = $clientRepository->findAll();

        // Convertir grace au serializer ma clientsList en json et stocker le resultat et indiqué que je veux le group getUsers
        $jsonClientsList = $serializer->serialize($clientsList, 'json', ['groups' => 'getUsers']);

        /* Retourne la liste convertit en json, la response, les headers part defaut, 
        * et true qui indique au jsonresponse que les données sont déja convertis
        */
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }
}
