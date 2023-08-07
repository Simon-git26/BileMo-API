<?php

namespace App\DataFixtures;

// Importer ma class Phone
use App\Entity\Phone;
use App\Entity\Client;
use App\Entity\User;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // Recuperer l'outil pour hasher les mdp
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // create 20 products! Bam!
        for ($i = 0; $i < 20; $i++) {
            $product = new Phone();
            $product->setBrand('Samsung');
            $product->setModel('S '.$i);
            $product->setColor('rouge '.$i);
            $product->setPrice(mt_rand(10, 100));
            $product->setDescription('description '.$i);
            $product->setStorage(64);

            $manager->persist($product);
        }

        // Creation de mes clients
        $this->setClient($manager);

        $manager->flush();
    }

    private function setClient(ObjectManager $manager) {
        
        // Creation d'un utilisateur role user
        $client = new Client();
        $client->setEmail('user@bilemo.com');
        $client->setUsername('SimonUser');
        $client->setRoles(['ROLES_USER']);
        $client->setPassword($this->userPasswordHasher->hashPassword($client, "password"));

        $manager->persist($client);


        // Creation d'un utilisateur role admin
        $clientAdmin = new Client();
        $clientAdmin->setEmail('admin@bilemo.com');
        $clientAdmin->setUsername('SimonAdmin');
        $clientAdmin->setRoles(['ROLES_ADMIN']);
        $clientAdmin->setPassword($this->userPasswordHasher->hashPassword($clientAdmin, "password"));

        $manager->persist($clientAdmin);




        // Creation de mes users associés au clients
        
        // Initialiser mes user
        $user = new User();

        $user->setName('SimonUser');
        $user->setClient($client);
        $manager->persist($user);



        $user2 = new User();

        $user2->setName('SimonAdmin');
        $user2->setClient($clientAdmin);
        $manager->persist($user2);
        
            
        

        $manager->flush();
    }
}
