<?php

namespace App\Controller;

use App\Entity\Empresas;
use App\Form\EmpresasType;
use App\Repository\EmpresasRepository;
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
class EmpresasController extends ApiController
{

   /**
    * @OA\Get(
    *   path="/api/empresas/{id}",
    *   summary="Recuperación de un empresa por id",
    *   security={ {"bearer": {} }},
    *   tags={"Empresas"},
    *   @OA\Parameter(
    *     name="id",
    *     in="path",
    *     description="Identificador del empresa que se quiere recoger. Rango posible de valores: números enteros desde el 1 hasta el +∞",
    *     required=true,
    *     @OA\Schema(type="integer")
    *   ),
    *   @OA\Response(
    *     response=404,
    *     description="El empresa no existe",
    *     @OA\JsonContent(
    *       @OA\Property(property="status", type="integer", example=404),
    *       @OA\Property(property="message", type="string", example="Empresas no encontrado"),
    *     )
    *   ),
    *   @OA\Response(
    *     response="200",
    *     description="El empresa ha sido devuelto correctamente",
    *     @OA\JsonContent(
    *       type="object",
    *       example=
    *       {
    *          "id": "3",
    *          "nombre": "string",
    *          "cif": "string",
    *       },    
    *     )
    *   )
    * )
    *
    * @param EmpresasRepository $empresasRepository
    * @param mixed $id
    * @Route("/empresas/{id}", name="app_empresas_get", methods={"GET"}, requirements={"id"="\d+"}))
    * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
    */
    public function getEmpresas(EmpresasRepository $empresasRepository, $id): Response
    {
        $empresa = $empresasRepository->find($id);

        if (!$empresa) {
            return $this->respondNotFound('Empresas con ese id no encontrado');
        }

        return $this->response($empresa);
    }

   /**
    * @OA\Get(
    *   path="/api/empresas",
    *   summary="Recuperación del listado de empresas",
    *   security={ {"bearer": {} }},
    *   tags={"Empresas"},
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
    *     name="nombre",
    *     in="query",
    *     description="Filtro por nombre.",
    *     required=false,
    *     @OA\Schema(type="string")
    *   ),
    *   @OA\Parameter(
    *     name="cif",
    *     in="query",
    *     description="Filtro por cif.",
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
    *             "cif": "string",
    *          },
    *       },
    *       @OA\Items(
    *         @OA\Property(
    *           property="id",
    *           type="integer",
    *           example=3
    *         ),
    *         @OA\Property(
    *           property="nombre",
    *           type="string",
    *           example="string"
    *         ),
    *         @OA\Property(
    *           property="cif",
    *           type="string",
    *           example="string"
    *         ),
    *       )
    *     )
    *   )
    * )
    *
    * @param EmpresasRepository $empresasRepository
    *
    * @Route("/empresas", name="app_empresass_get", methods={"GET"})
    * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
    */
    public function getEmpresass(Request $request, EmpresasRepository $empresasRepository): Response
    {
        $num_devoluciones = $request->query->get('num_devoluciones');
        $num_pagina = $request->query->get('num_pagina');

        if (!StaticUtilities::dataIsValid($num_devoluciones) || !StaticUtilities::dataIsValid($num_pagina)) {
            return $this->respondValidationError("Datos de paginación no válidos");
        }

        $empresas = $empresasRepository->findFilteredByParams($request);
    
        return $this->response($empresas);
    }

   /**
    * @OA\Post(
    *   path="/api/empresas",
    *   summary="Creación de un empresa nuevo",
    *   security={ {"bearer": {} }},
    *   tags={"Empresas"},
    *   @OA\RequestBody(
    *     required=true,
    *     description="Información del nuevo empresa",
    *     @OA\JsonContent(
    *       required={"nombre","cif",},
    *       @OA\Property(property="nombre", type="string", example="string"),
    *       @OA\Property(property="cif", type="string", example="string"),
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
    *     description="El empresa ha sido creado correctamente",
    *     @OA\JsonContent(
    *       type="array",
    *       example={
    *         "message": "El empresa ha sido editado correctamente",
    *         "data": {
    *             "id": "3",
    *             "nombre": "string",
    *             "cif": "string",
    *          },
    *       },
    *       @OA\Items(
    *         @OA\Property(
    *           property="id",
    *           type="integer",
    *           example=3
    *         ),
    *         @OA\Property(
    *           property="nombre",
    *           type="string",
    *           example="string"
    *         ),
    *         @OA\Property(
    *           property="cif",
    *           type="string",
    *           example="string"
    *         ),
    *       )
    *     )
    *   )
    * )
    * @param Request $request
    *
    * @Route("/empresas", name="app_empresas_add", methods={"POST"})
    * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
    */
    public function addEmpresas(Request $request, ManagerRegistry $doctrine, EmpresasRepository $empresasRepository): Response
    {
        $request = $this->transformJsonBody($request);

        $missingParameter = $this->allNeededParametersPresent($request);
        if ($missingParameter) {
            return $this->respondValidationError('Datos inválidos, falta el parámetro ' . $missingParameter);
        }

        $empresa = $empresasRepository->addFromRequest($request);
        if (is_string($empresa)) {
            return $this->respondValidationError($empresa);
        }

        $em = $doctrine->getManager();
        $em->persist($empresa);
        
        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
        }

        return $this->respondWithSuccess('Empresas creado correctamente', $empresa);
    }


   /**
    * @OA\Put(
    *   path="/api/empresas/{id}",
    *   summary="Edición de un empresa",
    *   security={ {"bearer": {} }},
    *   tags={"Empresas"},
    *   @OA\Parameter(
    *     name="id",
    *     in="path",
    *     description="Identificador del empresa que se quiere editar. Rango posible de valores: números enteros desde el 1 hasta el +∞",
    *     required=true,
    *     @OA\Schema(type="integer")
    *   ),
    *   @OA\RequestBody(
    *     required=true,
    *     description="Información a editar del empresa",
    *     @OA\JsonContent(
    *       @OA\Property(property="nombre", type="string", example="string"),
    *       @OA\Property(property="cif", type="string", example="string"),
    *     ),
    *   ),    
    *   @OA\Response(
    *     response=404,
    *     description="El empresa no existe",
    *     @OA\JsonContent(
    *       @OA\Property(property="status", type="integer", example=404),
    *       @OA\Property(property="message", type="string", example="Empresas no encontrado"),
    *     )
    *   ),
    *   @OA\Response(
    *     response="200",
    *     description="El empresa ha sido editado correctamente. Se devuelven los nuevos datos",
    *     @OA\JsonContent(
    *       type="array",
    *       example={
    *         "message": "El empresa ha sido editado correctamente",
    *         "data": {
    *             "id": "3",
    *             "nombre": "string",
    *             "cif": "string",
    *          },
    *       },
    *       @OA\Items(
    *         @OA\Property(
    *           property="id",
    *           type="integer",
    *           example=3
    *         ),
    *         @OA\Property(
    *           property="nombre",
    *           type="string",
    *           example="string"
    *         ),
    *         @OA\Property(
    *           property="cif",
    *           type="string",
    *           example="string"
    *         ),
    *       )
    *     )
    *   )
    * )
    *
    * @param Request $request
    * @param EmpresasRepository $empresasRepository
    * @param mixed $id
    *
    * @Route("/empresas/{id}", name="app_empresas_edit",methods={"PUT"}, requirements={"id"="\d+"})
    * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
    */
    public function edit(Request $request, ManagerRegistry $doctrine, EmpresasRepository $empresasRepository, $id): Response
    {
        $request = $this->transformJsonBody($request);
        if (!$request) {
            return $this->respondValidationError('Datos inválidos');
        }

        $empresa = $empresasRepository->find($id);

        if (!$empresa) {
            return $this->respondNotFound('Empresas con ese id no encontrado');
        }

        $empresa = $empresasRepository->editFromRequest($request, $empresa);

        $em = $doctrine->getManager();

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
        }

        return $this->respondWithSuccess('Empresas editado correctamente', $empresa);
    }

   /**
    * @OA\Delete(
    *   path="/api/empresas",
    *   summary="Eliminación de empresas",
    *   security={ {"bearer": {} }},
    *   tags={"Empresas"},
    *   @OA\RequestBody(
    *     required=true,
    *     description="Identificadores de los empresas que se quieren eliminar",
    *     @OA\JsonContent(
    *       required={"ids"},
    *       @OA\Property(property="ids", type="array", example={1, 3, 5}, @OA\Items())
    *     ),
    *   ),
    *   @OA\Response(
    *     response=404,
    *     description="El empresa no existe",
    *     @OA\JsonContent(
    *       @OA\Property(property="status", type="integer", example=404),
    *       @OA\Property(property="message", type="string", example="Empresas con id 3 no encontrado"),
    *     )
    *   ),
    *   @OA\Response(
    *     response="200",
    *     description="Los empresa han sido eliminados correctamente",
    *     @OA\JsonContent(
    *       @OA\Property(property="status", type="integer", example=200),
    *       @OA\Property(property="message", type="string", example="Empresass eliminados con éxito"),
    *     )
    *   )
    * )
    * @param EmpresasRepository $empresasRepository
    * @param mixed $id
    *
    * @Route("/empresas", name="app_empresas_delete", methods={"DELETE"})
    * @Security("is_granted('ROLE_USER')", statusCode=403, message="No tienes permisos para acceder a este recurso.")
    */
    public function delete(Request $request, ManagerRegistry $doctrine, EmpresasRepository $empresasRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $empresasIds = $request->get('ids');
        if (!StaticUtilities::dataIsValid($empresasIds)) {
            return $this->respondValidationError("Datos no válidos");
        }

        $empresas = array();
        // Comprobar si algún empresa no existe
        foreach ($empresasIds as $id) {
            $empresa = $empresasRepository->find($id);
            if (!$empresa) {
                return $this->respondNotFound('Empresas con ese id no encontrado');
            }
            $empresas[] = $empresa;
        }

        $em = $doctrine->getManager();

        // Finalmente eliminar
        foreach ($empresas as $empresa) {
            $em->remove($empresa);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible eliminar tu empresa ' . $e);
        }

        return $this->respondWithSuccess('Empresass eliminados correctamente');
    }

   /**
    *
    * Checks if all needed parameters are present or not
    *
    * @param mixed $empresaJson
    *
    * @return [type]
    */
    private function allNeededParametersPresent($empresaJson): string
    {
        $parameters = ['nombre','cif',];

        foreach ($parameters as $param) {
            if ($empresaJson->get($param) === null) {
                return $param;
            }
        }

        return '';
    }
}
