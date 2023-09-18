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

// Systeme de mise en cache
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;



class UserController extends AbstractController
{
    /**
     * ********************************* Retourne la liste de tous les Users ***********************************************
     * 
     * @Route("/api/users", name="app_user", methods={"GET"})
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function getAllUsers(Request $request, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        // Pagination ...
        // Récupérez les données du Body de la requête en JSON
        $requestData = json_decode($request->getContent(), true);

        // S'assurez-vous que les données de pagination sont présentes et valides
        if (!isset($requestData['page']) || !isset($requestData['limit'])) {
            return new JsonResponse(['error' => 'Invalid pagination data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Si les données de pagination sont présente en Body et valides, je les attribue au var $page et $limit
        $page = $requestData['page'];
        $limit = $requestData['limit'];



        // Mise en Cache ...
        // Systeme de mise en cache, créer un id qui represente la requete recu
        $idCache = "getAllUsers-" . $page . "-" . $limit;


        /*
        * Mettre en cache l'objet déja serializer, la liste sera recuperer directement part mon cache si existe, 
        * sinon utiliser la fonction anonyme passé en param
        *
        * Function anonyme : $item represente ce qui va etre stocker en cache
        */
        $jsonUsersList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            echo ("L'element vient d'etre mise en cache !\n");
            
            // Attribuer le tag usersCache, qui permettra par la suite de savoir quel tag supprimer pour reset le cache
            $item->tag("usersCache");

            // Recuperer mes users en passant par la nouvelle methode de pagination
            $usersList = $userRepository->findAllWithPagination($page, $limit);

            // Convertir grace au serializer ma usersList en json et stocker le resultat et indiqué que je veux le group getUsers
            return $serializer->serialize($usersList, 'json', ['groups' => 'getUsers']);
        });


  

        /* Retourne la liste convertit en json, la response, les headers part defaut, 
        * et true qui indique au jsonresponse que les données sont déja convertis
        */
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }



    /**
     * ************************************** Retourne un user selon son id *******************************************
     * 
     * @Route("/api/users/{id}", name="app_detailUser", methods={"GET"})
     * 
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
    */
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }




    /**
     * ************************************** Retourne la liste des users selon l'id du client donnée *******************************************
     * 
     * @Route("/api/clients/{id}/users", name="app_client_users", methods={"GET"})
     * 
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return JsonResponse
    */
    public function getUsersListByClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $UsersListByClient = $serializer->serialize($client, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($UsersListByClient, Response::HTTP_OK, [], true);

    }




    /**
     * ************************************** Ajouter un utilisateur liés a un client *******************************************
     * 
     * @Route("/api/user", name="app_createUser", methods={"POST"})
     * 
     * @param ClientRepository $clientRepository
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
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



    /**
     * *********************************** Supprimer un User selon son id ****************************************
     * 
     * @Route("/api/users/{id}", name="app_deleteUser", methods={"DELETE"})
     * 
     * @param User $user
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        /*
        * Utiliser le tag du cache mis en place en GET Phones pour supprimer le cache lors du DELETE afin que le cache soit recalculer 
        * lors du GET et afin de garantir en permanence que nos données en cache sont Ok avec la realite
        */
        $cache->invalidateTags(["usersCache"]);

        // Supprimer le user en question
        $em->remove($user);
        // Confirmer
        $em->flush();
        // Retourner la reponse 204, car c'est un succé, mais no content car il n'y a plus de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
}
