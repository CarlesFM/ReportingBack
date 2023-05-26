<?php

namespace App\Entity;

use App\Repository\RegistroRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\Mapping\UniqueConstraint;


/**
 * Common\Model\Entity\Registro
 *
 * @ORM\Table(name="registro", 
 *    uniqueConstraints={
 *        @UniqueConstraint(name="registro_unique", 
 *            columns={"mes_id"})
 *    }
 * )
 * @ORM\Entity
 */
#[ORM\Entity(repositoryClass: RegistroRepository::class)]
class Registro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: 'integer')]
    private $dia;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $entrada = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $salida = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $almuerzoEntrada = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $almuerzoSalida = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $comidaEntrada = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private  $comidaSalida = null;

    #[ORM\ManyToOne(inversedBy: 'registros')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Mes $mes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntrada()
    {
        return $this->entrada;
    }

    public function setEntrada($entrada): self
    {
        $this->entrada = $entrada;

        return $this;
    }

    public function getSalida()
    {
        return $this->salida;
    }

    public function setSalida($salida): self
    {
        $this->salida = $salida;

        return $this;
    }

    public function getAlmuerzoEntrada()
    {
        return $this->almuerzoEntrada;
    }

    public function setAlmuerzoEntrada($almuerzoEntrada): self
    {
        $this->almuerzoEntrada = $almuerzoEntrada;

        return $this;
    }

    public function getAlmuerzoSalida()
    {
        return $this->almuerzoSalida;
    }

    public function setAlmuerzoSalida($almuerzoSalida): self
    {
        $this->almuerzoSalida = $almuerzoSalida;

        return $this;
    }

    public function getComidaEntrada()
    {
        return $this->comidaEntrada;
    }

    public function setComidaEntrada($comidaEntrada): self
    {
        $this->comidaEntrada = $comidaEntrada;

        return $this;
    }

    public function getComidaSalida()
    {
        return $this->comidaSalida;
    }

    public function setComidaSalida($comidaSalida): self
    {
        $this->comidaSalida = $comidaSalida;

        return $this;
    }

    public function getMes(): ?Mes
    {
        return $this->mes;
    }

    public function setMes(?Mes $mes): self
    {
        $this->mes = $mes;

        return $this;
    }

    public function getDia(): ?int
    {
        return $this->dia;
    }

    public function setDia(int $dia): self
    {
        $this->dia = $dia;

        return $this;
    }
}
