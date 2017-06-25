<?php

	$recursos = array('0' => 'dispositivos', '1' => 'fabricantes');

	//Obtenemos la ruta a la que se está intentando acceder por medio del parámetro req_path
	$req_path = $_GET['req_path'];

	if ($req_path == null) {
		header('Location: https://es.wikipedia.org/wiki/HTTP_403');
		die();
	}

	$ruta = explode('/', $req_path);

	//Ahora tenemos la ruta en un array asociativo

	$recursoAccedido = $ruta[0];

	//Comprobamos que estamos accediendo a uno de los recursos disponibles en nuestra API

	if(!in_array($recursoAccedido, $recursos)){
		header('Location: https://es.wikipedia.org/wiki/HTTP_401');
		die();
	}

	//Obtenemos el método de la petición
	$metodo = strtolower($_SERVER['REQUEST_METHOD']);

	//Procesamos ahora la petición dependiendo del método

	switch ($metodo) {
    	case 'get':
        	procesarGet($conexion, $ruta, $_GET);
        	echo "done";
       	 	break;

    	case 'post':
        	//procesarPost($ruta);
        	break;
    	case 'put':
        	//procesarPut($ruta);
        	break;

    	case 'delete':
        	//procesarDelete($ruta);
        	break;
    	default:
        	echo "Not implemented yet";
	}

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

				return $resultado[1];

			}else{

				$resultado = consultaRecursosPaginado($conexion, $recurso, 1, 10);

				return $resultado[1];

			}

		} else if(count($ruta) == 2){
			
			//Al existir más de un parámetro en la ruta, es un GET hacia un recurso específico (El segundo elemento en la ruta es el identificador).
			//Si existieran más de dos elementos en la ruta, devolveríamos que la ruta no es válida

			//En este caso, los parámetros no son necesarios, así que los obviamos.

			//El identificador es sólo una string por la cual filtramos, por lo que no es necesaria su validación.

			$identificador = $ruta[1];
			$resultado = null;

			$resultado = consultaRecurso($conexion, $recurso, $identificador);

			return $resultado;


		}else{

			//En nuestra API no existe esta ruta

			header('Location: novalid.php');
			die();
		}
		


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
