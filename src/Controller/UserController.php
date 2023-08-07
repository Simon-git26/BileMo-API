<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="app_user")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Bienvenue sur le controller user!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }
}
