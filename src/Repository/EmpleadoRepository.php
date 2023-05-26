<?php

namespace App\Repository;

use App\Entity\Empleado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * @method Empleado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Empleado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Empleado[]    findAll()
 * @method Empleado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpleadoRepository extends ServiceEntityRepository
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, Empleado::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Empleado $entity, bool $flush = true): void
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
    public function remove(Empleado $entity, bool $flush = true): void
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
    public function addFromRequest($request, bool $flush = false,UserPasswordHasherInterface $passwordHasher): Empleado
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $empleado = new Empleado();
        $empleado = $this->setPropertiesIfFound($request, $empleado,$passwordHasher);

        $this->_em->persist($empleado);
        if ($flush) {
            $this->_em->flush();
        }

        return $empleado;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editFromRequest($request, Empleado $empleado, bool $flush = false, UserPasswordHasherInterface $passwordHasher): Empleado
    {
        // If array is passed, convert it to "request"
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        $this->setPropertiesIfFound($request, $empleado, $passwordHasher);

        if ($flush) {
            $this->_em->flush();
        }

        return $empleado;
    }

    private function setPropertiesIfFound($request, Empleado $empleado, $passwordHasher)
    {
        echo($request);
        $request->get('nombre') === null ? '' : $empleado->setNombre($request->get('nombre'));
        $request->get('apellidos') === null ? '' : $empleado->setApellidos($request->get('apellidos'));
        $request->get('correo') === null ? '' : $empleado->setCorreo($request->get('correo'));
        $request->get('dni') === null ? '' : $empleado->setDni($request->get('dni'));

        // $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));


        $request->get('password') === null ? '' : $empleado->setPassword(($passwordHasher->hashPassword($empleado,$request->get('password'))));


        $request->get('roles') === null ? '' : $empleado->setRoles($request->get('roles'));
        if ($request->get('empresas')) {
            $empresasRepository = new EmpresasRepository($this->registry);

             $empresas = $empresasRepository->find($request->get('empresas'));
            $empleado->setEmpresas($empresas);
}

        return $empleado;
    }

    //    /**
    //     * @return Empleado[] Returns an array of Empleado objects
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

    //    public function findOneBySomeField($value): ?Empleado
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
