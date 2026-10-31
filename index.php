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
            $resultado = array(
                "mensaje" => "Petición para obtener un estudiante específico",
                "valores" => ""
            );
            break;

        case "new":
            // INSERTAR UN NUEVO ESTUDIANTE
            $resultado = array(
                "mensaje" => "Petición para registrar un nuevo estudiante",
                "valores" => ""
            );
            break;

        default:
            // TIPO DE PETICIÓN NO RECONOCIDO
            $resultado = array(
                "mensaje" => "¿Qué haremos?",
                "valores" => ""
            );
            break;
    }

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

    // PROCESAR PETICIONES POST
    $resultado = array(
        "mensaje" => "Petición POST recibida",
        "valores" => ""
    );

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