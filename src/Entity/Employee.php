<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\User;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeRepository")
 */
class Employee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message = "'{{ value }}' n'est pas un email valide."
     * )
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $poste;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(separator="-", updatable=true, fields={"prenom", "nom", "poste"}, unique=true, style="lowercase")
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="employees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $telephone;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(string $poste): self
    {
        $this->poste = $poste;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }


    /**
     * @return \App\Entity\User|ArrayCollection|null
     */
    public function getUser()
    {
        return $this->user;

    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }
}
