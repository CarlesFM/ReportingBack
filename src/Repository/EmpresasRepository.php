<?php

namespace App\Repository;

use App\Entity\Empresas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Empresas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Empresas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Empresas[]    findAll()
 * @method Empresas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresasRepository extends ServiceEntityRepository
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, Empresas::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Empresas $entity, bool $flush = true): void
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
    public function remove(Empresas $entity, bool $flush = true): void
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
    public function addFromRequest($request, bool $flush = false): Empresas|string
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $empresas = new Empresas();
        $empresas = $this->setPropertiesIfFound($request, $empresas);
        if (is_string($empresas)) {
            return $empresas;
        }

        $this->_em->persist($empresas);
        if ($flush) {
            $this->_em->flush();
        }

        return $empresas;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editFromRequest($request, Empresas $empresas, bool $flush = false): Empresas|string
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $this->setPropertiesIfFound($request, $empresas);

        if ($flush) {
            $this->_em->flush();
        }

        return $empresas;
    }

    private function setPropertiesIfFound($request, Empresas $empresas)
    {
        $request->get('nombre') === null ? '' : $empresas->setNombre($request->get('nombre'));
        $request->get('cif') === null ? '' : $empresas->setCif($request->get('cif'));

        return $empresas;
    }

    /**
     * Getting the query params from the request for filtering
     * @param Request $request The request including all the query params
     * @return QueryBuilder The result of the query
     */
    public function findFilteredByParams($request)
    {
        $query = $this->createQueryBuilder('e');

        if ($request->get('nombre')) {
            $query->andWhere('e.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $request->get('nombre') . '%');
        }

        if ($request->get('cif')) {
            $query->andWhere('e.cif LIKE :cif')
                ->setParameter('cif', '%' . $request->get('cif') . '%');
        }

        // Add pagination to query
        $query->setFirstResult(($request->get('num_pagina') -1) * $request->get('num_devoluciones'))
        ->setMaxResults($request->get('num_devoluciones'));

        return $query->getQuery()->getResult();
    }


//    /**
//     * @return Empresas[] Returns an array of Empresas objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Empresas
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
