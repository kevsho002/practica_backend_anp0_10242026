<?php
$ruta_absoluta = dirname(__DIR__, 1);
require_once($ruta_absoluta."/db/conn.class.php");
require_once($ruta_absoluta."/inc/validaciones.inc.php");
class Usuario{
    
    public $idusuario;
    public $usuario;
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

    public function setIdUsuario($idusuario){
        $this->idusuario = intval($idusuario);
    }

    public function getIdUsuario(){
        return intval($this->idusuario);
    }

    public function setUsuario($usuario){
        $this->usuario = $usuario;
    }

    public function getUsuario(){
        return $this->usuario;
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

    public function obtenerUsuarios(){
        $resultado = $this->conexion->run('SELECT * FROM usuario');
        if($this->conexion->n > 0){
            $array = array("mensaje"=>"Registros encontrados","valores"=>$resultado->fetchAll());
            return $array;
        }else{
            $array = array("mensaje"=>"No se encontraron registros","valores"=>"");
            return $array;
        }
    }

    public function obtenerUsuario(int $idusuario){
        if($idusuario > 0){
            $resultado = $this->conexion->run('SELECT * FROM usuario WHERE id_usuario ='.$idusuario);
            $array = array("mensaje"=>"Registros encontrados","valores"=>$resultado->fetch());
            return $array;
        }else{
            $array = array("mensaje"=>"No se puede ejecutar la consulta, el parámetro ID es incorrecto","valores"=>"");
            return $array;
        }
    }

    public function nuevoUsuario($usuario){
        if(!empty($usuario)){
            $parametros = array(
                "usuario"=>$usuario
            );
            $resultado = $this->conexion->run('INSERT INTO usuario (usuario) VALUES (:usuario)',$parametros);
            if($this->conexion->n > 0 and $this->conexion->id > 0){
                //Si se hizo un insert
                $resultado = $this->obtenerUsuario($this->conexion->id);
                $array = array("status"=>true,"mensaje"=>"Se ha registrado el usuario correctamente","valores"=>$resultado["valores"]);
                return $array;
            }else{   
                //no se hizo insert
                $array = array("status"=>false,"mensaje"=>"Hubo un problema al registrar el usuariooooooo","valores"=>"");
                return $array;
            }   
        }else{
            $array = array("status"=>false,"mensaje"=>"No se puede ejecutar la consulta, el parámetro USUARIO es incorrecto","valores"=>"");
            return $array;
        }
        return $array; 
    }

    public function inactivarUsuario(int $idusuario){
        if($idusuario > 0){
            $resultado = $this->conexion->run('UPDATE usuario SET estado_usuario = 0 WHERE id_usuario = :id_usuario',array("id_usuario"=>$idusuario));
            $array = array("mensaje"=>"Se ha inactivado el usuario correctamente","valores"=>"");
            return $array;
        }else{
            $array = array("mensaje"=>"No se puede ejecutar la consulta, el parámetro ID es incorrecto","valores"=>"");
            return $array;
        }
    }
}
?>