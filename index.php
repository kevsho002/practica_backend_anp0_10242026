<?php  

//HABILITANDO CORS PARA DIFERENTES DOMINIOS Y DIFERENTES TIPOS DE REQUESTS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Header: X-API-KEY, Origin, X-Request-With, Content-Type, Accept, Access-Cotrol-Request-Method");
header("Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Credentials: true");
header("Allow: GET,POST,OPTIONS,PUT,DELETE");


//NECESITO UTILIZAR LA CLASE ESTUDIANTE (para heredar todas sus propieddes y metodos)
require("classes/estudiante.class.php");

$Estudiante = new Estudiante();
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    // Obtener el tipo de petición
    $tipo_peticion = (isset($_GET["t"]) && $_GET["t"] !== "")
        ? $_GET["t"]
        : null;

    switch ($tipo_peticion) {

        case "all":
            // DEVOLVER TODOS LOS DATOS DE LA TABLA ESTUDIANTE
            $resultado = $Estudiante->obtenerEstudiantes();
            break;

        case "unique":
            // DEVOLVER UN ÚNICO ESTUDIANTE SEGÚN SU IDENTIFICADOR
            //DISTINCION DE LA URL O ENVIADOS COMO PARAMETROS DEL ENDPOINT
            $id =   (   ( isset($_GET["id"]))    ?   (  ($_GET["id"]!= "") ?   intval($_GET["id"])  : 0)    :   0);
            if($id>0){
                $resultado  = $Estudiante->obtenerEstudiante($id);
            }else{
                header("HTTP/1.1 412 Precondition Failed");
                $resultado = array(
                "mensaje" => "Por favor, indique el id del estudiante que usted esta buscando",
                "valores" => ""
            );
            }
            break;

        case "new":
            // INSERTAR UN NUEVO ESTUDIANTE POR METODO GET CON PARAMETROS DE URL
            if(array_key_exists("fecha_nac",$_GET) && array_key_exists("id_genero", $_GET)){

                //SANEANDO LOS VALORES NO OBLIIGAORIOS
                $carnet = ((isset ($_GET["carnet"]))    ?   $_GET["carnet"] : null);
                $anioingreso = ((isset ($_GET["anio_ingreso"]))    ?   intval($_GET["anio_ingreso"]) : 0);
                $resultado = $Estudiante->crearEstudiante($_GET["fecha_nac"],$_GET["id_genero"],$carnet,$anioingreso);            
            }else{
                header("HTTP/1.1 400 Bad Request");
                $resultado = array(
                    "mensaje" => "Existen campos obligatorios que no han sido enviados",
                    "valores" => ""
                );
            }
            break;

        default:
            // TIPO DE PETICIÓN NO RECONOCIDO
            header("HTTP/1.1 403 Forbidden");
            $resultado = array(
                "mensaje" => "¿Qué haremos?",
                "valores" => ""
            );
            break;
    }

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

// INSERTAR UN NUEVO ESTUDIANTE POR METODO POST CON PARAMETROS DE URL
           if(array_key_exists("fecha_nac",$_POST) && array_key_exists("id_genero", $_POST)){

//SANEANDO LOS VALORES NO OBLIIGAORIOS
                $carnet = ((isset ($_POST["carnet"]))    ?   $_POST["carnet"] : null);
                $anioingreso = ((isset ($_POST["anio_ingreso"]))    ?   intval($_POST["anio_ingreso"]) : 0);
                $resultado = $Estudiante->crearEstudiante($_POST["fecha_nac"],$_POST["id_genero"],$carnet,$anioingreso);            
           }else{
                header("HTTP/1.1 400 Bad Request");
                $resultado = array(
                    "mensaje" => "Existen campos obligatorios que no han sido enviados",
                    "valores" => ""
                );
            }

} else {

    // MÉTODO HTTP NO PERMITIDO
    $resultado = array(
        "mensaje" => "¿Qué método usaremos?",
        "valores" => ""
    );
}

// DEVOLVER RESPUESTA JSON
header("Content-Type: application/json; charset=utf-8");

echo json_encode($resultado);
?>