<?php
$ruta_absoluta = dirname(__DIR__, 1);
require_once($ruta_absoluta."/db/conn.class.php");
require_once($ruta_absoluta."/inc/validaciones.inc.php");

class Login{
    public $idlogin;
    public $token;
    public $fechalogin;
    public $estadologin;
    public $conexion;
    public $validacion;

    public function __construct(){
        $ruta_absoluta = dirname(__DIR__, 1);
        $this->conexion = new DB();
        $this->validacion = new Validaciones();
        $this->validacion->cargarEnv($ruta_absoluta . '/.env');
    }

    public function setIdLogin(int $idlogin){
        $this->idlogin = intval($idlogin);
    }

    public function getIdLogin(){
        return intval($this->idlogin);
    }

    public function setToken($token){
        $this->token = $token;
    }

    public function getToken(){
        return $this->token;
    }

    public function setFechaLogin($fechalogin){
        $this->fechalogin = $fechalogin;
    }

    public function getFechaLogin(){
        return $this->fechalogin;
    }

    public function setEstadoLogin($estadologin){
        $this->estadologin = $estadologin;
    }

    public function getEstadoLogin(){
        return $this->estadologin;
    }

    public function nuevoLogin($usuario,$password){
        if($usuario!="" && $password!=""){
            if(strlen($password) < getenv('PASSWORD_MIN_LENGTH') || strlen($password) > getenv('PASSWORD_MAX_LENGTH')){
                //LA CONTRASEÑA DEBE TENER COMO MINIMO 8 CARACTERES y DEBE TENER COMO MAXIMO 16 CARACTERES
                $array = array("status"=>false,"mensaje"=>"Credenciales incorrectas","valores"=>"");
                return $array;
            }else{
                $parametros = array(
                    "usu"=>$usuario,
                    "pass" => $password
                );
                
                $resultado = $this->conexion->run('SELECT u.id_usuario,u.usuario FROM usuario u INNER JOIN usuario_password up on u.id_usuario=up.id_usuario WHERE u.usuario=:usu and up.password = SHA2(:pass,512);',$parametros);
                $datos = $resultado->fetch();
                if($datos){
                    $token = $this->validacion->generarJWToken(getenv('JWT_PASS_KEY'),$datos["id_usuario"],$datos["usuario"],getenv('SESSION_TIMEOUT'));
                    $resultado_insert_token = $this->conexion->run('INSERT INTO login (token,id_usuario)VALUES(:token,:iduser);',array("token"=>$token,"iduser"=>$datos["id_usuario"]));
                    if($this->conexion->n > 0 and $this->conexion->id > 0){
                        $array = array("status"=>true,"mensaje"=>"Inicio de sesión correcto","token"=>$this->conexion->id."|".$token);
                        return $array;
                    }else{
                        $array = array("status"=>false,"mensaje"=>"Credenciales incorrectas","valores"=>"");
                        return $array;
                    }
                }else{
                    $array = array("status"=>false,"mensaje"=>"Credenciales incorrectas","valores"=>"");
                    return $array;
                }
            }
        }else{
            $array = array("status"=>false,"mensaje"=>"No se han enviado parámetros con valores","valores"=>"");
            return $array;
        }
    }

    public function validarToken($tokenRecibido){
        $tokenRecibido = trim((string)$tokenRecibido);
        if ($tokenRecibido === '') {
            return array("status"=>false,"mensaje"=>"No se ha enviado el token");
        }

        $jwt = $tokenRecibido;
        if (str_contains($tokenRecibido, '|')) {
            $partes = explode('|', $tokenRecibido, 2);
            $jwt = $partes[1];
        }

        $verificacion = $this->validacion->validarJWToken(getenv('JWT_PASS_KEY'), $jwt);
        if (!$verificacion["status"]) {
            return $verificacion;
        }

        $resultado = $this->conexion->run(
            'SELECT id_usuario FROM login WHERE token = :token',
            array("token"=>$jwt)
        );
        $fila = $resultado->fetch();
        if (!$fila) {
            return array("status"=>false,"mensaje"=>"El token no está registrado");
        }

        return array("status"=>true,"mensaje"=>"Token válido","valores"=>$verificacion["valores"]);
    }

}
?>