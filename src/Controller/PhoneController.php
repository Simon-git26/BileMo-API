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

class PhoneController extends AbstractController
{
    /**
     * @Route("/api/phones", name="app_phone", methods={"GET"})
     * 
     * ********************* Retourne la liste de tous les phones ******************************
    */
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
        // Recuperer tous mes phones
        $phonesList = $phoneRepository->findAll();

        // Convertir grace au serializer ma phonesList en json et stocker le resultat
        $jsonPhonesList = $serializer->serialize($phonesList, 'json');

        /* Retourne la liste convertit en json, la response, les headers part defaut, 
        * et true qui indique au jsonresponse que les données sont déja convertis
        */
        return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);
    }



    /**
     * @Route("/api/phones/{id}", name="app_detailPhone", methods={"GET"})
     * 
     * ************************** Retourne un phone selon son id **********************************
     * Utilisation du ParamConverter de Symfony afin d'envoyer la bonne entité correspondante
     * Renvoi un 200 en succés, et un 404 quand il ne trouve pas l'entité correspondant à l'id
    */
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }



    /**
     * @Route("/api/phone", name="app_createPhone", methods={"POST"})
     * 
     * **************************  Créer un Phone **********************************
     * Request permet de récupéré les infos que j"ai envoyé en Body de la requete
    */
    public function createPhone(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        // Récupéré les données posté, et les déserializer dans un objet Phone, donc $phone contiendra un véritable phone
        $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');
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
     * @Route("/api/phones/{id}", name="app_deletePhone", methods={"DELETE"})
     * 
     * ************************** Supprimer un Phone selon son id **********************************
    */
    public function deletePhone(Phone $phone, EntityManagerInterface $em): JsonResponse
    {
        // Supprimer le phone en question
        $em->remove($phone);
        // Confirmer
        $em->flush();
        // Retourner la reponse 204, car c'est un succé, mais no content car il n'y a plus de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
