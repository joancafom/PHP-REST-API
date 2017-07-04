<?php

	require_once "../BD/gestionBD.php";
	require_once "../BD/utilidades.php";
	require_once "../resources/gestionRecursos.php";
	require_once "../OAuth2Common/server.php";

	$recursos = array('0' => 'dispositivos', '1' => 'fabricantes');

	//Obtenemos la ruta a la que se está intentando acceder por medio del parámetro req_path (si está definido)
	$req_path = (isset($_GET['req_path'])) ? $_GET['req_path'] : null;

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

        	if ($res != null && $res[1] != null) {

        		replyToClient($res, 200, array(), 'json');

        	}else{

        		//Si no hay nada que mostrar, es porque no hemos encontrado nada en la BD.
        		replyToClient(array('No results found' => 'The server was not able to find any results'), 404, array(), 'json');
        	}
        	
       	 	break;

    	case 'post':

        	$res = procesarPost($conexion, $ruta, file_get_contents("php://input"), $server);

        	if($res === True){

        		replyToClient(array(), 201, array(), 'html');

        	}else{
        		//Revisar
        		replyToClient(array(), 500, array(), 'html');

        	}

        	break;

    	case 'put':

        	$res = procesarPut($conexion, $ruta, file_get_contents("php://input"), $server);

        	if($res === True){

        		replyToClient(array(), 204, array(), 'html');

        	}else{
        		//Revisar
        		replyToClient(array(), 500, array(), 'html');

        	}

        	break;

    	case 'delete':

        	$res = procesarDelete($conexion, $ruta, $server);
        	
        	if ($res === True) {

        		replyToClient(array(), 204, array(), 'html');

        	} else {
        		//Revisar
        		replyToClient(array(), 500, array(), 'html');

        	}

        	break;

    	default:
    	
    		replyToClient(array(), 405, array(), 'html');

	}

	cerrarConexionBD($conexion);


	function procesarGet($conexion, $ruta, $parametros){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);
		$campos = ($recurso == 'DISPOSITIVOS') ? '*' : 'NOMBRE, F_OID, DIRECCION, PAIS, TLF' ;
		
		if (count($ruta) == 1) {

			//Como sólo hay un parámetro, es un GET simple en el que devolvemos todos los resultados

			//Comprobamos que los parámetros son válidos. Si no lo son (o no existen) devolvemos una
			//representación con los parámetros por defecto (limit = 10 & offset = 1)

			$resultado = null;

			if(isset($parametros['limit']) || isset($parametros['offset'])){

				$limit = isset($parametros['limit']) ? $parametros['limit'] : 10;
				$offset = isset($parametros['offset']) ? $parametros['offset'] : 1;

				$parametrosValidados = validarLimitOffset($limit, $offset);

				$resultado = consultaRecursosPaginado($conexion, $recurso, $parametrosValidados['offset'], $parametrosValidados['limit'], $campos);

			}else{

				$resultado = consultaRecursosPaginado($conexion, $recurso, 1, 10, $campos);
			}

			return $resultado;

		} else if(count($ruta) == 2 && $ruta[1] != ''){
			
			//Al existir más de un parámetro en la ruta, es un GET hacia un recurso específico (El segundo elemento en la ruta es el identificador).
			//Si existieran más de dos elementos en la ruta, devolveríamos que la ruta no es válida

			//En este caso, los parámetros no son necesarios, así que los obviamos.

			//El identificador es sólo una string por la cual filtramos, por lo que no es necesaria su validación.

			$identificador = $ruta[1];

			$resultado = null;

			$resultado = consultaRecurso($conexion, $recurso, $identificador, $campos);

			return $resultado;


		}else{

			//En nuestra API, no existe esta ruta

			replyToClient(array(),404,array(), 'html');
		}

	}

	function procesarPost($conexion, $ruta, $bodyParams, $server){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		//Nuestra API sólo dispone de método POST para dispositivos, por lo que lo verificamos

		if ($recurso == 'DISPOSITIVOS' && count($ruta) == 1) {
			
			//Verificamos en primer lugar que el formato de entrada es JSON mediante el atributo Content-Type del header
			checkContentType('application/json');

			//Verificamos ahora que se adjunte un recurso en formato JSON correcto
			if ($bodyParams != null && strlen($bodyParams) > 0 && isValidJSON($bodyParams)) {

				$json = json_decode($bodyParams, true);

				//Verificamos que existe el token y que es correcto
				//Si no lo fuera, cancelamos el procesamiento y se envía el mensaje al cliente informándole de ello 
				if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    				$server->getResponse()->send();
    				die;
				}

				//Obtenemos el usuario correspondiente al token
				$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

				$checker = array(0 => 'referencia',1 => 'marca',2 => 'nombre',3 => 'color',4 => 'capacidad');

				//Cambiamos todos los índices a minúscula para proceder a la comprobación
				$json = array_change_key_case($json);

				//Comprobamos que el recurso tenga todos los campos necesarios

				if(count($checker) != count($json)){
					replyToClient(array('Not a Valid Device'=>'The provided device does not have the 5 required fields'),400,array(), 'json');
				}

				foreach ($checker as $value) {

					if(!array_key_exists($value, $json)){
						replyToClient(array('Not a Valid Device'=>'The provided device does not fulfill the schema. \''.$value.'\' was not found'),400,array(), 'json');
						break;
					}

				}

				//Validamos el contenido de los campos
				
				$erroresRecurso = validarRecurso($conexion, $recurso, $json, 'POST');

				if (count($erroresRecurso) == 0) {
					//Si no hay errores, procedemos a su inserción

					return creaDispositivo($conexion, $json, $token['USER_ID']);

				}else{

					replyToClient($erroresRecurso, 400, array(), 'json');
				}

			} else {

				replyToClient(array('Malformed or Inexistent JSON'=>'The JSON provided in the Body is malformed or does not exist'), 400, array(), 'json');

			}
			

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(), 404, array(), 'html');
		}
		
	}

	function procesarPut($conexion, $ruta, $bodyParams, $server){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if ( ($recurso == 'DISPOSITIVOS' && count($ruta) == 2) || ($recurso == 'FABRICANTES' && count($ruta) == 1) ) {
			
			//Verificamos en primer lugar que el formato de entrada es JSON mediante el atributo Content-Type del header
			checkContentType('application/json');

			//Verificamos ahora que se adjunte un recurso en formato JSON correcto
			if ($bodyParams != null && strlen($bodyParams) > 0 && isValidJSON($bodyParams)) {

				$json = json_decode($bodyParams, true);

				//Verificamos que existe el token y que es correcto
				//Si no lo fuera, cancelamos el procesamiento y se envía el mensaje al cliente informándole de ello 
				if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    				$server->getResponse()->send();
    				die;
				}

				//Obtenemos el usuario correspondiente al token
				$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

				$checker = $recurso == 'DISPOSITIVOS' ? array(0 => 'marca', 1 => 'nombre', 2 => 'color', 3 => 'capacidad', 4 => 'referencia') : array(0 => 'nombre', 1 => 'direccion', 2 => 'tlf', 3 => 'pais') ;

				//Cambiamos todos los índices a minúscula para proceder a la comprobación
				$json = array_change_key_case($json);

				//Comprobamos que el recurso tenga todos los campos necesarios

				if(count($checker) != count($json)){
					replyToClient(array('Not a Valid Resource'=>'The provided resource does not have the '.count($checker).' required fields'),400,array(), 'json');
				}

				foreach ($checker as $value) {

					if(!array_key_exists($value, $json)){
						replyToClient(array('Not a Valid Resource'=>'The provided resource does not fulfill the schema. \''.$value.'\' was not found'),400,array(), 'json');
						break;
					}

				}

				//Si estamos ante un dispositivo y no deseamos actualizar su referencia, la eliminamos de los campos a actualizar.
				if($recurso == 'DISPOSITIVOS' && $ruta[1] == $json['referencia']){
					unset($json['referencia']);
				}

				//Seleccionamos cuál va a ser la PK por la cual actualizar
				$identificador = ($recurso == 'DISPOSITIVOS') ? $ruta[1] : $token['USER_ID'];
				
				//En caso de querer actualizar un dispositivo, comprobamos que estamos autorizados para ello
				//No necesitamos verificarlo para los Fabricantes, ya que modificaremos los datos de aquél del token

	 			if($recurso == 'DISPOSITIVOS' && !verifyPrivileges($conexion, $token['USER_ID'], $recurso, $identificador)){
	 				replyToClient(array('Authoritation Error' => 'You have no privileges to access to this resource'), 403, array(), 'json');
	 			}

				//Validamos el contenido de los campos
				$erroresRecurso = validarRecurso($conexion, $recurso, $json, $identificador);

				if (count($erroresRecurso) == 0) {
	 				
					return actualizaRecurso($conexion, $recurso, $json, $identificador);

				}else{

					replyToClient($erroresRecurso, 400, array(), 'json');
				}

			} else {
				replyToClient(array('Malformed or Inexistent JSON'=>'The JSON provided in the Body is malformed or does not exist'), 400, array(), 'json');
			}
			

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(), 404, array(), 'html');
		}

	}

	//Devuelve true o false dependiendo del resultado de la operación
	function procesarDelete($conexion, $ruta, $server){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if ($recurso == 'DISPOSITIVOS' && count($ruta) == 2) {
			
			//Verificamos que existe el token y que es correcto
			//Si no lo fuera, cancelamos el procesamiento y se envía el mensaje al cliente informándole de ello 
			if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    			$server->getResponse()->send();
    			die;
			}

			//Obtenemos el usuario correspondiente al token
			$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

	 		//Verificamos que tiene privilegios para realizar la operacion
	 		if(!verifyPrivileges($conexion, $token['USER_ID'], $recurso, $ruta[1])){
	 			replyToClient(array('Authoritation Error' => 'You have no privileges to access to this resource'), 403, array(), 'json');
	 		}

	 		//Realizamos la operación
			$resultado = eliminaRecurso($conexion, $recurso, $ruta[1]);

			return $resultado;

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(),404,array(), 'html');
		}
	
	}

?>
