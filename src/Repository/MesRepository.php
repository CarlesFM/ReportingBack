<?php

namespace App\Repository;

use App\Entity\Mes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Empleado;


/**
 * @method Mes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mes[]    findAll()
 * @method Mes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MesRepository extends ServiceEntityRepository
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, Mes::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Mes $entity, bool $flush = true): void
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
    public function remove(Mes $entity, bool $flush = true): void
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
    public function addFromRequest($request, bool $flush = false): Mes
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $mes = new Mes();
        $mes = $this->setPropertiesIfFound($request, $mes);

        $this->_em->persist($mes);
        if ($flush) {
            $this->_em->flush();
        }

        return $mes;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editFromRequest($request, Mes $mes, bool $flush = false): Mes
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $this->setPropertiesIfFound($request, $mes);

        if ($flush) {
            $this->_em->flush();
        }

        return $mes;
    }

    private function setPropertiesIfFound($request, Mes $mes)
    {
        $request->get('fecha') === null ? '' : $mes->setFecha(new \DateTime($request->get('fecha')));

        if ($request->get('empleado')) {
            $empleadoRepository = new EmpleadoRepository($this->registry);

            if (is_scalar($request->get('empleado'))) {
                $empleado = $empleadoRepository->find($request->get('empleado'));
            }
            if (!isset($empleado)) {
                $empleado = $empleadoRepository->addFromRequest($request->get('empleado'));
            }

            $mes->setEmpleado($empleado);
        }

        if ($request->get('registros')) {
            $registroRepository = new RegistroRepository($this->registry);
            foreach ($request->get('registros') as $registroElement) {
                if (is_scalar($registroElement)) {
                    $registro = $registroRepository->find($registroElement);
                }
                if (!isset($registro)) {
                    $registro = $registroRepository->addFromRequest($registroElement);
                }

                $mes->addRegistro($registro);
                $registro = null;
            }
        }
        return $mes;
    }





    //    /**
    //     * @return Mes[] Returns an array of Mes objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Mes
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
