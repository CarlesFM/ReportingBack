<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\EmpleadoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpleadoRepository::class)]
class Empleado implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "json")]
    private $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellidos = null;

    #[ORM\Column(type: 'string', length: 9)]
    private $dni;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $correo = null;

    #[ORM\OneToMany(mappedBy: 'empleado', targetEntity: Mes::class)]
    private Collection $mes;

    #[ORM\ManyToOne(targetEntity: Empresas::class, inversedBy: 'empleado')]
    private $empresas;

    public function __construct()
    {
        $this->mes = new ArrayCollection();
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(?string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    /**
     * @return Collection<int, Mes>
     */
    public function getMes(): Collection
    {
        return $this->mes;
    }

    public function addMe(Mes $me): self
    {
        if (!$this->mes->contains($me)) {
            $this->mes->add($me);
            $me->setEmpleado($this);
        }

        return $this;
    }

    public function removeMe(Mes $me): self
    {
        if ($this->mes->removeElement($me)) {
            // set the owning side to null (unless already changed)
            if ($me->getEmpleado() === $this) {
                $me->setEmpleado(null);
            }
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->correo;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /* 
    * Returning a salt is only needed, if you are not using a modern
    * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
    *
    * @see UserInterface
    */
    public function getSalt(): ?string
    {
        return null;
    }

    /*
    * @deprecated since Symfony 5.3, use getUserIdentifier instead
    */
    public function getUsername(): string
    {
        return (string) $this->correo;
    }

    public function addRole(string $rol): self
    {
        //recojo todos los roles
        $roles = $this->roles;

        if (!in_array($rol, $roles)) {
            $this->roles[] = $rol;
        }

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    public function getEmpresas(): ?Empresas
    {
        return $this->empresas;
    }

    public function setEmpresas(?Empresas $empresas): self
    {
        $this->empresas = $empresas;

        return $this;
    }
}
