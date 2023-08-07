<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PhoneController extends AbstractController
{
    /**
     * @Route("/phone", name="app_phone")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Bienvenu sur le controller phone!',
            'path' => 'src/Controller/PhoneController.php',
        ]);
    }
}
