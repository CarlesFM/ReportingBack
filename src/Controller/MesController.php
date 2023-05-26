<?php

namespace App\Controller;

use App\Entity\Mes;
use App\Form\MesType;
use App\Repository\EmpleadoRepository;
use App\Repository\MesRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/api")
 */
class MesController extends ApiController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @OA\Get(
     *   path="/{id}",
     *   summary="Recuperación de un me por id",
     *   security={ {"bearer": {} }},
     *   tags={"Mes"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del me que se quiere recoger. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El me no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Mes no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El me ha sido devuelto correctamente",
     *     @OA\JsonContent(
     *       type="object",
     *       example=
     *       {
     *          "id": "3",
     *          "fecha": "datetime",
     *          "empleado": "entity",
     *       },    
     *     )
     *   )
     * )
     *
     * @param MesRepository $mesRepository
     * @param mixed $id
     * @Route("/mes/empleado/{id}", name="app_mes_get", methods={"GET"}, requirements={"id"="\d+"}))
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getMes(MesRepository $mesRepository, $id): Response
    {

        $me = $mesRepository->findBy(['empleado' => $id]);

        if (!$me) {
            return $this->respondNotFound('Mes con ese id no encontrado');
        }

        return $this->response($me);
    }


    /**
     * @OA\Get(
     *   path="/api/mes",
     *   summary="Recuperación del listado de mes",
     *   security={ {"bearer": {} }},
     *   tags={"Mes"},
     *   @OA\Parameter(
     *     name="num_devoluciones",
     *     in="query",
     *     description="Número máximo de filas a devolver. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="num_pagina",
     *     in="query",
     *     description="Número de página desde el que devolver las filas. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="fecha",
     *     in="query",
     *     description="Filtro por fecha.",
     *     required=false,
     *     @OA\Schema(type="datetime")
     *   ),
     *   @OA\Parameter(
     *     name="empleado",
     *     in="query",
     *     description="Filtro por empleado.",
     *     required=false,
     *     @OA\Schema(type="entity")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Los parámetros de la paginación no son correctos",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=422),
     *       @OA\Property(property="message", type="string", example="Datos de paginación no válidos"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El listado ha sido devuelto correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *          {
     *             "id": "3",
     *             "fecha": "datetime",
     *             "empleado": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="fecha",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="empleado",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param MesRepository $mesRepository
     *
     * @Route("/mes", name="app_mess_get", methods={"GET"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getMess(Request $request, MesRepository $mesRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');

        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $searchCriteria = [];
        $fecha = $request->query->get('fecha');
        if ($fecha) {
            $searchCriteria['fecha'] = new \DateTime($fecha);
        }

        $empleado = $request->query->get('empleado');
        if ($empleado) {
            $searchCriteria['empleado'] = $empleado;
        }

        $mes = $mesRepository->findBy($searchCriteria, [], $limit = $num_devoluciones, $offset = ($num_pagina - 1) * $limit);

        return $this->response($mes);
    }

    /**
     * @OA\Post(
     *   path="/api/mes",
     *   summary="Creación de un mes nuevo",
     *   security={ {"bearer": {} }},
     *   tags={"Mes"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información del nuevo mes",
     *     @OA\JsonContent(
     *       required={"empleado",},
     *       @OA\Property(property="fecha", type="datetime", example="datetime"),
     *       @OA\Property(property="empleado", type="entity", example="entity"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Alguno de los parámetros no es válido",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=422),
     *       @OA\Property(property="message", type="string", example="Datos no válidos"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El me ha sido creado correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El me ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "fecha": "datetime",
     *             "empleado": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="fecha",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="empleado",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     * @param Request $request
     *
     * @Route("/mes", name="app_mes_add", methods={"POST"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function addMes(Request $request, ManagerRegistry $doctrine, MesRepository $mesRepository): Response
    {
        $request = $this->transformJsonBody($request);

        $missingParameter = $this->allNeededParametersPresent($request);
        if ($missingParameter) {
            return $this->respondValidationError('Datos inválidos, falta el parámetro ' . $missingParameter);
        }

        $mesExistente = $mesRepository->findOneBy(['fecha' => new \DateTime($request->get('fecha')), 'empleado' => $request->get('empleado')]);

        if ($mesExistente == null) {
            $me = $mesRepository->addFromRequest($request);

            $em = $doctrine->getManager();
            $em->persist($me);

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
            }

            return $this->respondWithSuccess('Mes creado correctamente', $me);
        } else {
            return $this->respondWithErrors('El mes no se ha creado, ya existente');
        }
    }
    /**
     * @OA\Put(
     *   path="/api/mes/{id}",
     *   summary="Edición de un me",
     *   security={ {"bearer": {} }},
     *   tags={"Mes"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del me que se quiere editar. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información a editar del me",
     *     @OA\JsonContent(
     *       @OA\Property(property="fecha", type="datetime", example="datetime"),
     *       @OA\Property(property="empleado", type="entity", example="entity"),
     *     ),
     *   ),    
     *   @OA\Response(
     *     response=404,
     *     description="El me no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Mes no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El me ha sido editado correctamente. Se devuelven los nuevos datos",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El me ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "fecha": "datetime",
     *             "empleado": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="fecha",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="empleado",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param Request $request
     * @param MesRepository $mesRepository
     * @param mixed $id
     *
     * @Route("/mes/{id}", name="app_mes_edit",methods={"PUT"}, requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function edit(Request $request, ManagerRegistry $doctrine, MesRepository $mesRepository, $id): Response
    {
        $request = $this->transformJsonBody($request);
        if (!$request) {
            return $this->respondValidationError('Datos inválidos');
        }

        $me = $mesRepository->find($id);

        if (!$me) {
            return $this->respondNotFound('Mes con ese id no encontrado');
        }

        $mesExistente = $mesRepository->findOneBy(['fecha' => $request->get('fecha'), 'empleado' => $request->get('empleado')]);

        if ($mesExistente == null) {
            $mes = $mesRepository->editFromRequest($request, $me);

            $em = $doctrine->getManager();

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
            }

            return $this->respondWithSuccess('Mes editado correctamente', $me);
        } else {
            return $this->respondWithErrors('El mes no se ha actualizado, ya existe uno con esos datos');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/mes",
     *   summary="Eliminación de mes",
     *   security={ {"bearer": {} }},
     *   tags={"Mes"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Identificadores de los mes que se quieren eliminar",
     *     @OA\JsonContent(
     *       required={"ids"},
     *       @OA\Property(property="ids", type="array", example={1, 3, 5}, @OA\Items())
     *     ),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El me no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Mes con id 3 no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="Los me han sido eliminados correctamente",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=200),
     *       @OA\Property(property="message", type="string", example="Mess eliminados con éxito"),
     *     )
     *   )
     * )
     * @param MesRepository $mesRepository
     * @param mixed $id
     *
     * @Route("/mes", name="app_mes_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function delete(Request $request, ManagerRegistry $doctrine, MesRepository $mesRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $mesIds = $request->get('ids');
        if (!StaticUtilities::dataIsValid($mesIds)) {
            return $this->respondValidationError("Datos no válidos");
        }

        $mes = array();
        // Comprobar si algún me no existe
        foreach ($mesIds as $id) {
            $me = $mesRepository->find($id);
            if (!$me) {
                return $this->respondNotFound('Mes con ese id no encontrado');
            }
            $mes[] = $me;
        }

        $em = $doctrine->getManager();

        // Finalmente eliminar
        foreach ($mes as $me) {
            $em->remove($me);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible eliminar tu me ' . $e);
        }

        return $this->respondWithSuccess('Mes eliminados correctamente');
    }

    /**
     *
     * Checks if all needed parameters are present or not
     *
     * @param mixed $meJson
     *
     * @return [type]
     */
    private function allNeededParametersPresent($meJson): string
    {
        $parameters = ['fecha', 'empleado'];

        foreach ($parameters as $param) {
            if ($meJson->get($param) === null) {
                return $param;
            }
        }

        return '';
    }
}
