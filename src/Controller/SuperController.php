<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\ItemInterface;

class SuperController extends AbstractController
{
    // Fonction pour gerer mon cache, pour l'instant utilisée en getAllPhone et getAllUsers
    protected function getItemsListWithCache(
        $repository, // variable générique (UserRepository ou PhoneRepository)
        $serializer,
        $cache,
        $page, // Le param page
        $limit, // Le param limit
        $cacheKey, // getAllPhones ou getAllUsers
        $controller, // user ou phone
        $itemCache // le tag du cache
    ) {
        $idCache = $cacheKey . "-" . $page . "-" . $limit;

        return $cache->get($idCache, function (ItemInterface $item) use ($repository, $page, $limit, $controller, $itemCache, $serializer) {
            echo ("L'élément vient d'être mis en cache !\n");

            $item->tag($itemCache);

            $itemsList = $repository->findAllWithPagination($page, $limit);

            // Si on vien du userController on ajoute le getUsers group, sinon non
            $groups = ($controller == "user") ? ['groups' => 'getUsers'] : [];

            return $serializer->serialize($itemsList, 'json', $groups);
            
        });
    }
}