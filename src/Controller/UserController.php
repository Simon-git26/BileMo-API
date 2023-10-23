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
use Symfony\Component\HttpKernel\Exception\HttpException;
// Serializer
use Symfony\Component\Serializer\SerializerInterface;

// Systeme de mise en cache
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

// Utilisation de la validation
use Symfony\Component\Validator\Validator\ValidatorInterface;

// Import de isGranted pour verifier le role du user connecte
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


// Doc Nelmio
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class UserController extends SuperController
{


    /**
     * Retourne la liste de tous les utilisateurs avec pagination.
     * 
     * @Route("/api/users", name="app_user", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste de tous les utilisateurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Numéro de page",
     *     @OA\Schema(type="integer", example=1)
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Nombre d'éléments par page",
     *     @OA\Schema(type="integer", example=4)
     * )
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function getAllUsers(Request $request, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 4);

        $jsonItemsList = $this->getItemsListWithCache($userRepository, $serializer, $cache, $page, $limit, "getAllUsers", "user", "tagUser");

        return new JsonResponse($jsonItemsList, Response::HTTP_OK, [], true);
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
    public function createUser(ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Récupéré les données posté, et les déserializer dans un objet User
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');


        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idClient. S'il n'est pas défini, renvoyer une erreur.
        if ($content['id']) {
            $idClient = $content['id'];
        } else {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requetes est invalide !");
        }

        // On cherche le client qui correspond et on l'assigne au user.

        // Si "find" ne trouve pas le client, alors je renvoi une erreur.

        // Cherchez le client correspondant
        $client = $clientRepository->find($idClient);

        // Vérifiez si le client n'a pas été trouvé
        if ($client === null) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide, le client n'est pas trouvé !");
        }

        // Assigner le client au user seulement s'il a été trouvé
        $user->setClient($client);

       


        // Avant d'enregistrer mon user, je verifie si celui ci est valide grace au Constraintes de validation utilisée en Entity.
        // Verifie les erreurs
        // Demande au validator de valider l'entity puser, le resultat est stocké dans $errors
        $errors = $validator->validate($user);

        //Si errors présente, alors retourner une JsonResponse avec l'erros sérialisée et retour d"une bad reponse http
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

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
     * @IsGranted("ROLE_ADMIN")
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
        $cache->invalidateTags(["tagUser"]);

        // Supprimer le user en question
        $em->remove($user);
        // Confirmer
        $em->flush();
        // Retourner la reponse 204, car c'est un succé, mais no content car il n'y a plus de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
}
