<?php

	require_once "../BD/gestionBD.php";
	require_once "../resources/gestionRecursos.php";
	require_once "../OAuth2Common/server.php";

	$recursos = array('0' => 'dispositivos', '1' => 'fabricantes');

	//Obtenemos la ruta a la que se está intentando acceder por medio del parámetro req_path
	$req_path = $_GET['req_path'];

	if ($req_path == null) {
		replyToClient(array(),400,array(), 'html');
	}

	$ruta = explode('/', $req_path);

	//Ahora tenemos la ruta en un array asociativo

	$recursoAccedido = $ruta[0];

	//Comprobamos que estamos accediendo a uno de los recursos disponibles en nuestra API

	if(!in_array($recursoAccedido, $recursos)){
		replyToClient(array(),404,array(), 'html');
	}

	//Obtenemos el método de la petición
	$metodo = strtolower($_SERVER['REQUEST_METHOD']);

	//Procesamos ahora la petición dependiendo del método

	$conexion = crearConexionBD();

	switch ($metodo) {
    	case 'get':

        	$res = procesarGet($conexion, $ruta, $_GET);

        	if ($res[0] == 'single' && $res[1] != null) {

        		replyToClient($res[1], 200, array(), 'json');

        	} else if($res[0] == 'collection' && $res[1] != null){

        		replyToClient($res[1], 200, array(), 'json');

        	}else{
        		replyToClient(array(), 404, array(), 'html');
        	}
        	
       	 	break;

    	case 'post':
        	$res = procesarPost($conexion, $ruta, file_get_contents("php://input"), $server);
        	break;
    	case 'put':
        	//procesarPut($ruta);
        	break;

    	case 'delete':

        	$res = procesarDelete($conexion, $ruta, $_GET, $server);
        	
        	if ($res) {

        		replyToClient(array(), 204, array(), 'html');

        	} else {

        		replyToClient(array(), 404, array(), 'html');

        	}

        	break;

    	default:
    		replyToClient(array(), 405, array(), 'html');
	}

	cerrarConexionBD($conexion);

	//Devuelve un array con dos elementos:
	// 1 =>Indica el tipo de recurso devuelto (único dispositivo o varios)
	// 2 => El resultado
	function procesarGet($conexion, $ruta, $parametros){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if (count($ruta) == 1) {

			//Como sólo hay un parámetro, es un GET simple en el que devolvemos todos los resultados

			//Comprobamos que los parámetros son válidos. Si no lo son (o no existen) devolvemos una
			//representación con los parámetros por defecto (limit = 10 & offset = 1)

			$resultado = null;

			if(isset($parametros['limit']) || isset($parametros['offset'])){

				$limit = isset($parametros['limit']) ? $parametros['limit'] : 10;
				$offset = isset($parametros['offset']) ? $parametros['offset'] : 1;

				$parametrosValidados = validarLimitOffset($limit, $offset);

				$resultado = consultaRecursosPaginado($conexion, $recurso, $parametrosValidados['offset'], $parametrosValidados['limit']);

			}else{

				$resultado = consultaRecursosPaginado($conexion, $recurso, 1, 10);
			}

			return array('0' => 'collection', '1' => $resultado);

		} else if(count($ruta) == 2){
			
			//Al existir más de un parámetro en la ruta, es un GET hacia un recurso específico (El segundo elemento en la ruta es el identificador).
			//Si existieran más de dos elementos en la ruta, devolveríamos que la ruta no es válida

			//En este caso, los parámetros no son necesarios, así que los obviamos.

			//El identificador es sólo una string por la cual filtramos, por lo que no es necesaria su validación.

			$identificador = $ruta[1];
			$resultado = null;

			$resultado = consultaRecurso($conexion, $recurso, $identificador);

			return array('0' => 'single', '1' => $resultado);


		}else{

			//En nuestra API, no existe esta ruta

			replyToClient(array(),400,array(), 'html');
		}

	}

	function procesarPost($conexion, $ruta, $bodyParams, $server){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if ($recurso == 'DISPOSITIVOS' && count($ruta) == 1) {
			
			//Verificamos ahora que se adjunte un recurso en formato JSON
			if ($bodyParams != null && strlen($bodyParams) > 0 && isValidJSON($bodyParams)) {

				$json = json_decode($bodyParams, true);

				//Verificamos que existe el token y que es correcto
				//Si no lo fuera, el propio servidor se encargaría de cancelar el procesamiento 
				if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    				$server->getResponse()->send();
    				die;
				}

				//Obtenemos el usuario correspondiente al token
				$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

				$checker = array(0 => 'referencia',1 => 'marca',2 => 'nombre',3 => 'color',4 => 'capacidad');

				//Comprobamos que el recurso tenga todos los campos necesarios
				foreach ($json as $key => $value) {

					if(!in_array(strtolower($key), $checker)){
						replyToClient(array('Not a Valid Device'=>'The provided device does not contain all the required fields'),400,array(), 'json');
						break;
					}

				}

				//Validamos el contenido de los campos
				$erroresRecurso = validarRecurso($conexion, $recurso, $json);

				if (count($erroresRecurso) == 0) {
					//Si no hay errores, procedemos a su inserción

					return creaDispositivo($conexion, $json, $token['USER_ID']);

				}else{

					replyToClient($erroresRecurso,400,array(), 'json');
				}

			} else {
				replyToClient(array('Malformed or Inexistent JSON'=>'The JSON provided in the Body is malformed or does not exist'),400,array(), 'json');
			}
			

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(),404,array(), 'html');
		}
		
	}

	//Devuelve true o false dependiendo del resultado de la operación
	function procesarDelete($conexion, $ruta, $parametros, $server){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if (count($ruta) == 2) {
			
			//Verificamos que existe el token y que es correcto
			//Si no lo fuera, el propio servidor se encargaría de cancelar el procesamiento 
			if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    			$server->getResponse()->send();
    			die;
			}

			//Obtenemos el usuario correspondiente al token
			$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	 		//echo "User ID associated with this token is {".$token['USER_ID']."}";

	 		//Verificamos que tiene privilegios para realizar la operacion
	 		if(!verifyPrivileges($conexion, $token['USER_ID'], $recurso, $ruta[1])){
	 			replyToClient(array('Authoritation Error' => 'You have no privileges to access to this resource'),403,array(), 'json');
	 		}

	 		//Realizamos la operación
			$resultado = eliminaRecurso($conexion, $recurso, $ruta[1]);

			return $resultado;

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(),400,array(), 'html');
		}
		

	}

	function isValidJSON($str) {
   		json_decode($str);
   		return json_last_error() == JSON_ERROR_NONE;
	}

	//Terminal Operation, sends a response to the client
	function replyToClient($parametros = array(), $codigo = 200, $header = array(), $format){
		$response = new OAuth2\Response($parametros,$codigo,$header);
	 	$response->send($format);
	 	die();
	}

	function comprobarExistencia($conexion, $recurso, $identificador){
		//SELECT * FROM DISPOSITIVOS WHERE F_OID = (SELECT F_OID FROM FABRICANTES WHERE NOMBRE = 'Apple Inc.') AND REFERENCIA = '1000000000000';
		//SELECT * FROM FABRICANTES WHERE F_OID = 1 AND  NOMBRE = 'Apple Inc.';
		try {

			if ($recurso == 'DISPOSITIVOS') {
				$query = "SELECT * FROM DISPOSITIVOS WHERE REFERENCIA = :identificador";
			} else {
				$query = "SELECT * FROM FABRICANTES WHERE F_OID = :identificador";
			}

			$stmt = $conexion->prepare($query);
			$stmt->bindParam(':identificador', $identificador);
			$stmt->execute();

			$res = $stmt->fetch();

			if(!$res){
				return false;
			}else{
				return true;
			}
			
		} catch (PDOException $e) {
			return true;
		}


	}

	function validarRecurso($conexion, $recurso, $objeto){

		$errores = array();

		if ($recurso == 'DISPOSITIVOS') {

			if (strlen($objeto['marca']) <= 0) {
				$errores[] = 'La marca del dispositivo no debe estar vacía';
			}

			if (strlen($objeto['nombre']) <= 0) {
				$errores[] = 'El nombre del dispositivo no debe estar vacío';
			}

			if (strlen($objeto['color']) <= 0) {
				$errores[] = 'El color del dispositivo no debe estar vacío';
			}

			if($objeto['capacidad'] <= 0){
				$errores[] = 'La capacidad del dispositivo debe ser mayor que 0';
			}

			if (!preg_match('/^[0-9]{1,8}$/', $objeto['f_oid'])) {
				$errores[] = 'El identificador del fabricante del dispositivo debe ser un número de 1 a 8 dígitos';
			}

			if(strlen($objeto['referencia']) != 13 || comprobarExistencia($conexion, $recurso, $objeto['referencia'])){
				$errores[] = 'La referencia del dispositivo debe ser única y no existente';
			}

		} else {

			if (strlen($objeto['nombre']) <= 0) {
				$errores[] = 'El nombre del fabricante no debe estar vacío';
			}

			if (strlen($objeto['direccion']) <= 0) {
				$errores[] = 'La dirección del fabricante no debe estar vacía';
			}

			if (!preg_match('/^[A-Z][A-Z][A-Z]$/', $objeto['pais'])) {
				$errores[] = 'El país del fabricante debe tener 3 letras mayúsculas';
			}

			if (!preg_match('/^((\+[0-9][0-9])|(00[0-9][0-9]))?[0-9]{3,15}$/', $objeto['tlf'])) {
				$errores[] = 'El teléfono del fabricante debe ser válido';
			}

			if (!preg_match('/^[0-9]{1,8}$/', $objeto['f_oid'])) {
				$errores[] = 'El identificador del fabricante debe ser un número de 1 a 8 dígitos';
			}

			if(comprobarExistencia($conexion, $recurso, $objeto['f_oid'])){
				$errores[] = 'El identificador del fabricante debe ser único y no existente';
			}
		}

		return $errores;
		
	}

	function validarLimitOffset($limit, $offset){

		$parametrosValidados = array('limit' => 10, 'offset' => 1);

		$offset = is_numeric($offset) ? intval($offset) : 1;
		$limit = is_numeric($limit) ? intval($limit) : 10;

		if($limit > 0){
			$parametrosValidados['limit'] = $limit;
		}

		if($offset > 0){
			$parametrosValidados['offset'] = $offset;
		}

		return $parametrosValidados;

	}



?>
