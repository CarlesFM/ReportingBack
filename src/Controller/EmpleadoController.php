<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Form\EmpleadoType;
use App\Repository\EmpleadoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class EmpleadoController extends ApiController
{

    /**
     * @OA\Get(
     *   path="/api/empleado/{id}",
     *   summary="Recuperación de un empleado por id",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del empleado que se quiere recoger. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El empleado no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El empleado ha sido devuelto correctamente",
     *     @OA\JsonContent(
     *       type="object",
     *       example=
     *       {
     *          "id": "3",
     *          "nombre": "string",
     *          "apellidos": "string",
     *          "correo": "string",
     *       },    
     *     )
     *   )
     * )
     *
     * @param EmpleadoRepository $empleadoRepository
     * @param mixed $id
     * @Route("/empleado/{id}", name="app_empleado_get", methods={"GET"}, requirements={"id"="\d+"}))
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getEmpleado(EmpleadoRepository $empleadoRepository, $id): Response
    {
        $empleado = $empleadoRepository->find($id);

        if (!$empleado) {
            return $this->respondNotFound('Empleado con ese id no encontrado');
        }

        return $this->response($empleado);
    }

    /**
     * @OA\Get(
     *   path="/api/empleado",
     *   summary="Recuperación del listado de empleados",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
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
     *     name="id",
     *     in="query",
     *     description="Filtro por id.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="nombre",
     *     in="query",
     *     description="Filtro por nombre.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="apellidos",
     *     in="query",
     *     description="Filtro por apellidos.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="correo",
     *     in="query",
     *     description="Filtro por correo.",
     *     required=false,
     *     @OA\Schema(type="string")
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
     *             "nombre": "string",
     *             "apellidos": "string",
     *             "correo": "string",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="id",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="nombre",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="apellidos",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="correo",
     *           type="string",
     *           example="string"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param EmpleadoRepository $empleadoRepository
     *
     * @Route("/empleado", name="app_empleados_get", methods={"GET"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getEmpleados(Request $request, EmpleadoRepository $empleadoRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');

        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $searchCriteria = [];
        $dni = $request->query->get('dni');
        if ($dni) {
            $searchCriteria['dni'] = $dni;
        }

        $nombre = $request->query->get('nombre');
        if ($nombre) {
            $searchCriteria['nombre'] = $nombre;
        }

        $apellidos = $request->query->get('apellidos');
        if ($apellidos) {
            $searchCriteria['apellidos'] = $apellidos;
        }

        $correo = $request->query->get('correo');
        if ($correo) {
            $searchCriteria['correo'] = $correo;
        }

        $empleados = $empleadoRepository->findBy($searchCriteria, [], $limit = $num_devoluciones, $offset = ($num_pagina - 1) * $limit);

        return $this->response($empleados);
    }

    /**
     * @OA\Post(
     *   path="/api/empleado",
     *   summary="Creación de un empleado nuevo",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información del nuevo empleado",
     *     @OA\JsonContent(
     *       required={"dni",},
     *       @OA\Property(property="dni", type="string", example="string"),
     *       @OA\Property(property="nombre", type="string", example="string"),
     *       @OA\Property(property="apellidos", type="string", example="string"),
     *       @OA\Property(property="correo", type="string", example="string"),
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
     *     description="El empleado ha sido creado correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El empleado ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "nombre": "string",
     *             "apellidos": "string",
     *             "correo": "string",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="id",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="nombre",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="apellidos",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="correo",
     *           type="string",
     *           example="string"
     *         ),
     *       )
     *     )
     *   )
     * )
     * @param Request $request
     *
     * @Route("/empleado", name="app_empleado_add", methods={"POST"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function addEmpleado(Request $request, ManagerRegistry $doctrine, EmpleadoRepository $empleadoRepository): Response
    {
        $request = $this->transformJsonBody($request);

        $missingParameter = $this->allNeededParametersPresent($request);
        if ($missingParameter) {
            return $this->respondValidationError('Datos inválidos, falta el parámetro ' . $missingParameter);
        }

        $empleado = $empleadoRepository->addFromRequest($request);

        $em = $doctrine->getManager();
        $em->persist($empleado);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
        }

        return $this->respondWithSuccess('Empleado creado correctamente', $empleado);
    }


    /**
     * @OA\Put(
     *   path="/api/empleado/{id}",
     *   summary="Edición de un empleado",
     *   tags={"Empleado"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del empleado que se quiere editar. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información a editar del empleado",
     *     @OA\JsonContent(
     *       @OA\Property(property="nombre", type="string", example="string"),
     *       @OA\Property(property="apellidos", type="string", example="string"),
     *       @OA\Property(property="correo", type="string", example="string"),
     *     ),
     *   ),    
     *   @OA\Response(
     *     response=404,
     *     description="El empleado no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El empleado ha sido editado correctamente. Se devuelven los nuevos datos",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El empleado ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "nombre": "string",
     *             "apellidos": "string",
     *             "correo": "string",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="id",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="nombre",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="apellidos",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="correo",
     *           type="string",
     *           example="string"
     *         ),
     *       )
     *     )
     *   )
     * )
     *
     * @param Request $request
     * @param EmpleadoRepository $empleadoRepository
     * @param mixed $id
     *
     * @Route("/empleado/{id}", name="app_empleado_edit",methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, ManagerRegistry $doctrine, EmpleadoRepository $empleadoRepository, $id,UserPasswordHasherInterface $passwordHasher): Response
    {
        $request = $this->transformJsonBody($request);
        if (!$request) {
            return $this->respondValidationError('Datos inválidos');
        }

        $empleado = $empleadoRepository->find($id);

        if (!$empleado) {
            return $this->respondNotFound('Empleado con ese id no encontrado');
        }

        $empleado = $empleadoRepository->editFromRequest($request, $empleado,false,$passwordHasher);

        $em = $doctrine->getManager();

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
        }

        return $this->respondWithSuccess('Empleado editado correctamente', $empleado);
    }

    /**
     * @OA\Delete(
     *   path="/api/empleado",
     *   summary="Eliminación de empleados",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Identificadores de los empleados que se quieren eliminar",
     *     @OA\JsonContent(
     *       required={"ids"},
     *       @OA\Property(property="ids", type="array", example={1, 3, 5}, @OA\Items())
     *     ),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El empleado no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Empleado con id 3 no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="Los empleado han sido eliminados correctamente",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=200),
     *       @OA\Property(property="message", type="string", example="Empleados eliminados con éxito"),
     *     )
     *   )
     * )
     * @param EmpleadoRepository $empleadoRepository
     * @param mixed $id
     *
     * @Route("/empleado", name="app_empleado_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function delete(Request $request, ManagerRegistry $doctrine, EmpleadoRepository $empleadoRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $empleadosIds = $request->get('ids');
        if (!StaticUtilities::dataIsValid($empleadosIds)) {
            return $this->respondValidationError("Datos no válidos");
        }

        $empleados = array();
        // Comprobar si algún empleado no existe
        foreach ($empleadosIds as $id) {
            $empleado = $empleadoRepository->find($id);
            if (!$empleado) {
                return $this->respondNotFound('Empleado con ese dni no encontrado');
            }
            $empleados[] = $empleado;
        }

        $em = $doctrine->getManager();

        // Finalmente eliminar
        foreach ($empleados as $empleado) {
            $em->remove($empleado);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible eliminar tu empleado ' . $e);
        }

        return $this->respondWithSuccess('Empleados eliminados correctamente');
    }











    /**
     *
     * Checks if all needed parameters are present or not
     *
     * @param mixed $empleadoJson
     *
     * @return [type]
     */
    private function allNeededParametersPresent($empleadoJson): string
    {
        $parameters = ['nombre', 'apellidos', 'correo'];

        foreach ($parameters as $param) {
            if ($empleadoJson->get($param) === null) {
                return $param;
            }
        }

        return '';
    }
    /**
     * @OA\Get(
     *   path="/api/empleado/{correo}",
     *   summary="Recuperación de un empleado por correo",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identificador del empleado que se quiere recoger. Rango posible de valores: números enteros desde el 1 hasta el +∞",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="El empleado no existe",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=404),
     *       @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El empleado ha sido devuelto correctamente",
     *     @OA\JsonContent(
     *       type="object",
     *       example=
     *       {
     *          "id": "3",
     *          "nombre": "string",
     *          "apellidos": "string",
     *          "correo": "string",
     *       },    
     *     )
     *   )
     * )
     *
     * @param EmpleadoRepository $empleadoRepository
     * @param mixed $correo
     * @Route("/empleado/{correo}", name="app_empleado_get_correo", methods={"GET"}, requirements={"id"="\d+"}))
     * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
     */
    public function getEmpleadoCorreo(EmpleadoRepository $empleadoRepository, $correo): Response
    {
        $empleado = $empleadoRepository->findOneBy(['correo' => $correo]);

        if (!$empleado) {
            return $this->respondNotFound('Empleado con ese id no encontrado');
        }

        return $this->response($empleado);
    }
}
