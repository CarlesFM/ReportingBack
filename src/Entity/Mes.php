<?php

namespace App\Entity;

use App\Repository\MesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;


/**
 * Common\Model\Entity\Mes
 *
 * @ORM\Table(name="mes", 
 *    uniqueConstraints={
 *        @UniqueConstraint(name="mes_unique", 
 *            columns={"empleado_id", "fecha"})
 *    }
 * )
 * @ORM\Entity
 */


#[ORM\Entity(repositoryClass: MesRepository::class)]
class Mes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\OneToMany(mappedBy: 'mes', targetEntity: Registro::class)]
    private Collection $registros;

    #[ORM\ManyToOne(inversedBy: 'mes')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Empleado $empleado = null;

    public function __construct()
    {
        $this->registros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return Collection<int, Registro>
     */
    public function getRegistros(): Collection
    {
        return $this->registros;
    }

    public function addRegistro(Registro $registro): self
    {
        if (!$this->registros->contains($registro)) {
            $this->registros->add($registro);
            $registro->setMes($this);
        }

        return $this;
    }

    public function removeRegistro(Registro $registro): self
    {
        if ($this->registros->removeElement($registro)) {
            // set the owning side to null (unless already changed)
            if ($registro->getMes() === $this) {
                $registro->setMes(null);
            }
        }

        return $this;
    }

    public function getEmpleado(): ?Empleado
    {
        return $this->empleado;
    }

    public function setEmpleado(?Empleado $empleado): self
    {
        $this->empleado = $empleado;

        return $this;
    }
}
