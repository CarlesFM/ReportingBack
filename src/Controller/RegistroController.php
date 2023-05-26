<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Form\RegistroType;
use App\Repository\RegistroRepository;
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
class RegistroController extends ApiController
{

    /**
     * @OA\Get(
     *   path="/api/registro/{id}",
     *   summary="Recuperación de un registro por id",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del registro que se quiere recoger. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El registro no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Registro no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El registro ha sido devuelto correctamente",
     *     @OA\JsonContent(
     *       type="object",
     *       example=
     *       {
     *          "id": "3",
     *          "entrada": "time",
     *          "salida": "time",
     *          "almuerzoEntrada": "time",
     *          "almuerzoSalida": "time",
     *          "comidaEntrada": "time",
     *          "comidaSalida": "time",
     *          "mes": "entity",
     *       },    
     *     )
     *   )
     * )
     *
     * @param RegistroRepository $registroRepository
     * @param mixed $id
     * @Route("/registro/{id}", name="app_registro_get", methods={"GET"}, requirements={"id"="\d+"}))
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getRegistro(RegistroRepository $registroRepository, $id): Response
    {
        $registro = $registroRepository->find($id);

        if (!$registro) {
            return $this->respondNotFound('Registro con ese id no encontrado');
        }

        return $this->response($registro);
    }

    /**
     * @OA\Get(
     *   path="/api/registro",
     *   summary="Recuperación del listado de registros",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
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
     *     name="entrada",
     *     in="query",
     *     description="Filtro por entrada.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="salida",
     *     in="query",
     *     description="Filtro por salida.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="almuerzoEntrada",
     *     in="query",
     *     description="Filtro por almuerzoEntrada.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="almuerzoSalida",
     *     in="query",
     *     description="Filtro por almuerzoSalida.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="comidaEntrada",
     *     in="query",
     *     description="Filtro por comidaEntrada.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="comidaSalida",
     *     in="query",
     *     description="Filtro por comidaSalida.",
     *     required=false,
     *     @OA\Schema(type="time")
     *   ),
     *   @OA\Parameter(
     *     name="mes",
     *     in="query",
     *     description="Filtro por mes.",
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
     *             "entrada": "time",
     *             "salida": "time",
     *             "almuerzoEntrada": "time",
     *             "almuerzoSalida": "time",
     *             "comidaEntrada": "time",
     *             "comidaSalida": "time",
     *             "mes": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="entrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="salida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="mes",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param RegistroRepository $registroRepository
     *
     * @Route("/registro", name="app_registros_get", methods={"GET"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getRegistros(Request $request, RegistroRepository $registroRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');

        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $searchCriteria = [];
        $entrada = $request->query->get('entrada');
        if ($entrada) {
            $searchCriteria['entrada'] = $entrada;
        }

        $salida = $request->query->get('salida');
        if ($salida) {
            $searchCriteria['salida'] = $salida;
        }

        $almuerzoEntrada = $request->query->get('almuerzoEntrada');
        if ($almuerzoEntrada) {
            $searchCriteria['almuerzoEntrada'] = $almuerzoEntrada;
        }

        $almuerzoSalida = $request->query->get('almuerzoSalida');
        if ($almuerzoSalida) {
            $searchCriteria['almuerzoSalida'] = $almuerzoSalida;
        }

        $comidaEntrada = $request->query->get('comidaEntrada');
        if ($comidaEntrada) {
            $searchCriteria['comidaEntrada'] = $comidaEntrada;
        }

        $comidaSalida = $request->query->get('comidaSalida');
        if ($comidaSalida) {
            $searchCriteria['comidaSalida'] = $comidaSalida;
        }

        $mes = $request->query->get('mes');
        if ($mes) {
            $searchCriteria['mes'] = $mes;
        }

        $dia = $request->query->get('dia');
        if ($dia) {
            $searchCriteria['dia'] = $dia;
        }

        $registros = $registroRepository->findBy($searchCriteria, [], $limit = $num_devoluciones, $offset = ($num_pagina - 1) * $limit);

        return $this->response($registros);
    }

    /**
     * @OA\Get(
     *   path="/api/registro/mes",
     *   summary="Recuperación del listado de registros",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
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
     *    @OA\Parameter(
     *     name="fechaInicio",
     *     in="query",
     *     description="Fecha de inicio de filtrado",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *    @OA\Parameter(
     *     name="fechaFin",
     *     in="query",
     *     description="Fecha fin de filtrado",
     *     required=true,
     *     @OA\Schema(type="integer")
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
     *             "entrada": "time",
     *             "salida": "time",
     *             "almuerzoEntrada": "time",
     *             "almuerzoSalida": "time",
     *             "comidaEntrada": "time",
     *             "comidaSalida": "time",
     *             "mes": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="entrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="salida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="mes",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param RegistroRepository $registroRepository
     *
     * @Route("/registro/mes", name="filtrado_mes", methods={"GET"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getRegistrosMes(Request $request, RegistroRepository $registroRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');
        $empleado = $request->query->get('empleado');


        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $searchCriteria = [];

        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');

        $registros = $registroRepository->findByEmpleadoYFechas($empleado, $fechaInicio, $fechaFin, $num_devoluciones, ($num_pagina - 1) * $num_devoluciones);

        return $this->response($registros);
    }
    /**
     * @OA\Get(
     *   path="/api/registro/empleado",
     *   summary="Recuperación del listado de registros",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
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
     *  @OA\Parameter(
     *     name="empleado",
     *     in="query",
     *     description="",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="mes",
     *     in="query",
     *     description="Filtro por mes.",
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
     *             "entrada": "time",
     *             "salida": "time",
     *             "almuerzoEntrada": "time",
     *             "almuerzoSalida": "time",
     *             "comidaEntrada": "time",
     *             "comidaSalida": "time",
     *             "mes": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="entrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="salida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="mes",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param RegistroRepository $registroRepository
     *
     * @Route("/registro/empleado", name="filtrado_empleado", methods={"GET"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getRegistrosEmpleado(Request $request, RegistroRepository $registroRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');
        $empleado = $request->query->get('empleado');


        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $registros = $registroRepository->findByEmpleado($empleado, $num_devoluciones, ($num_pagina - 1) * $num_devoluciones);

        return $this->response($registros);
    }
    /**
     * @OA\Post(
     *   path="/api/registro",
     *   summary="Creación de un registro nuevo",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información del nuevo registro",
     *     @OA\JsonContent(
     *       required={"comidaEntrada","mes",},
     *       @OA\Property(property="entrada", type="time", example="time"),
     *       @OA\Property(property="salida", type="time", example="time"),
     *       @OA\Property(property="almuerzoEntrada", type="time", example="time"),
     *       @OA\Property(property="almuerzoSalida", type="time", example="time"),
     *       @OA\Property(property="comidaEntrada", type="time", example="time"),
     *       @OA\Property(property="comidaSalida", type="time", example="time"),
     *       @OA\Property(property="mes", type="entity", example="entity"),
     *       @OA\Property(property="dia", type="integer", example="integer"),
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
     *     description="El registro ha sido creado correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El registro ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "entrada": "time",
     *             "salida": "time",
     *             "dia" : "integer",
     *             "almuerzoEntrada": "time",
     *             "almuerzoSalida": "time",
     *             "comidaEntrada": "time",
     *             "comidaSalida": "time",
     *             "mes": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="entrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="salida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="dia",
     *           type="integer",
     *           example="integer"
     *         ),
     *         @OA\Property(
     *           property="almuerzoEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="mes",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     * @param Request $request
     *
     * @Route("/registro", name="app_registro_add", methods={"POST"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function addRegistro(Request $request, ManagerRegistry $doctrine, RegistroRepository $registroRepository): Response
    {
        $request = $this->transformJsonBody($request);

        $missingParameter = $this->allNeededParametersPresent($request);
        if ($missingParameter) {
            return $this->respondValidationError('Datos inválidos, falta el parámetro ' . $missingParameter);
        }

        $registroExistente = $registroRepository->findOneBy(['mes' => $request->get('mes'), 'dia' => $request->get('dia')]);

        if ($registroExistente == null) {
            $me = $registroRepository->addFromRequest($request);

            $em = $doctrine->getManager();
            $em->persist($me);

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
            }

            return $this->respondWithSuccess('Registro creado correctamente', $me);
        } else {
            return $this->respondWithErrors('El registro no se ha creado, ya existente');
        }
    }
    /**
     * @OA\Put(
     *   path="/api/registro/{id}",
     *   summary="Edición de un registro",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del registro que se quiere editar. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información a editar del registro",
     *     @OA\JsonContent(
     *       @OA\Property(property="entrada", type="time", example="time"),
     *       @OA\Property(property="salida", type="time", example="time"),
     *       @OA\Property(property="almuerzoEntrada", type="time", example="time"),
     *       @OA\Property(property="almuerzoSalida", type="time", example="time"),
     *       @OA\Property(property="comidaEntrada", type="time", example="time"),
     *       @OA\Property(property="comidaSalida", type="time", example="time"),
     *       @OA\Property(property="mes", type="entity", example="entity"),
     *     ),
     *   ),    
     *   @OA\Response(
     *     response=404,
     *     description="El registro no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Registro no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El registro ha sido editado correctamente. Se devuelven los nuevos datos",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El registro ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "entrada": "time",
     *             "salida": "time",
     *             "almuerzoEntrada": "time",
     *             "almuerzoSalida": "time",
     *             "comidaEntrada": "time",
     *             "comidaSalida": "time",
     *             "mes": "entity",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="entrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="salida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="almuerzoSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaEntrada",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="comidaSalida",
     *           type="time",
     *           example="time"
     *         ),
     *         @OA\Property(
     *           property="mes",
     *           type="entity",
     *           example="entity"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param Request $request
     * @param RegistroRepository $registroRepository
     * @param mixed $id
     *
     * @Route("/registro/{id}", name="app_registro_edit",methods={"PUT"}, requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function edit(Request $request, ManagerRegistry $doctrine, RegistroRepository $registroRepository, $id): Response
    {
        $request = $this->transformJsonBody($request);
        if (!$request) {
            return $this->respondValidationError('Datos inválidos');
        }

        $registro = $registroRepository->find($id);

        if (!$registro) {
            return $this->respondNotFound('Registro con ese id no encontrado');
        }

        
       
            $registro = $registroRepository->editFromRequest($request, $registro);

            $em = $doctrine->getManager();

            try {
                $em->flush();
            } catch (\Exception $e) {
                return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
            }

            return $this->respondWithSuccess('Registro editado correctamente', $registro);
        
    }

    /**
     * @OA\Delete(
     *   path="/api/registro",
     *   summary="Eliminación de registros",
     *   security={ {"bearer": {} }},
     *   tags={"Registro"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Identificadores de los registros que se quieren eliminar",
     *     @OA\JsonContent(
     *       required={"ids"},
     *       @OA\Property(property="ids", type="array", example={1, 3, 5}, @OA\Items())
     *     ),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El registro no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Registro con id 3 no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="Los registro han sido eliminados correctamente",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=200),
     *       @OA\Property(property="message", type="string", example="Registros eliminados con éxito"),
     *     )
     *   )
     * )
     * @param RegistroRepository $registroRepository
     * @param mixed $id
     *
     * @Route("/registro", name="app_registro_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function delete(Request $request, ManagerRegistry $doctrine, RegistroRepository $registroRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $registrosIds = $request->get('ids');
        if (!StaticUtilities::dataIsValid($registrosIds)) {
            return $this->respondValidationError("Datos no válidos");
        }

        $registros = array();
        // Comprobar si algún registro no existe
        foreach ($registrosIds as $id) {
            $registro = $registroRepository->find($id);
            if (!$registro) {
                return $this->respondNotFound('Registro con ese id no encontrado');
            }
            $registros[] = $registro;
        }

        $em = $doctrine->getManager();

        // Finalmente eliminar
        foreach ($registros as $registro) {
            $em->remove($registro);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible eliminar tu registro ' . $e);
        }

        return $this->respondWithSuccess('Registros eliminados correctamente');
    }

    /**
     *
     * Checks if all needed parameters are present or not
     *
     * @param mixed $registroJson
     *
     * @return [type]
     */
    private function allNeededParametersPresent($registroJson): string
    {
        $parameters = ['dia','mes'];

        foreach ($parameters as $param) {
            if ($registroJson->get($param) === null) {
                return $param;
            }
        }

        return '';
    }
}
