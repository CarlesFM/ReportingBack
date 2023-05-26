<?php

namespace App\Repository;


use App\Entity\Registro;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Mes;



/**
 * @method Registro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registro[]    findAll()
 * @method Registro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistroRepository extends ServiceEntityRepository
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, Registro::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Registro $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Registro $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addFromRequest($request, bool $flush = false): Registro
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $registro = new Registro();
        $registro = $this->setPropertiesIfFound($request, $registro);

        $this->_em->persist($registro);
        if ($flush) {
            $this->_em->flush();
        }

        return $registro;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editFromRequest($request, Registro $registro, bool $flush = false): Registro
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $this->setPropertiesIfFound($request, $registro);

        if ($flush) {
            $this->_em->flush();
        }

        return $registro;
    }

    private function setPropertiesIfFound($request, Registro $registro)
    {
        $request->get('entrada') === null ? $registro->setEntrada(null) : $registro->setEntrada(\DateTime::createFromFormat('H:i', $request->get('entrada')));
        $request->get('salida') === null ? $registro->setSalida(null) : $registro->setSalida(\DateTime::createFromFormat('H:i', $request->get('salida')));
        $request->get('almuerzoEntrada') === null ? $registro->setAlmuerzoEntrada(null) : $registro->setAlmuerzoEntrada(\DateTime::createFromFormat('H:i', $request->get('almuerzoEntrada')));
        $request->get('almuerzoSalida') === null ? $registro->setAlmuerzoSalida(null) : $registro->setAlmuerzoSalida(\DateTime::createFromFormat('H:i', $request->get('almuerzoSalida')));
        $request->get('comidaEntrada') === null ? $registro->setComidaEntrada(null) : $registro->setComidaEntrada(\DateTime::createFromFormat('H:i', $request->get('comidaEntrada')));
        $request->get('comidaSalida') === null ? $registro->setComidaSalida(null) : $registro->setComidaSalida(\DateTime::createFromFormat('H:i', $request->get('comidaSalida')));
        $request->get('dia') === null ? '' : $registro->setDia($request->get('dia'));

        if ($request->get('mes')) {
            $mesRepository = new MesRepository($this->registry);

            if (is_scalar($request->get('mes'))) {
                $mes = $mesRepository->find($request->get('mes'));
            }
            if (!isset($mes)) {
                $mes = $mesRepository->addFromRequest($request->get('mes'));
            }

            $registro->setMes($mes);
        }
        return $registro;
    }

    public function findByEmpleadoYFechas($empleado,  $fechaInicio, $fechaFin, $num_devoluciones, $num_pagina)
    {

        $sql = "SELECT registro.* FROM registro INNER JOIN mes ON mes.id = registro.mes_id WHERE empleado_id= $empleado AND mes.fecha >'$fechaInicio' AND mes.fecha<='$fechaFin' LIMIT $num_devoluciones OFFSET $num_pagina ;";


        $conn = $this->getEntityManager()
            ->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }


    public function findByEmpleado($empleado, $num_devoluciones, $num_pagina)
    {

        $sql = "SELECT registro.* FROM registro INNER JOIN mes ON mes.id = registro.mes_id WHERE empleado_id= $empleado LIMIT $num_devoluciones OFFSET $num_pagina ;";


        $conn = $this->getEntityManager()
            ->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }
    //    /**
    //     * @return Registro[] Returns an array of Registro objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Registro
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
