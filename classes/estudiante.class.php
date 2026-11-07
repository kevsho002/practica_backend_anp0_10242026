<?php
//echo (__DIR__);
$ruta_absoluta = dirname(__DIR__,1);
//echo ($ruta_absoluta."/db/conn.class.php");
require($ruta_absoluta."/db/conn.class.php");
//require($ruta_absoluta."/inc/validaciones.inc.php");
class Estudiante{
    public $idestudiante;
    public $fechanacimiento;
    public $estadoregistroestudiante;
    public $idgenero;
    public $carnet;
    public $anioingreso;
    public $conexion;
    public $validacion;

    public function __construct(){
        $this->conexion     = new DB();
        //$this->validacion   = new validaciones();
    }
    
    //SETTER de $this->idestudiante
    public function setIdEstudiante($idestudiante){
        $this->idestudiante = intval($idestudiante);
    }

    //GETTER $this->idestudiante
        public function getIdEstudiante(){
        return intval($this->$idestudiante);
    }



    //SETTER de $this->fechanacimiento
    public function setFechaNacimiento($fechanacimiento){
        $this->fechanacimiento = $fechanacimiento;
    }

    //GETTER $this->fechanacimiento
        public function getFechaNacimiento(){
        return $this->$fechanacimiento;
    }



    //SETTER de $this->estadoregistroestudiante
    public function setEstadoRegistroEstudiante($estadoregistroestudiante){
        $this->estadoregistroestudiante = $estadoregistroestudiante;
    }

    //GETTER $this->estadoregistroestudiante
        public function getEstadoRegistroEstudiante(){
        return $this->$estadoregistroestudiante;
    }


    
    //SETTER de $this->idgenero
    public function setIdGenero($idgenero){
        $this->idgenero = $idgenero;
    }

    //GETTER $this->idgenero
        public function getIdGenero(){
        return $this->$idgenero;
    }


    
    //SETTER de $this->carnet
    public function setCarnet($carnet){
        $this->carnet = $carnet;
    }

    //GETTER $this->carnet
        public function getCarnet(){
        return $this->$carnet;
    }



    //SETTER de $this->anioingreso
    public function setAnioingreso($anioingreso){
        $this->anioingreso = intval($anioingreso);
    }

    //GETTER $this->anioingreso
        public function getAnioingreso(){
        return intval($this->$anioingreso);
    }

    public function obtenerEstudiantes(){
        $resultado = $this->conexion->run('SELECT * FROM estudiante');
        $array = array("mensaje"=>"Registros encontrados","data"=>$resultado->fetchAll());
        return $array;
    }

    public function obtenerEstudiante(int $idestudiante){
        if($idestudiante >0){
            //EJECUTAR LA CONSULTA
            $resultado = $this->conexion->run('SELECT * FROM estudiante WHERE id_estudiante='.$idestudiante);
            $array = array("mensaje"=>"Registros encontados","valores"=>$resultado->fetch());
            return $array;
        }else{
            $array = array("mensaje"=>"Registros no encontados","valores"=>"");
            return $array;
        }
    }


public function crearEstudiante($fechanacimiento, $idgenero, $carnet, $anioingreso){

    //if(!empty($fechanacimiento) && !empty($idgenero) && !empty($carnet) && !empty($anioingreso)){
    if(!empty($fechanacimiento) && !empty($idgenero)){    

        // TODOS LOS CAMPOS OBLIGATORIOS HAN SIDO ENVIADOS

        // CREANDO PARAMETROS DE DATOS
        $parametros_formulario = array(
            "fecha_nac" => $fechanacimiento,
            "id_genero" => $idgenero,
            "carnet" => $carnet,
            "anio_ingreso" => $anioingreso
        );

        $resultado = $this->conexion->run(
            'INSERT INTO estudiante(
                fecha_nacimiento_estudiante,
                id_genero,
                carnet_estudiante,
                anio_ingreso_universidad
            ) VALUES (
                :fecha_nac,
                :id_genero,
                :carnet,
                :anio_ingreso
            );',
            $parametros_formulario
        );

        if($this->conexion->n > 0 && $this->conexion->id > 0){

            // SI SE HIZO INSERT
            $resultado = $this->obtenerEstudiante($this->conexion->id);

            $array = array(
                "mensaje" => "Se ha registrado el estudiante correctamente",
                "valores" => $resultado["valores"]
            );

            return $array;

        }else{

            // NO SE HIZO INSERT
            $array = array(
                "mensaje" => "Hubo un problema al procesar la informacion",
                "valores" => ""
            );

            return $array;
        }

    }else{

        // AL MENOS UN CAMPO NO ESTA ENVIANDOSE
        $array = array(
            "mensaje" => "Existen campos obligatorios que no fueron enviados",
            "valores" => ""
        );

        return $array;
    }
}
}

?>