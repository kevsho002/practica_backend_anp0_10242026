<?php
$ruta_absoluta = dirname(__DIR__, 1);
require_once($ruta_absoluta."/db/conn.class.php");
require_once($ruta_absoluta."/inc/validaciones.inc.php");
class Password{
    public $idpassword;
    public $password;
    public $fecharegistro;
    public $estadoregistro;
    public $conexion;
    public $validacion;

    public function __construct(){
        $ruta_absoluta = dirname(__DIR__, 1);
        $this->conexion = new DB();
        $this->validacion = new Validaciones();
        $this->validacion->cargarEnv($ruta_absoluta . '/.env');
    }

    public function setIdPassword($idpassword){
        $this->idpassword = intval($idpassword);
    }
    public function getIdPassword(){
        return intval($this->idpassword);
    }
    public function setPassword($password){
        $this->password = $password;
    }
    public function getPassword(){
        return $this->password;
    }
    public function setFechaRegistro($fecharegistro){
        $this->fecharegistro = $fecharegistro;
    }
    public function getFechaRegistro(){
        return $this->fecharegistro;
    }
    public function setEstadoRegistro($estadoregistro){
        $this->estadoregistro = $estadoregistro;
    }
    public function getEstadoRegistro(){
        return $this->estadoregistro;
    }


    public function nuevoPassword(int $idusuario,$password){
        if($idusuario > 0){
            if(strlen($password) < getenv('PASSWORD_MIN_LENGTH')){
                $array = array("status"=>false,"mensaje"=>"La contraseña debe tener al menos ".getenv('PASSWORD_MIN_LENGTH')." caracteres","valores"=>"");
                return $array;
            }elseif(strlen($password) > getenv('PASSWORD_MAX_LENGTH')){
                $array = array("status"=>false,"mensaje"=>"La contraseña debe tener un máximo de ".getenv('PASSWORD_MIN_LENGTH')." caracteres","valores"=>"");
                return $array;
            }else{
                $parametros = array(
                    "password"=>$password,
                    "iduser" => $idusuario
                );
                $resultado = $this->conexion->run('INSERT INTO usuario_password (password, id_usuario) VALUES ( SHA2(:password,512) , :iduser)', $parametros);
                $array = array("status"=>true,"mensaje"=>"Password creado correctamente","valores"=>$this->conexion->lastInsertId());
                return $array;
            }
        }else{
            $array = array("status"=>false,"mensaje"=>"No se ha enviado el id de usuario","valores"=>"");
            return $array;
        }
    }
}
?>