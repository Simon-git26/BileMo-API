<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


// Utiliser pour la suppression
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
// Url Generator en cas de POST Phone
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


// Importer l'entite pour le param converter
use App\Entity\User;
use App\Entity\Client;
// Serializer
use Symfony\Component\Serializer\SerializerInterface;
//use Symfony\Component\Serializer\SerializationContext;
//use JMS\Serializer\SerializationContext;



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






    /**
     * @Route("/api/user", name="app_createUser", methods={"POST"})
     * 
     * ************************************** Ajouter un utilisateur liés a un client *******************************************
     * 
    */
    public function createUser(ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        // Récupéré les données posté, et les déserializer dans un objet User
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');


        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idAuthor. S'il n'est pas défini, alors on met -1 par défaut.
        $idClient = $content['id'] ?? -1;

        // On cherche l'auteur qui correspond et on l'assigne au livre.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        
        $user->setClient($clientRepository->find($idClient));

        $em->persist($user);
        $em->flush();








        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
      

        /* Calculer l'url pour pouvoir indiquez l'url du User que l"on vient de créer en header pour pouvoir aller tester rapidement 
        *  Si la création c'est bien passé en récupérant mon nouveau user selon son id par exemple
        */
        $location = $urlGenerator->generate('app_detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Code 201 pour la création
        // Retourner dans le headers de la reponse que j'ai besoin de mon nouvel element location
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);

    }

    
}
