<?php

namespace App\Entity;

use App\Repository\EmpresasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpresasRepository::class)]
class Empresas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $cif = null;

    #[ORM\OneToMany(mappedBy: 'empresas', targetEntity: Empleado::class)]
    private $empleado;

    public function __construct()
    {
        $this->empleado = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(string $cif): self
    {
        $this->cif = $cif;

        return $this;
    }

    /**
     * @return Collection<int, Empleado>
     */
    public function getEmpleado(): Collection
    {
        return $this->empleado;
    }

    public function addEmpleado(Empleado $empleado): self
    {
        if (!$this->empleado->contains($empleado)) {
            $this->empleado[] = $empleado;
            $empleado->setEmpresas($this);
        }

        return $this;
    }

    public function removeEmpleado(Empleado $empleado): self
    {
        if ($this->empleado->removeElement($empleado)) {
            // set the owning side to null (unless already changed)
            if ($empleado->getEmpresas() === $this) {
                $empleado->setEmpresas(null);
            }
        }

        return $this;
    }
}
