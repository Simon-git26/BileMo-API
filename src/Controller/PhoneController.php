<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
// Serializer
use Symfony\Component\Serializer\SerializerInterface;
// Importer l'entite pour le param converter
use App\Entity\Phone;
// Utiliser pour la suppression
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
// Url Generator en cas de POST Phone
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// Pour la modification d'un phone
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
// Systeme de mise en cache
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
// Utilisation de la validation
use Symfony\Component\Validator\Validator\ValidatorInterface;


// Doc Nelmio
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;



class PhoneController extends SuperController
{
    /**
     * Retourne la liste des phones avec pagination.
     * 
     * @Route("/api/phones", name="app_phone", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des phones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class))
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
     *     @OA\Schema(type="integer", example=3)
     * )
     * 
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function getAllPhones(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 4);

        $jsonItemsList = $this->getItemsListWithCache($phoneRepository, $serializer, $cache, $page, $limit, "getAllPhones", "phone", "tagPhone");

        return new JsonResponse($jsonItemsList, Response::HTTP_OK, [], true);
    }




    /**
     * ************************************** Retourne un phone selon son id *******************************************
     * 
     * @Route("/api/phones/{id}", name="app_detailPhone", methods={"GET"})
     * 
     * Utilisation du ParamConverter de Symfony afin d'envoyer la bonne entité correspondante
     * Renvoi un 200 en succés, et un 404 quand il ne trouve pas l'entité correspondant à l'id
     * 
     * @param Phone $phone
     * @param SerializerInterface $serializer
     * @return JsonResponse
    */
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }



    /**
     * *********************************************  Créer un Phone *************************************************
     * 
     * @Route("/api/phone", name="app_createPhone", methods={"POST"})
     * 
     * Request permet de récupéré les infos que j"ai envoyé en Body de la requete
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
    */
    public function createPhone(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Récupéré les données posté, et les déserializer dans un objet Phone, donc $phone contiendra un véritable phone
        $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');


        // Avant d'enregistrer mon phone, je verifie si celui ci est valide grace au Constraintes de validation utilisée en Entity.
        // Verifie les erreurs
        // Demande au validator de valider l'entity phone, le resultat est stocké dans $errors
        $errors = $validator->validate($phone);

        //Si errors présente, alors retourner une JsonResponse avec l'erros sérialisée et retour d"une bad reponse http
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }


        // Enregistrer et Confirmer
        $em->persist($phone);
        $em->flush();

        $jsonPhone = $serializer->serialize($phone, 'json');


        /* Calculer l'url pour pouvoir indiquez l'url du Phone que l"on vient de créer en header pour pouvoir aller tester rapidement 
        *  Si la création c'est bien passé en récupérant mon nouveau phone selon son id par exemple
        */
        $location = $urlGenerator->generate('app_detailPhone', ['id' => $phone->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Code 201 pour la création
        // Retourner dans le headers de la reponse que j'ai besoin de mon nouvel element location
        return new JsonResponse($jsonPhone, Response::HTTP_CREATED, ["Location" => $location], true);
    }



    /**
     * *********************************** Supprimer un Phone selon son id ****************************************
     * 
     * @Route("/api/phones/{id}", name="app_deletePhone", methods={"DELETE"})
     * 
     * @param Phone $phone
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function deletePhone(Phone $phone, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        /*
        * Utiliser le tag du cache mis en place en GET Phones pour supprimer le cache lors du DELETE afin que le cache soit recalculer 
        * lors du GET et afin de garantir en permanence que nos données en cache sont Ok avec la realite
        */
        $cache->invalidateTags(["tagPhone"]);

        // Supprimer le phone en question
        $em->remove($phone);
        // Confirmer
        $em->flush();
        // Retourner la reponse 204, car c'est un succé, mais no content car il n'y a plus de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }




    /**
     * *********************************************  Modifier un Phone *************************************************
     * 
     * @Route("/api/phones/{id}", name="app_updatePhone", methods={"PUT"})
     * 
     * CurrentPhone contient l'element qui va correspondre a l'id en base de donnée, Avant d'avoir eu les nouvelles infos du PUT
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Phone $currentPhone
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
    */
    public function updatePhone(Request $request, SerializerInterface $serializer, Phone $currentPhone, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse 
    {
        /*
        * Utiliser le tag du cache mis en place en GET Phones pour supprimer le cache lors du DELETE afin que le cache soit recalculer 
        * lors du GET et afin de garantir en permanence que nos données en cache sont Ok avec la realite
        */
        $cache->invalidateTags(["tagPhone"]);

        // Grace à AbstractNormalizer, je deserialize directement à l'interieur de currentPhone
        $updatedPhone = $serializer->deserialize($request->getContent(), 
            Phone::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentPhone])
        ;
     
        $em->persist($updatedPhone);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}
