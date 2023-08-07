<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    /**
     * @Route("/client", name="app_client")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Bienvenue sur le controller client!',
            'path' => 'src/Controller/ClientController.php',
        ]);
    }
}
