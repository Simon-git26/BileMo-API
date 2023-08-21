<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// Importer l'entite pour le param converter
use App\Entity\User;
use App\Entity\Client;
// Serializer
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="app_user", methods={"GET"})
     * 
     * ********************************** Retourne la liste de tous les Users et leur client associé ***********************************************
    */
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        // Recuperer tous mes users
        $usersList = $userRepository->findAll();

        // Convertir grace au serializer ma usersList en json et stocker le resultat et indiqué que je veux le group getUsers
        $jsonUsersList = $serializer->serialize($usersList, 'json', ['groups' => 'getUsers']);

        /* Retourne la liste convertit en json, la response, les headers part defaut, 
        * et true qui indique au jsonresponse que les données sont déja convertis
        */
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }



    /**
     * @Route("/api/users/{id}", name="app_detailUser", methods={"GET"})
     * 
     * ************************************** Retourne un user selon son id *******************************************
     * 
    */
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }




     /**
     * @Route("/api/clients/{id}/users", name="app_client_users", methods={"GET"})
     * 
     * ************************************** Retourne la liste des users selon l'id du client donnée *******************************************
     * 
    */
    public function getUsersListByClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $UsersListByClient = $serializer->serialize($client, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($UsersListByClient, Response::HTTP_OK, [], true);

    }
}
