<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
// Ajout de la Validation
use Symfony\Component\Validator\Constraints as Assert;

/* Validation mise en place : 
* Validation sur le non null avec message d'un champs obligatoire pour les varchar
* Validation sur la taille mini et max pour les varchar
* Validation pour un nombre positif sur les champs INT
*/

/**
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(
     *      message = "La marque du produit est obligatoire !"
     * )
     * 
     * @Assert\Length(
     *      min = 1, 
     *      max = 255, 
     *      minMessage = "La marque doit faire au moins {{ limit }} caractères", 
     *      maxMessage = "La marque ne peut pas faire plus de {{ limit }} caractères"
     * )
     * 
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(
     *      message = "La model du produit est obligatoire !"
     * )
     * 
     * @Assert\Length(
     *      min = 1, 
     *      max = 255, 
     *      minMessage = "Le model doit faire au moins {{ limit }} caractères", 
     *      maxMessage = "Le model ne peut pas faire plus de {{ limit }} caractères"
     * )
     * 
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=40)
     * 
     *
     * @Assert\NotBlank(
     *      message = "La couleur du produit est obligatoire !"
     * )
     * 
     * @Assert\Length(
     *      min = 1, 
     *      max = 255, 
     *      minMessage = "La couleur doit faire au moins {{ limit }} caractères", 
     *      maxMessage = "La couleur ne peut pas faire plus de {{ limit }} caractères"
     * )
     * 
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     * 
     * @Assert\Positive(message = "Le prix doit être supérieur à 0 !")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     *
     * @Assert\NotBlank(
     *      message = "La description du produit est obligatoire !"
     * )
     * 
     * @Assert\Length(
     *      min = 1, 
     *      max = 255, 
     *      minMessage = "La description doit faire au moins {{ limit }} caractères", 
     *      maxMessage = "La description ne peut pas faire plus de {{ limit }} caractères"
     * )
     * 
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * 
     * @Assert\Positive(message = "Le stockage doit être supérieur à 0 !")
     */
    private $storage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStorage(): ?int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): self
    {
        $this->storage = $storage;

        return $this;
    }
}
