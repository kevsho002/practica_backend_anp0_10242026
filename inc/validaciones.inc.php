<?php
/**
 * Utilidades de validación, configuración y autenticación de la API.
 *
 * Agrupa comprobaciones de texto, fechas, correo y teléfono; la carga del
 * archivo .env; la creación y verificación de tokens JWT (HS256); y la
 * comprobación de direcciones IP contra rangos permitidos.
 *
 * Los métodos estáticos se llaman con Validaciones::metodo(). Los demás
 * requieren una instancia: $validacion = new Validaciones().
 */
class Validaciones{

	/**
	 * Comprueba que un texto contenga solo letras y espacios, con una longitud de 1 a 50 caracteres.
	 *
	 * @param string $str        Texto a evaluar. Los espacios están permitidos.
	 * @param bool   $mayusculas true: solo letras mayúsculas (A-Z). false: mayúsculas y minúsculas.
	 * @return bool true si el texto cumple el patrón; false si está vacío, es demasiado largo o incluye números u otros símbolos.
	 */
	public static function verificar_solo_letras($str,Bool $mayusculas=false) {
		if($mayusculas){
			return preg_match('/^[\sA-Z]{1,50}$/', $str) > 0;
		}else{
			return preg_match('/^[\sa-zA-Z]{1,50}$/', $str) > 0;
		}
	}

	/**
	 * Comprueba que una fecha tenga la forma indicada, sin verificar si el día existe en el calendario.
	 *
	 * Acepta únicamente "Y-m-d" (2026-11-18) y "d/m/Y". En el caso "d/m/Y" la
	 * expresión actual también espera guiones y un año de cuatro dígitos al inicio,
	 * así que una fecha como 18/11/2026 no coincide.
	 *
	 * @param string $fecha   Fecha en texto.
	 * @param string $formato "Y-m-d" o "d/m/Y". Cualquier otro valor devuelve false.
	 * @return bool true si el texto coincide con el patrón del formato; false en caso contrario.
	 */
	public static function verificar_formato_fecha($fecha,$formato){
		//$d = DateTime::createFromFormat($formato, $fecha);
		//return $d && $d->format($formato) === $fecha;
		$correcto = false;
		switch($formato){
			case "Y-m-d":
				$correcto = ((preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',$fecha)) === 1 ? true : false);
			break;
			case "d/m/Y":
				$correcto = ((preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',$fecha)) === 1 ? true : false);
			break;
			default:
				$correcto = false;
			break;
		}
		return $correcto;
	}

	/**
	 * Comprueba que una fecha tenga el formato pedido y que sea un día real del calendario.
	 *
	 * Primero usa verificar_formato_fecha(). Si el patrón coincide, separa año, mes
	 * y día y los valida con checkdate(), que rechaza fechas como 2026-02-31.
	 *
	 * @param string $fecha   Fecha en texto. Por defecto se espera el formato Y-m-d.
	 * @param string $formato "Y-m-d" o "d/m/Y". El valor por defecto es "Y-m-d".
	 * @return bool true si el formato y la fecha de calendario son válidos; false si falla cualquiera de las dos comprobaciones.
	 */
	public static function verificar_fecha($fecha, $formato = 'Y-m-d'){
		$resultado = false;
		$formato_correcto = self::verificar_formato_fecha($fecha, $formato);
		if($formato_correcto){
			switch($formato){
				case "Y-m-d":
					$seccion = explode("-",$fecha);
					$resultado = checkdate ( $seccion[1], $seccion[2], $seccion[0] );
				break;
				case "d/m/Y":
					$seccion = explode("-",$fecha);
					$resultado = checkdate ( $seccion[1], $seccion[0], $seccion[2] );
				break;
				default:
					$resultado = false;
				break;
			}
			return $resultado;
		}else{
			return false;
		}
	}

	/**
	 * Comprueba que una dirección de correo tenga un formato aceptado.
	 *
	 * Pasa primero por filter_var(FILTER_VALIDATE_EMAIL) y después por una expresión
	 * que exige minúsculas, un dominio y una extensión de 2 o 3 letras.
	 * Ejemplo válido: usuario@dominio.com.sv
	 *
	 * @param string $email Dirección de correo a evaluar.
	 * @return bool true si cumple ambas comprobaciones; false si falta, trae mayúsculas o no tiene forma de correo.
	 */
	public static function validar_email($email){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			//formato ejemplo: usuario@dominio.com.sv
			if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email) === 1 ){  
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Comprueba que un teléfono tenga ocho dígitos, opcionalmente separados en dos grupos de cuatro.
	 *
	 * Acepta un separador que no sea dígito entre los grupos, o ningún separador.
	 * Ejemplos válidos: 22223333, 2222-3333, 2222 3333.
	 *
	 * @param string $telefono Número de teléfono a evaluar.
	 * @return bool true si coincide con el patrón; false en caso contrario.
	 */
	public static function validar_telefono($telefono){
		if(preg_match('/^[\D]?(\d{4})[\D]?(\d{4})$/', $telefono) === 1){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * Lee un archivo .env y publica sus pares clave=valor en el entorno del proceso.
	 *
	 * Ignora líneas vacías y líneas que empiezan con #. En valores sin comillas,
	 * recorta un comentario final precedido de espacio, como "300 # cinco minutos".
	 * Si el valor va entre comillas simples o dobles, conserva el texto interior.
	 * No sobrescribe una clave que ya exista en $_ENV.
	 *
	 * @param string $ruta Ruta absoluta o relativa del archivo .env.
	 * @return void No devuelve datos. El resultado queda en $_ENV y en putenv().
	 * @throws RuntimeException Si el archivo no existe en la ruta indicada.
	 */
	public function cargarEnv(string $ruta): void {
		if (!file_exists($ruta)) {
			throw new RuntimeException("No se encontró el archivo .env en: $ruta");
		}

		$lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lineas as $linea) {
			// Ignorar comentarios
			$linea = trim($linea);
			if ($linea === '' || str_starts_with($linea, '#')) {
				continue;
			}

			// Separar en clave=valor (solo la primera '=')
			if (!str_contains($linea, '=')) {
				continue;
			}

			[$clave, $valor] = explode('=', $linea, 2);
			$clave = trim($clave);
			$valor = trim($valor);

			if ($valor !== '' && $valor[0] !== '"' && $valor[0] !== "'") {
				$valor = trim(preg_replace('/\s+#.*$/', '', $valor));
			}

			// Quitar comillas simples o dobles
			if (preg_match('/^"(.*)"$/', $valor, $m) || preg_match("/^'(.*)'$/", $valor, $m)) {
				$valor = $m[1];
			}

			// Cargar en variables de entorno
			if (!array_key_exists($clave, $_ENV)) {
				$_ENV[$clave] = $valor;
				putenv("$clave=$valor");
			}
		}
	}


	/**
	 * Codifica datos en Base64 apto para usarse dentro de una URL o de un JWT.
	 *
	 * Sustituye "+" por "-" y "/" por "_", y elimina el relleno "=" del final.
	 *
	 * @param string $data Texto o bytes que se van a codificar. En el JWT se usa con el encabezado, el payload y la firma.
	 * @return string Cadena Base64 URL-safe, sin el carácter "=".
	 */
	public function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	
	/**
	 * Decodifica una cadena Base64 URL-safe y recupera los bytes originales.
	 *
	 * Revierte los reemplazos de base64url_encode() y vuelve a agregar el relleno
	 * "=" cuando la longitud no es múltiplo de 4, porque base64_decode() lo necesita.
	 *
	 * @param string $data Cadena producida por base64url_encode(), normalmente una parte del JWT.
	 * @return string|false Bytes decodificados, o false si el contenido no es Base64 válido.
	 */
	public function base64url_decode($data) {
		$data = strtr($data, '-_', '+/');
		$relleno = strlen($data) % 4;
		if ($relleno) {
			$data .= str_repeat('=', 4 - $relleno);
		}
		return base64_decode($data);
	}

	/**
	 * Genera un JSON Web Token firmado con HMAC-SHA256.
	 *
	 * El token tiene tres partes separadas por punto: encabezado, payload y firma.
	 * El payload incluye iat (emitido en), exp (expira en), sub (id del usuario)
	 * y name (nombre de usuario). La firma se calcula con $passkey; quien no tenga
	 * esa clave no puede fabricar un token que validarJWToken() acepte.
	 *
	 * También envía el encabezado HTTP Content-Type: application/json.
	 *
	 * @param string     $passkey       Clave secreta de firma. En este proyecto proviene de JWT_PASS_KEY en el .env.
	 * @param int|string $idusuario     Identificador del usuario. Se guarda en la reclamación "sub".
	 * @param string     $usuario       Nombre del usuario. Se guarda en la reclamación "name".
	 * @param int|string $tiempovalidez Segundos de vigencia a partir del momento actual. Se suma a time() para obtener "exp".
	 * @return string Token JWT listo para entregar al cliente, con la forma encabezado.payload.firma.
	 */
	public function generarJWToken($passkey,$idusuario,$usuario,$tiempovalidez){		
		$secret_key = $passkey;

		$header = json_encode([
			'typ' => 'JWT',
			'alg' => 'HS256'
		]);

		$tiempo_actual = time();
		$payload = json_encode([
			'iat' => $tiempo_actual,
			'exp' => $tiempo_actual + $tiempovalidez,
			'sub' => $idusuario,
			'name' => $usuario,        
			//'role' => 'user'                
		]);
		
		$header_encoded = $this->base64url_encode($header);
		$payload_encoded = $this->base64url_encode($payload);
		
		$signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret_key, true);
		$signature_encoded = $this->base64url_encode($signature);
		
		$jwt = $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
		
		header('Content-Type: application/json');
		//return json_encode(['token' => $jwt]);
		return $jwt;
	}

	/**
	 * Verifica la firma y la vigencia de un JWT creado por generarJWToken().
	 *
	 * Recalcula la firma HMAC-SHA256 con la misma clave y la compara con la firma
	 * recibida. Después decodifica el payload y rechaza el token si no trae "exp"
	 * o si la hora actual ya alcanzó esa expiración. No consulta la base de datos:
	 * la existencia del token en la tabla login se comprueba en Login::validarToken().
	 *
	 * @param string $passkey Misma clave secreta usada al generar el token (JWT_PASS_KEY).
	 * @param string $jwt     Token de tres segmentos. Si el cliente envía "id|jwt", hay que pasar solo la parte posterior al "|".
	 * @return array{status: bool, mensaje: string, valores?: array} status true y el payload en "valores" cuando la firma es correcta y el token no expiró. status false y un mensaje cuando el token está incompleto, la firma no coincide o ya venció.
	 */
	public function validarJWToken($passkey, $jwt){
		$partes = explode('.', $jwt);
		if (count($partes) !== 3 || $passkey === false || $passkey === '') {
			return array("status"=>false,"mensaje"=>"Token inválido");
		}

		[$header_encoded, $payload_encoded, $signature_encoded] = $partes;
		$firma_esperada = $this->base64url_encode(
			hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $passkey, true)
		);

		if (strlen($firma_esperada) !== strlen($signature_encoded) || !hash_equals($firma_esperada, $signature_encoded)) {
			return array("status"=>false,"mensaje"=>"Token inválido");
		}

		$payload = json_decode($this->base64url_decode($payload_encoded), true);
		if (!is_array($payload) || !isset($payload['exp'])) {
			return array("status"=>false,"mensaje"=>"Token inválido");
		}

		if (time() >= intval($payload['exp'])) {
			return array("status"=>false,"mensaje"=>"El token ha expirado");
		}

		return array("status"=>true,"mensaje"=>"Token válido","valores"=>$payload);
	}



		
	/*
	* ip_in_range.php - Function to determine if an IP is located in a
	*                   specific range as specified via several alternative
	*                   formats.
	*
	* Network ranges can be specified as:
	* 1. Wildcard format:     1.2.3.*
	* 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
	* 3. Start-End IP format: 1.2.3.0-1.2.3.255
	*
	* Return value BOOLEAN : ip_in_range($ip, $range);
	*
	* Copyright 2008: Paul Gregg <pgregg@pgregg.com>
	* 10 January 2008
	* Version: 1.2
	*
	* Source website: http://www.pgregg.com/projects/php/ip_in_range/
	* Version 1.2
	*
	* This software is Donationware - if you feel you have benefited from
	* the use of this tool then please consider a donation. The value of
	* which is entirely left up to your discretion.
	* http://www.pgregg.com/donate/
	*
	* Please do not remove this header, or source attibution from this file.
	*/
	
	/**
	 * Indica si una dirección IPv4 pertenece a un rango.
	 *
	 * Adaptación del código de Paul Gregg (ip_in_range.php, 2008). El aviso de
	 * autoría original se conserva arriba. El rango admite tres formas:
	 * 1. Comodín:     1.2.3.*
	 * 2. CIDR:        1.2.3.0/24 o 1.2.3.4/255.255.255.0
	 * 3. Inicio-fin:  1.2.3.0-1.2.3.255
	 *
	 * Hace poca validación del texto del rango: espera uno de esos tres formatos.
	 * Si el formato no se reconoce, imprime un mensaje y devuelve false.
	 *
	 * @param string $ip    Dirección IPv4 a comprobar, por ejemplo "192.168.1.10".
	 * @param string $range Rango en formato comodín, CIDR o inicio-fin.
	 * @return bool true si la IP cae dentro del rango; false si queda fuera o el rango no tiene un formato reconocido.
	 */
	public function ip_in_range($ip, $range) {
		if (strpos($range, '/') !== false) {
		// $range is in IP/NETMASK format
		list($range, $netmask) = explode('/', $range, 2);
		if (strpos($netmask, '.') !== false) {
			// $netmask is a 255.255.0.0 format
			$netmask = str_replace('*', '0', $netmask);
			$netmask_dec = ip2long($netmask);
			return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
		} else {
			// $netmask is a CIDR size block
			// fix the range argument
			$x = explode('.', $range);
			while(count($x)<4) $x[] = '0';
			list($a,$b,$c,$d) = $x;
			$range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
			$range_dec = ip2long($range);
			$ip_dec = ip2long($ip);
	
			# Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
			#$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));
	
			# Strategy 2 - Use math to create it
			$wildcard_dec = pow(2, (32-$netmask)) - 1;
			$netmask_dec = ~ $wildcard_dec;
	
			return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
		}
		} else {
		// range might be 255.255.*.* or 1.2.3.0-1.2.3.255
		if (strpos($range, '*') !==false) { // a.b.*.* format
			// Just convert to A-B format by setting * to 0 for A and 255 for B
			$lower = str_replace('*', '0', $range);
			$upper = str_replace('*', '255', $range);
			$range = "$lower-$upper";
		}
	
		if (strpos($range, '-')!==false) { // A-B format
			list($lower, $upper) = explode('-', $range, 2);
			$lower_dec = (float)sprintf("%u",ip2long($lower));
			$upper_dec = (float)sprintf("%u",ip2long($upper));
			$ip_dec = (float)sprintf("%u",ip2long($ip));
			return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
		}
	
		echo 'Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format';
		return false;
		}
	
	}
	
	/**
	 * Indica si una dirección IPv4 pertenece al menos a uno de varios rangos.
	 *
	 * Recorre $ranges_array y delega cada elemento en ip_in_range(). Se detiene
	 * en el primer rango que contenga la IP. Se usa, por ejemplo, con la lista
	 * de la lista blanca antes de procesar una petición.
	 *
	 * @param string $ip            Dirección IPv4 a comprobar.
	 * @param array  $ranges_array  Lista de rangos. Cada elemento usa el mismo formato que el parámetro $range de ip_in_range().
	 * @return bool true si la IP coincide con algún rango; false si el arreglo está vacío, no es un arreglo o ningún rango la contiene.
	 */
	public function ip_in_ranges($ip, $ranges_array) {
		// Ensure $ranges_array is actually an array and not empty
		if (!is_array($ranges_array) || empty($ranges_array)) {
			// Optionally log an error or warning here
			// echo "Error: The ranges argument must be a non-empty array.";
			return false;
		}
	
		foreach ($ranges_array as $range) {
			// Use the existing ip_in_range function for each individual range
			if ($this->ip_in_range($ip, $range)) {
				return true; // Found the IP in this range, no need to check further
			}
		}
	
		// If the loop finishes without finding the IP in any range
		return false;
	}
  

}

?>