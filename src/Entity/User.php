<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
// Utilisation des Groups
use Symfony\Component\Serializer\Annotation\Groups;
// Ajout de la Validation
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getUsers"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     * @Groups({"getUsers"})
     * 
     *
     * @Assert\NotBlank(
     *      message = "Un champ est manquant !"
     * )
     * 
     * @Assert\Length(
     *      min = 1, 
     *      max = 180, 
     *      minMessage = "Le nom doit faire au moins {{ limit }} caractères", 
     *      maxMessage = "Le nom ne peut pas faire plus de {{ limit }} caractères"
     * )
     * 
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * 
     */
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
