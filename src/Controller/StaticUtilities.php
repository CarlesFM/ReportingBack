<?php

namespace App\Controller;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class StaticUtilities
{
    /******************/
    /*** USER ROLES ***/
    /******************/
    public static $ROLE_USER = "ROLE_USER";


    /*******************/
    /*** FILES PATHS ***/
    /*******************/
    public static $USER_IMG_PATH = "img/usuarios/";
    public static $EMPRESA_IMG_PATH = "img/logotipos_plataformas/";
    public static $ARCHIVOS_DOCS_PATH = "archivos_docs/";


    /*****************************/
    /*** ENVIRONMENT DEPENDENT VARIABLES ***/
    /*****************************/
    public static $PRODUCTION = false;
    public static $SITE_GROUND_URL = "https://nexo.easyfresh.tk";
    public static $PRODUCTION_URL = "https://erp.nexotelecom.es";

    /************************/
    /*** GOOGLE API LOGIN ***/
    /************************/
    public static $CLIENT_ID = '405922306465-vqljddmm5d17tf8qvlq0g33t70h3t0h5.apps.googleusercontent.com';
    public static $CLIENT_SECRET = "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";

    /************************/
    /*** IMAGE PROPERTIES ***/
    /************************/
    private static $ANCHO_IMG_MAX = 500;

    /**
     * Parses the specified role into a human readable one
     *
     * @param mixed $role
     *
     * @return [type] parsed role name
     */
    public static function parseRolesNames($role)
    {
        $parsedRole = $role;
        switch ($role) {
            case StaticUtilities::$ROLE_USER:
                $parsedRole = 'User';
                break;
        }

        return $parsedRole;
    }

    /**
     * Dependiendo de si la variable de "PRODUCTION" está a true o false,
     * devolvemos la URL de Nexo de producción o de SiteGround
     *
     * @return [type]
     */
    public static function getURLNexo()
    {
        if (StaticUtilities::$PRODUCTION) {
            return StaticUtilities::$PRODUCTION_URL;
        }

        return StaticUtilities::$SITE_GROUND_URL;
    }

    public static function getAlias()
    {
        $ADMIN_MAIL_DIR = StaticUtilities::$PRODUCTION ? "noreply@grupomemorable.com" : "proyectos@quasardynamics.com";

        return $ADMIN_MAIL_DIR;
    }

    /****************************/
    /*** RECOVER ACCOUNT DATA ***/
    /****************************/
    private static $RECOVER_ENC_METHOD = "AES-256-CBC";
    private static $RECOVER_ENC_KEY = "t%~B^g%Q~Q]2Aw6S%V;R2DJnXj*Xcm2{#3y6+\^-Ts~:K*Kq^g5!Pj.~6F~R.>m#";
    private static $RECOVER_ENC_URL = "https://quasar-ar.quasardynamics.com/index.php?token=";
    private static $RECOVER_ENC_EXPIRATION_MARGIN = 24 * 60 * 60;

    public static function getRecoverData($email)
    {
        $length = openssl_cipher_iv_length(StaticUtilities::$RECOVER_ENC_METHOD);
        $iv = openssl_random_pseudo_bytes($length);
        $time = time() + StaticUtilities::$RECOVER_ENC_EXPIRATION_MARGIN;
        $plainText = $email . "___" . $time;
        $encrypted = openssl_encrypt($plainText, StaticUtilities::$RECOVER_ENC_METHOD, StaticUtilities::$RECOVER_ENC_KEY, OPENSSL_RAW_DATA, $iv);
        $token = base64_encode($encrypted) . '|' . base64_encode($iv);
        $url = StaticUtilities::$RECOVER_ENC_URL . $token;
        return array($url, $time, $token);
    }

    public static function getDecryptedRecoverToken($recover_token)
    {
        list($data, $iv) = explode('|', $recover_token);
        $iv = base64_decode($iv);
        $decrypted = openssl_decrypt($data, StaticUtilities::$RECOVER_ENC_METHOD, StaticUtilities::$RECOVER_ENC_KEY, 0, $iv);
        return $decrypted;
    }

    /*****************/
    /*** MAIL DATA ***/
    /*****************/
    public static $RECOVER_MAIL_SUBJECT = "Recuperación de cuenta Nexo";
    public static $WELCOME_MAIL_SUBJECT = "¡Le damos la bienvenida!";
    public static $NEW_PASSWORD_SENT_MAIL_SUBJECT = "Generada nueva contraseña";
    public static $NEW_ADMIN_EMPRESA_MAIL_SUBJECT = "Administración de empresa";
    public static $RESPONSABLE_DPTO_MAIL_SUBJECT = "Responsable de departamento";
    public static $EMPLEADO_MANAGER_REMOVED_MAIL_SUBJECT = "Cambio de mánagers de departamento";

    /**
     * Send email to user with subject and html body
     * 
     * @param mixed $to
     * @param mixed $subject
     * @param mixed $htmlBody
     * 
     * @return [type]
     */
    public static function sendEmail($to, $subject, $htmlBody, $cc = 'false')
    {
        $email = (new Email())
            ->from(StaticUtilities::getAlias())
            ->subject($subject)
            ->html($htmlBody);

        if (is_array($to)) {
            $email->bcc(...$to);
        } else {
            $email->to($to);
        }
        if ($cc != 'false') {
            $email->cc($cc);
        };

        $mailer = new Mailer(Transport::fromDsn($_ENV['MAILER_DSN']));

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        return true;
    }


    public static function getRecoverEmailBody($nombre, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./recover_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./recover_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getWelcomeEmailBody($nombre, $cargo, $password, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./welcome_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./welcome_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_CARGO_-", $cargo, $body);
        $body = str_replace("-_PASSWORD_-", $password, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getNewPasswordSentEmailBody($nombre, $password, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./new_password_sent_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./new_password_sent_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_PASSWORD_-", $password, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getNuevoAdminEmpresaEmailBody($nombre, $empresa, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./new_admin_empresa_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./new_admin_empresa_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_EMPRESA_-", $empresa, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getResponsableDptoEmailBody($nombre, $dptoYEmpresa, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./responsable_dpto_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./responsable_dpto_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_DPTOYEMPRESA_-", $dptoYEmpresa, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getEmpleadoManagerRemovedEmailBody($nombre, $departamentosAfectados, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./empleado_manager_removed_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./empleado_manager_removed_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_DEPARTAMENTOS_-", $departamentosAfectados, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getRemoveAdminEmpresaEmailBody($nombre, $empresaAfectada, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./manager_removed_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./manager_removed_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_EMPRESA_-", $empresaAfectada, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }


    /***********************/
    /*** DATA VALIDATION ***/
    /***********************/
    public static function dataIsValid($data)
    {
        switch (gettype($data)) {
            case 'string':
                return isset($data) && !empty($data);
                break;
            case 'boolean':
            case 'integer':
            case 'double':
                return !is_null($data);
                break;
            case 'array':
                return isset($data);
                break;
            case 'NULL':
                return false;
                break;
            default:
                return isset($data);
                break;
        }
    }

    public static function arrayOfIdsIsValid($array)
    {
        if (gettype($array) != 'array') {
            return false;
        }
        if (sizeof($array) == 0) {
            return false;
        }
        foreach ($array as $element) {
            if (!is_numeric($element)) {
                return false;
            }
        }
        return true;
    }

    public static function colorIsValid($color)
    {
        $validCharacters = str_split('#0123456789ABCDEF');
        $isString = gettype($color) == 'string';
        $formatIsValid = true;
        $splittedColor = str_split(strtoupper($color));
        foreach ($splittedColor as $colorCharacter) {
            $formatIsValid = $formatIsValid && in_array($colorCharacter, $validCharacters);
        }
        $formatIsValid = $formatIsValid && sizeof($splittedColor) == 7;
        return $isString && $formatIsValid;
    }

    public static function dateIsValid($date, $format = 'Y/m/d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Comprueba que la fecha inicial y final son válidas, además de que fecha inicial no puede ser después de fecha fin
     *
     * @param mixed $initialDate
     * @param mixed $finalDate
     *
     * @return [type]
     */
    public static function checkCorrectDates($initialDate, $finalDate)
    {
        if (!StaticUtilities::dateIsValid($initialDate)) {
            return "Fecha inicial no válida";
        }
        if (!StaticUtilities::dateIsValid($finalDate)) {
            return "Fecha final no válida";
        }

        if ($initialDate > $finalDate) {
            return "Periodo de fechas inconsistente";
        }

        return  "";
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public static function randomDigitsPassword()
    {
        $alphabet = '1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 7; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Comprueba que el base64 enviado es el de una imagen
     *
     * @param mixed $base64_string
     * @param mixed $id_archivo
     *
     * @return [type]
     */
    public static function checkImagen($base64_string, $id_archivo)
    {
        $extension = StaticUtilities::string_between_two_string($base64_string, 'image/', ';base64');
        if (!$extension) {
            return false;
        }

        $file = 'temp' . $id_archivo . '.' . $extension;
        $tmp = fopen($file, "wb");
        $data = explode(',', $base64_string);

        if (!$data || count($data) < 2) {
            return false;
        }

        $written = fwrite($tmp, base64_decode($data[1]));
        fclose($tmp);

        if (!$written) {
            return false;
        }
        if (filesize($file) > 1048576) { // 1MB
            return false;
        }
        $path_info = pathinfo($file);
        switch ($path_info['extension']) {
            case "jpg":
            case "JPG":
            case "jpeg":
            case "JPEG":
            case "png":
            case "PNG":
            case "gif":
            case "GIF":
            case "bmp":
            case "BMP":
                return $file;
                break;
            default:
                return false;
        }
    }

    /**
     * Guardar archivo imagen en esa ruta especificada
     *
     * @param mixed $file
     * @param mixed $path
     *
     * @return [type]
     */
    public static function setImagen($file, $path)
    {
        try {
            $path_info = pathinfo($file);
            switch ($path_info['extension']) {
                case "jpg":
                case "JPG":
                case "jpeg":
                case "JPEG":
                    $imagen = imagecreatefromjpeg($file);
                    break;
                case "png":
                case "PNG":
                    $imagen = imagecreatefrompng($file);
                    break;
                case "gif":
                case "GIF":
                    $imagen = imagecreatefromgif($file);
                    break;
                case "bmp":
                case "BMP":
                    $imagen = imagecreatefromwbmp($file);
                    break;
            }
            $sizes = getimagesize($file);
            if ($sizes[0] > StaticUtilities::$ANCHO_IMG_MAX) {
                $nuevo_ancho = StaticUtilities::$ANCHO_IMG_MAX;
                $nuevo_alto = StaticUtilities::$ANCHO_IMG_MAX * $sizes[1] / $sizes[0];
            } else {
                $nuevo_ancho = $sizes[0];
                $nuevo_alto = $sizes[1];
            }
            $thumb = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $nuevo_ancho, $nuevo_alto, $transparent);

            imagecopyresampled($thumb, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $sizes[0], $sizes[1]);
            unlink($file);

            return imagepng($thumb, $path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $str
     * @param mixed $starting_word
     * @param mixed $ending_word
     *
     * @return [type]
     */
    public static function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        $subtring_start += strlen($starting_word);
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        return substr($str, $subtring_start, $size);
    }


    /**
     * Uploads the base 64 image to the specified file path using a file name and
     * an id for creating unique file names (file1.png)
     *
     * @param mixed $base64Request
     * @param mixed $filePath
     * @param mixed $fileName
     * @param mixed $id
     *
     * @return [type]
     */
    public static function uploadImage($base64Request, $filePath, $fileName, $id)
    {
        if (StaticUtilities::dataIsValid($base64Request)) {
            $file = StaticUtilities::checkImagen($base64Request, $fileName . $id);
            if ($file) {
                // Crear carpeta si no existe
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0777, true);
                }
                $relativeUrl = $filePath . $fileName . $id . ".png";
                StaticUtilities::setImagen($file, $relativeUrl);
                return StaticUtilities::getServerPublicFolder() . '/' . $relativeUrl;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns the server public folder (http://localhost/memorable/public)
     *
     * @return string
     */
    public static function getServerPublicFolder(): string
    {

        $cwd = str_replace("\\", '/', getcwd());
        $cwd = explode("/", $cwd);
        $documentRoot = str_replace("\\", '/', $_SERVER['DOCUMENT_ROOT']);
        $documentRoot = explode("/", $_SERVER['DOCUMENT_ROOT']);

        $relative = [];
        foreach (array_reverse($cwd) as $currentDir) {
            if ($currentDir == end($documentRoot)) {
                break;
            }
            array_unshift($relative, $currentDir);
        }
        $relative = implode("/", $relative);

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . $relative;
    }

    /**
     * @param mixed $urlImage
     *
     * @return [type]
     */
    public function convertUrFileToBase64($urlImage)
    {
        // Get the image and convert into string
        $img = file_get_contents($urlImage);

        // Encode the image string data into base64
        return base64_encode($img);
    }
}
