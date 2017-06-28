	function procesarPost($conexion, $ruta, $bodyParams){

		/*
			Se supone que para acceder a esta función al menos hemos tenido que comprobar
			que el recurso es válido antes. Por lo tantos partimos de este supuesto.
		*/

		$recurso = strtoupper($ruta[0]);

		if (count($ruta) == 1) {
			
			//Verificamos ahora que se adjunte un recurso en formato JSON
			if ($bodyParams != null && strlen($bodyParams) > 0 && isValidJSON($bodyParams)) {

				$json = json_decode($bodyParams, true);

				//Verificamos que existe el token y que es correcto
				//Si no lo fuera, el propio servidor se encargaría de cancelar el procesamiento 
				if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    				$server->getResponse()->send();
    				die;
				}

				$checker = ($recurso == 'DISPOSITIVO') ? array(0 => 'referencia',1 => 'marca',2 => 'nombre',3 => 'color',4 => 'capacidad',5 => 'f_oid') : array(0 => 'f_oid',1 => 'nombre',2 => 'direccion',3 => 'tlf',4 => 'pais',5 => 'password') ;

				//Comprobamos que el recurso tenga todos los campos necesarios
				foreach ($json as $key => $value) {

					if(!in_array(strtolower($key), $checker)){
						replyToClient(array('Not a Valid Resource'=>'The provided resource does not contain all the required fields'),400,array(), 'json');
						break;
					}

				}

				//Validamos el contenido de los campos
				$erroresRecurso = validarRecurso($recurso, $json);

				if (count($erroresRecurso) == 0) {
					//Si no hay errores, procedemos a su inserción

					return creaRecurso($conexion, $recurso, $json);

				}else{
					replyToClient(array('Errors where found in the resource'=>'The provided resource does not fulfill the resource requirements'),400,array(), 'json');
				}

			} else {
				replyToClient(array('Malformed or Inexistent JSON'=>'The JSON provided in the Body is malformed or does not exist'),400,array(), 'json');
			}
			

		} else {

			//En nuestra API no existe esta ruta

			replyToClient(array(),400,array(), 'html');
		}
		
	}