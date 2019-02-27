<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiResource
 * @Vich\Uploadable
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(
     *     message = "'{{ value }}' n'est pas un email valide."
     * )
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];
    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     */
    private $nom_entreprise;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $code_postal;
  
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $site_web;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $social;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Employee", mappedBy="user", orphanRemoval=true)
     */
    private $employees;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex(
     *     pattern="/[0-9]{10}/"
     * )
     */
    private $telephone;

    /**
     * @Vich\UploadableField(mapping="logo", fileNameProperty="logoName")
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoName;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getLogoName()
    {
        return $this->logoName;
    }

    /**
     * @param mixed $logoName
     */
    public function setLogoName($logoName)
    {
        $this->logoName = $logoName;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     *  @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        if (null !== $logo) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getNomEntreprise(): ?string
    {
        return $this->nom_entreprise;
    }

    public function setNomEntreprise(string $nom_entreprise): self
    {
        $this->nom_entreprise = $nom_entreprise;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $telephone
     * @return User
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodePostal()
    {
        return $this->code_postal;
    }

    /**
     * @param mixed $code_postal
     */
    public function setCodePostal($code_postal): self
    {
        $this->code_postal = $code_postal;
        return $this;
    }
  
    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteWeb(string $site_web): self
    {
        $this->site_web = $site_web;

        return $this;
    }

    public function getSocial(): ?string
    {
        return $this->social;
    }

    public function setSocial(?string $social): self
    {
        $this->social = $social;

        return $this;
    }

    /**
     * @return Collection|Employee[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): self
    {
        if (!$this->employees->contains($employee)) {
            $this->employees[] = $employee;
            $employee->setUser($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): self
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            // set the owning side to null (unless already changed)
            if ($employee->getUser() === $this) {
                $employee->setUser(null);
            }
        }

        return $this;
    }
}
