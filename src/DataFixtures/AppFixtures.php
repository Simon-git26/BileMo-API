<?php

namespace App\DataFixtures;

// Importer ma class Phone
use App\Entity\Phone;
use App\Entity\Client;
use App\Entity\User;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create 20 products! Bam!
        for ($i = 0; $i < 10; $i++) {
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
        for ($i = 1; $i <= 2; $i++) {
            // Instanciations de ma class Trick
            $client = new Client();
            $client->setUsername('Simon'.$i);
            $client->setEmail('simoncestmoi@hotmail.fr'.$i);
            $client->setPassword('toto'.$i);
            $client->setRoles(['ROLES_USER']);
       
            $manager->persist($client);


            // Creation de mes users associés au clients
            for ($j = 1; $j <= 2; $j++) {
                // Initialiser mon user
                $user = new User();

                $user->setName('toto'.$i);
                $user->setClient($client);

                $manager->persist($user);
            }
            
        }

        $manager->flush();
    }
}
