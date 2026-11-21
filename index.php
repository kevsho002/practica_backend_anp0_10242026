<?php  

// HABILITANDO CORS PARA DIFERENTES DOMINIOS Y DIFERENTES TIPOS DE REQUESTS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Credentials: true");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// IMPORTANDO CLASES (usando require_once para evitar duplicados)
require_once("classes/estudiante.class.php");
require_once("classes/usuario.class.php");
require_once("classes/password.class.php");
require_once("classes/login.class.php");
require_once("inc/validaciones.inc.php");
require_once("inc/whitelist.inc.php");

$Estudiante = new Estudiante();
$Usuario = new Usuario();
$Password = new Password();
$Login = new Login();
$Validaciones = new Validaciones();


$ip = $Validaciones->ip_in_ranges($_SERVER["REMOTE_ADDR"], $rango);
$metodos_permitido = array("GET", "POST");
$tipo_peticion = (isset($_REQUEST["t"]) && $_REQUEST["t"] !== "") ? $_REQUEST["t"] : null;
$resultado = array();
//$tipo_peticion = (isset($_REQUEST["t"]) && $_REQUEST["t"] !== "") ? $_REQUEST["t"] : null;

if ($ip === true) {
    
    if (in_array($_SERVER["REQUEST_METHOD"], $metodos_permitido)) {
        
        if ($_SERVER["REQUEST_METHOD"] === "GET") {

            switch ($tipo_peticion) {

                case "all":
                    $resultado = $Estudiante->obtenerEstudiantes();
                    break;

                case "unique":
                    $id = (isset($_GET["id"]) && $_GET["id"] !== "") ? intval($_GET["id"]) : 0;
                    if ($id > 0) {
                        $resultado = $Estudiante->obtenerEstudiante($id);
                    } else {
                        header("HTTP/1.1 412 Precondition Failed");
                        $resultado = array(
                            "mensaje" => "Por favor, indique el id del estudiante que usted está buscando",
                            "valores" => ""
                        );
                    }
                    break;

                case "new":
                    if (array_key_exists("fecha_nac", $_GET) && array_key_exists("id_genero", $_GET)) {
                        $carnet = isset($_GET["carnet"]) ? $_GET["carnet"] : null;
                        $anioingreso = isset($_GET["anio_ingreso"]) ? intval($_GET["anio_ingreso"]) : 0;
                        $resultado = $Estudiante->crearEstudiante($_GET["fecha_nac"], $_GET["id_genero"], $carnet, $anioingreso);            
                    } else {
                        header("HTTP/1.1 400 Bad Request");
                        $resultado = array(
                            "mensaje" => "Existen campos obligatorios que no han sido enviados",
                            "valores" => ""
                        );
                    }
                    break;

                default:
                    header("HTTP/1.1 403 Forbidden");
                    $resultado = array(
                        "mensaje" => "¿Qué haremos?",
                        "valores" => ""
                    );
                    break;
            }

        } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

            switch ($tipo_peticion) {
                case "new_user":
                    if (array_key_exists("txt_user", $_POST) && array_key_exists("txt_pass", $_POST)) {
                        
                        if ($_POST["txt_user"] != "" && $_POST["txt_pass"] != "") {
                            
                            // Corregido: strlen() en ambos lados y operador '>' para longitud máxima
                            $min_len = getenv('PASSWORD_MIN_LENGHT') ?: 8;
                            $max_len = getenv('PASSWORD_MAX_LENGHT') ?: 16;

                            if (strlen($_POST["txt_pass"]) < $min_len || strlen($_POST["txt_pass"]) > $max_len) {
                                header("HTTP/1.1 403 Forbidden");
                                $resultado = array(
                                    "mensaje" => "La contraseña debe tener entre $min_len y $max_len caracteres",
                                    "valores" => ""
                                );
                            } else {
                                $resultado_user = $Usuario->nuevoUsuario($_POST["txt_user"]);
                                
                                if ($resultado_user["status"]) {
                                    $resultado_pass = $Password->nuevoPassword($resultado_user["id_usuario"], $_POST["txt_pass"]);
                                    
                                    if ($resultado_pass["status"]) {
                                        $resultado = array(
                                            "status" => true,
                                            "mensaje" => "Se ha registrado el usuario correctamente",
                                            "valores" => $resultado_user["valores"]
                                        );
                                    } else {
                                        header("HTTP/1.1 400 Bad Request");
                                        $resultado = array(
                                            "mensaje" => $resultado_pass["mensaje"],
                                            "valores" => ""
                                        );
                                    }
                                } else {
                                    header("HTTP/1.1 400 Bad Request");
                                    $resultado = array(
                                        "mensaje" => $resultado_user["mensaje"],
                                        "valores" => ""
                                    );
                                }
                            }
                        } else {
                            header("HTTP/1.1 400 Bad Request");
                            $resultado = array(
                                "mensaje" => "Los campos no deben estar vacíos",
                                "valores" => ""
                            );
                        }
                    } else {
                        header("HTTP/1.1 400 Bad Request");
                        $resultado = array(
                            "mensaje" => "No se han enviado parámetros obligatorios",
                            "valores" => ""
                        );
                    }
                    break;

                case "login":
                    if (array_key_exists("txt_user", $_POST) && array_key_exists("txt_pass", $_POST)) {
                        if ($_POST["txt_user"] != "" && $_POST["txt_pass"] != "") {
                            $resultado = $Login->nuevoLogin($_POST["txt_user"], $_POST["txt_pass"]);
                        } else {
                            header("HTTP/1.1 400 Bad Request");
                            $resultado = array(
                                "mensaje" => "Los campos no deben estar vacíos",
                                "valores" => ""
                            );
                        }
                    } else {
                        header("HTTP/1.1 400 Bad Request");
                        $resultado = array(
                            "mensaje" => "No se han enviado parámetros obligatorios",
                            "valores" => ""
                        );
                    }
                    break;

                default:
                    header("HTTP/1.1 403 Forbidden");
                    $resultado = array(
                        "mensaje" => "Petición POST no válida",
                        "valores" => ""
                    );
                    break;
            }
        }
    } else {
        header("HTTP/1.1 405 Method Not Allowed");
        $resultado = array(
            "mensaje" => "Método HTTP no permitido",
            "valores" => ""
        );
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    $resultado = array(
        "mensaje" => "Acceso denegado por IP",
        "valores" => ""
    );
}

// Imprimir respuesta en formato JSON
echo json_encode($resultado);
?>
