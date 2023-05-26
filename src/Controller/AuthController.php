<?php

namespace App\Controller;

use App\Repository\EmpleadoRepository;
use App\Entity\Empleado;
use App\Entity\RecuperacionCuenta;
use App\Repository\RecuperacionCuentaRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Controller\ApiController;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends ApiController
{

    /**
     * @OA\Post(
     *   path="/employee",
     *   summary="Creación de un empleado nuevo",
     *   security={ {"bearer": {} }},
     *   tags={"No authorization needed"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información del nuevo empleado",
     *     @OA\JsonContent(
     *       required={"email","name","surname","phone","birthDate",},
     *       @OA\Property(property="email", type="string", example="string"),
     *       @OA\Property(property="password", type="string", example="string"),
     *       @OA\Property(property="name", type="string", example="string"),
     *       @OA\Property(property="surname", type="string", example="string"),
     *       @OA\Property(property="phone", type="string", example="string"),
     *       @OA\Property(property="gender", type="string", example="string"),
     *       @OA\Property(property="birthDate", type="datetime", example="datetime"),
     *       @OA\Property(property="creationDate", type="datetime", example="datetime"),
     *       @OA\Property(property="lastConnection", type="datetime", example="datetime"),
     *       @OA\Property(property="dni", type="string", example="string"),
     *       @OA\Property(property="profileImage", type="string", example="string"),
     *       @OA\Property(property="marketingMails", type="boolean", example="boolean"),
     *       @OA\Property(property="centers", type="relation", example="relation"),
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
     *     description="El user ha sido creado correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "El user ha sido editado correctamente",
     *         "data": {
     *             "id": "3",
     *             "email": "string",
     *             "roles": "json",
     *             "password": "string",
     *             "name": "string",
     *             "surname": "string",
     *             "phone": "string",
     *             "gender": "string",
     *             "birthDate": "datetime",
     *             "creationDate": "datetime",
     *             "lastConnection": "datetime",
     *             "dni": "string",
     *             "profileImage": "string",
     *             "marketingMails": "boolean",
     *             "center": "relation",
     *          },
     *       },
     *       @OA\Items(
     *         @OA\Property(
     *           property="id",
     *           type="integer",
     *           example=3
     *         ),
     *         @OA\Property(
     *           property="email",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="roles",
     *           type="json",
     *           example="json"
     *         ),
     *         @OA\Property(
     *           property="password",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="name",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="surname",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="phone",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="gender",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="birthDate",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="creationDate",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="lastConnection",
     *           type="datetime",
     *           example="datetime"
     *         ),
     *         @OA\Property(
     *           property="dni",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="profileImage",
     *           type="string",
     *           example="string"
     *         ),
     *         @OA\Property(
     *           property="marketingMails",
     *           type="boolean",
     *           example="boolean"
     *         ),
     *         @OA\Property(
     *           property="centers",
     *           type="relation",
     *           example="relation"
     *         ),
     *       )
     *     )
     *   )
     * )
     * @param Request $request
     * @param mixed $rol
     *
     * @Route("/employee", name="app_employee_add", methods={"POST"})
     */
    public function addEmpleado(Request $request, UserPasswordHasherInterface $passwordHasher, EmpleadoRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $request = $this->transformJsonBody($request);

        $missingParameter = $this->allNeededParametersEmpleado($request);
        if ($missingParameter) {
            return $this->respondValidationError('Invalid data, missing parameter ' . $missingParameter);
        }

        $em = $doctrine->getManager();
        // Comprobar que el correo es válido
        // $email = $request->get('correo');
        // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //     return $this->respondValidationError("Correo no válido");
        // }

        // $userExists = $userRepository->findOneBy(array('dni' => $dni));
        // if ($userExists) {
        //     return $this->respondValidationError("El correo ya existe");
        // }

        $userRepository->addFromRequest($request,false,$passwordHasher);

        $em = $doctrine->getManager();

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->respondValidationError('No ha sido posible guardar tu entidad ' . $e);
        }

        return $this->respondWithSuccess('Usuario creado correctamente');
    }

    /**
     * @OA\Post(
     *   path="/recuperar/password",
     *   summary="Recuperar contraseña",
     *   security={ {"bearer": {} }},
     *   tags={"Empleado"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Información del nuevo empleado",
     *     @OA\JsonContent(
     *       required={"correo",},
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
     *     description="La contraseña se ha regenerado correctamente",
     *     @OA\JsonContent(
     *       type="array",
     *       example={
     *         "message": "La contraseña se ha regenerado correctamente",
     *         "data": {
     *             "correo": "string",
     *          },
     *       },
     *       @OA\Items(
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
     * @Route("/recuperar/password", name="recuperar_contrasenya", methods={"POST"})
     */
    public function recuperarPassword(Request $request, EmpleadoRepository $empleadoRepository, UserPasswordEncoderInterface $encoder, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $request = $this->transformJsonBody($request);

        $correo = $request->get('correo');

        if (!StaticUtilities::dataIsValid($correo)) {
            return $this->respondValidationError("Datos no válidos");
        }

        $user = $empleadoRepository->findOneBy(array('correo' => $correo));
        if (!StaticUtilities::dataIsValid($user)) {
            return $this->respondValidationError("Correo no encontrado");
        }

        $password = AuthController::randomPassword();

        $this->sendEmailNewPassword($password, $user);


        // Persistir la nueva contraseña
        $user->setPassword($encoder->encodePassword($user, $password));
        $em->flush();


        return $this->respondWithSuccess('Email enviado con éxito');
    }

    private function sendEmailNewPassword($password, Empleado $user)
    {

        $subject = StaticUtilities::$NEW_PASSWORD_SENT_MAIL_SUBJECT;
        $body = StaticUtilities::getNewPasswordSentEmailBody($user->getNombre(), $password, StaticUtilities::getURLNexo());

        StaticUtilities::sendEmail($user->getCorreo(), $subject, $body);
    }



    /**
     * @OA\Post(
     *   path="/cambiarPassword",
     *   summary="Cambio de contraseña de una cuenta",
     *   tags={"No authorization needed"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Token y nueva contraseña",
     *     @OA\JsonContent(
     *       required={"recover_token","new_password"},
     *       @OA\Property(property="recover_token", type="string", example="ZHjyt79MliaQ7lNeMbi4k32MvHcyut+ypN0BDsO+BNXO5nUb+A+f/JYur8KKBrO4%7C3kAmrA60ALEMKIAGjKAveQ=="),
     *       @OA\Property(property="new_password", type="string", example="a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3 (hasheado en SHA256)")
     *     ),
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Alguna de las propiedades del request body no es válida o bien el token ya ha caducado o ha sido usado",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example="422"),
     *       @OA\Property(property="message", type="string", example="Datos no válidos")
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="El user ha cambiado su contraseña con éxito",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example="200"),
     *       @OA\Property(property="message", type="string", example="Password cambiado con éxito"),
     *     )
     *   )
     * )
     * @Route("cambiarPassword", name="cambiar_password", methods={"POST"})
     */
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EmpleadoRepository $userRepository, RecuperacionCuentaRepository $recuperacionCuentaRepository, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $request = $this->transformJsonBody($request);
        $recover_token = $request->get('recover_token');
        $new_password = $request->get('new_password');
        $nombre = $request->get('nombre');

        if (!StaticUtilities::dataIsValid($recover_token) || !StaticUtilities::dataIsValid($new_password)) {
            return $this->respondValidationError("Alguno de los parámetros no es válido");
        }

        $recuperacionCuenta = $recuperacionCuentaRepository->findOneBy(array('tokenDeRecuperacion' => $recover_token));
        if (!isset($recuperacionCuenta)) {
            return $this->respondValidationError("token no existe");
        }
        if ($recuperacionCuenta->getUsado()) {
            return $this->respondValidationError("Token de recuperación ya usado");
        }

        $decrypted = StaticUtilities::getDecryptedRecoverToken($recover_token);
        if (!$decrypted) {
            return $this->respondValidationError("token no válido");
        }

        list($email, $fecha) = explode('___', $decrypted);
        $user = $userRepository->findOneBy(array('correo' => $email));
        if (!StaticUtilities::dataIsValid($user)) {
            return $this->respondValidationError("Correo no encontrado");
        }

        if (intval($fecha) < time()) {
            return $this->respondValidationError("Token de recuperación caducado");
        }

        $user->setPassword($passwordHasher->hashPassword($user, $new_password));
        $recuperacionCuenta->setUsado(true);
        $em->persist($user);
        $em->flush();
        return $this->respondWithSuccess('Contraseña cambiada con éxito');
    }


    /**
     * @OA\Post(
     *   path="/login/user",
     *   tags={"No authorization needed"},
     *   summary="Login que devuelve token",
     *   @OA\RequestBody(
     *     required=true,
     *     description="Credenciales de acceso",
     *     @OA\JsonContent(
     *       required={"username", "password"},
     *       @OA\Property(property="username", type="string", format="email", example="user@dominio.com"),
     *       @OA\Property(property="password", type="string", format="password", example="a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3 (hasheado en SHA256)"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Las credenciales no son correctas",
     *     @OA\JsonContent(
     *       @OA\Property(property="code", type="integer", example=401),
     *       @OA\Property(property="message", type="string", example="Invalid credentials."),
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="Token de seguridad devuelto",
     *     @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MDE4OTQyNjksImV4cCI6MTYwMTg5Nzg2OSwicm9sZXMiOlsiUk9MRV9BRE1JTiJdLCJ1c2VybmFtZSI6ImFkbWluQHplYnJhLmNvbSJ9.F2R9rqAe45L51fbjZ3I0r1KV4kGdfa4VkuZUkEyxvWQVNVk8J7fTdQd6oL563CGieCzIGrSHao2WUEF4Jia7be65rLbmWolowWzQfyhfUeXk6Lx45nNfBuOayaYD2fJAJUAuK7HMcMkpFfVCsNe2iXAHgGO9Sls6siPX_MAv3VNtUWevOOBnjhXIv8UmffHtmnA9ndXNNUmgjemvtWU2ZQTlevweVPrTZgfrNMioFY8cG9gMOUyCfi_xJuuW41YVoossxGE1lf618stw3uggUpZl4S3eDrUfuw24Gt_TiBEq0YI0sZRVJF8knczxzLZjS3cgWlWX49kELXkuO2OTYTrWeOaverDPiucicANVG267f_p_zfS3TIX-oOveCXfYaTEOSYbgGsm-e16CGllaXkXlkj2jMibCJHr7ITZH5ZuesC8yvWgdTrk03c9tbJ41qoIkTEgculwuQaecD_50yOZCb0vBG6MuMKh4c6RrEQWEw1-oLjOt2Ox0kIvgrem4-ZOvs8nlEvIDdPelqJADiGweKgEZUC7p6GLxrwvHuwfDUQrixVpExR-UXdkl6OwW4m37ik8CJwp98msiXRSGWkcpOvzKXBJd4SSeWx2yJpBhXyd5PDbbGcopxW8LEe6QK6vx5cvvGmKltgJ4rr2-1PnVd6aOiMaR31CEprFK3xg"),
     *     )
     *   )
     * )
     * @param UserInterface $user
     * @param JWTTokenManagerInterface $JWTManager
     * @return JsonResponse
     */
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager)
    {
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }

    /**
     * @OA\Post(
     *   path="/api/refreshToken",
     *   summary="Actualizar token de seguridad",
     *   tags={"No authorization needed"},
     *   security={ {"bearer": {} }},
     *   @OA\Response(
     *     response="200",
     *     description="El token ha sido actualizado con éxito",
     *     @OA\JsonContent(
     *       @OA\Property(property="refresh_token", type="string", example="f731efc553108b8940b8028aa2f9546ba052d4399efcf835ccf3d270a35000c073b88df133f108a0af1a4799f5bf0614df845b0b76f1b07dd21b54f4cd366383"),
     *     )
     *   )
     * )
     * @param JWTTokenManagerInterface $JWTManager
     * @return JsonResponse
     * @Route("api/documentado", name="documentado", methods={"POST"})
     */
    public function refreshTokenUser(JWTTokenManagerInterface $JWTManager)
    {
        // Esta clase solo sirve para documentación
    }


    /**
     *
     * Checks if all needed parameters are present or not
     *
     * @param mixed $userJson
     *
     * @return [type]
     */
    private function allNeededParametersEmpleado($userJson): string
    {
        $parameters = ['dni'];
        foreach ($parameters as $param) {
            if ($userJson->get($param) == null) {
                return $param;
            }
        }
        return '';
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
    
}
