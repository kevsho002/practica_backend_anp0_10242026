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
}
$estudiante = new Estudiante();
$datos = $estudiante->obtenerEstudiantes();
header("content-type: Application/json");
echo(json_encode($datos));
?>