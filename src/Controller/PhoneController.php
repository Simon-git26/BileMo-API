<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PhoneController extends AbstractController
{
    /**
     * @Route("/api/phones", name="app_phone")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Bienvenu sur le controller phone pour récuperer la liste de tous les téléphones!',
            'path' => 'src/Controller/PhoneController.php',
        ]);
    }
}
