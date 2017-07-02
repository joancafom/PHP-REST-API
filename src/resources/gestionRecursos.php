<?php

	require_once '../BD/utilidades.php';

	function consultaRecursosPaginado($conexion, $recurso, $offset, $limit, $columns = '*'){

		//No necesitamos hacer un bindParam() ya que sólo se llegará aquí tras comprobar
		//que el recurso es uno de los disponibles

		$consulta = "SELECT ".$columns." FROM ". $recurso;

		try {

			$stmt = stmtPaginado($conexion, $consulta, $offset, $limit);
			$stmt->execute();

			$total_consulta = total_consulta($conexion,$consulta,null,null);

			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

			//Contamos cuantos recursos han sido devueltos
			$contador = ($res == null) ? 0 : count($res);
			
			//1-> El total de recursos en la BD
			//2-> Los recursos devueltos
			$info = array('Total Recursos' => intval($total_consulta), 'Resultados Consulta' => $contador);

      		return array(0 => $info, 1 => $res);

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos null
			return null;
		}
	}

	function consultaRecursos($conexion, $recurso){

		$consulta = "SELECT * FROM " . $recurso;

		try {

			$resultado = $conexion->query($consulta);

			return $resultado;

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos null
			return null;
		}
	}

	function consultaRecurso($conexion, $recurso, $identificador, $columns = '*'){

		//Escogemos el atributo por el que filtrar dependiendo del recurso
		$cell = ($recurso == 'DISPOSITIVOS') ? 'REFERENCIA' : 'F_OID' ;

		$consulta = "SELECT ".$columns." FROM ".$recurso." WHERE ".$cell." = :identificador";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':identificador', $identificador);
			$stmt->execute();

			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$total_consulta = total_consulta($conexion,"SELECT * FROM ". $recurso,null,null);

			//Contamos cuantos recursos han sido devueltos
			$contador = ($res == null) ? 0 : 1;
			
			//1-> El total de recursos en la BD
			//2-> Los recursos devueltos
			$info = array('Total Recursos' => intval($total_consulta), 'Resultados Consulta' => $contador);

      		return array(0 => $info, 1 => $res);

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos null
			return null;
		}
	}

	function consultaFOID($conexion, $nombre){

		$consulta = "SELECT F_OID FROM FABRICANTES WHERE NOMBRE = :nombre";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':nombre', $nombre);
			$stmt->execute();

			//Devolvemos sólo el único resultado, en forma de array asociativo
			return $stmt->fetch(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos null
			return null;
		}
	}

	function creaDispositivo($conexion, $dispositivo, $f_oid){

		$consulta = "INSERT INTO DISPOSITIVOS (MARCA, NOMBRE, COLOR, CAPACIDAD, F_OID, REFERENCIA) VALUES (:marca, :nombre, :color, :capacidad, :f_oid, :referencia)";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':marca', $dispositivo['marca']);
			$stmt->bindParam(':nombre', $dispositivo['nombre']);
			$stmt->bindParam(':color', $dispositivo['color']);
			$stmt->bindParam(':capacidad', $dispositivo['capacidad']);
			$stmt->bindParam(':f_oid', $f_oid);
			$stmt->bindParam(':referencia', $dispositivo['referencia']);
			$res = $stmt->execute();

			// Execute devuelve TRUE o FALSE en caso de error
			return $res;

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos FALSE
			return false;
		}
	}

	function creaRecurso($conexion, $recurso, $objeto){

		$consulta = "INSERT INTO ".$recurso." ";

		//El tamaño del objeto. Usado para construir la consulta
		$tam = count($objeto);
		
		$aux1 = '( ';
		$aux2 = '( ';
		
		foreach ($objeto as $key => $value) {
			$tam = $tam - 1;
			
			$aux1= $aux1.strtoupper($key);
			$aux2= $aux2.":".$key;

			if ($tam != 0) {
				$aux1 .= " , ";
				$aux2 .= " , ";
			}
		}

		$aux1 .= ' )';
		$aux2 .= ' )';
		
		$consulta .= $aux1." VALUES ".$aux2;
		
		try {

			$stmt = $conexion->prepare($consulta);

			foreach ($objeto as $key => $value) {
				$stmt->bindParam(':'.$key, $objeto[$key]);
			}

			$res = $stmt->execute();

			//Devolvemos el resultado de la operación
			return $res;

		} catch (PDOException $e) {
			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos FALSE
			return false;
		}
	}

	function actualizaRecurso($conexion, $recurso, $objeto, $identificador){

		//Escogemos el atributo por el que filtrar dependiendo del recurso
		$cell = ($recurso == 'DISPOSITIVOS') ? 'REFERENCIA' : 'F_OID' ;

		$consulta = "UPDATE ".$recurso." SET ";

		//Tamaño del array de los objetos. Usado para la construcción de la consulta
		$tam = count($objeto);

		foreach ($objeto as $key => $value) {

			$tam = $tam - 1;

			$consulta = $consulta.strtoupper($key).' = '.':'.$key;

			if ($tam != 0) {
				$consulta .= ", ";
			}

		}

		$consulta .= ' WHERE '. $cell . ' = :identificador';
		
		try {

			$stmt = $conexion->prepare($consulta);

			foreach ($objeto as $key => $value) {
				$stmt->bindParam(':'.$key, $objeto[$key]);
			}

			$stmt->bindParam(':identificador', $identificador);
			$res = $stmt->execute();

			//Devolvemos el resultado de la operación
			return $res;

		} catch (PDOException $e) {

			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos FALSE
			return false;
		}
	}

	function eliminaRecurso($conexion, $recurso, $key){

		//Escogemos el atributo por el que filtrar dependiendo del recurso
		$cell = ($recurso == 'DISPOSITIVOS') ? 'REFERENCIA' : 'F_OID' ;

		$consulta = "DELETE FROM ".$recurso." WHERE ".$cell." = :key";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':key', $key);
			$res = $stmt->execute();

			//Devolvemos el resultado de la operación
			return $res;

		} catch (PDOException $e) {

			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos FALSE
			return false;
		}
	}

	function verifyPrivileges($conexion, $user_id, $recurso, $identificador){

		//Identificamos el recurso que estamos intentando acceder.
		if ($recurso == 'DISPOSITIVOS') {

			//Estamos intentanto acceder a DISPOSITIVOS

			$query = "SELECT * FROM DISPOSITIVOS WHERE F_OID = :user_id AND REFERENCIA = :identificador";

			try {
				
				$stmt = $conexion->prepare($query);
				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':identificador', $identificador);
				$stmt->execute();

				//Devolvemos el primer (y único) resultado
				$res = $stmt->fetch();

				//Si $res = null (no hay ninguna coincidencia) será porque no tiene privilegios
				if(!$res){
					return false;
				}else{
					return true;
				}

			} catch (PDOException $e) {

				//Para facilitar la depuración guardamos un atributo en sesión con el error
				$_SESSION['excepcion'] = $e->GetMessage(); 
				
				//Si algo saliera mal, por precaución devolveremos que no se tienen privilegios
				return false;
			}

		} else {
			
			//Estamos intentando acceder a FABRICANTES

			//Para ello comprobaremos que la F_OID a la que estamos intentando acceder es la misma que la del token

			return $user_id == $identificador;
		}

	}


	function validarRecurso($conexion, $recurso, $objeto, $opinfo){

		/*
			El argumento $opinfo puede contener dos valores y nos ayuda a identificar
			la operación que está siendo realizada. Si la operación es un POST, el valor contenido
			será 'POST'. En caso de ser un PUT, contendrá el valor del identificador del objeto, que
			será usado para las comprobaciones.
		*/

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

			if(isset($objeto['referencia']) && (strlen($objeto['referencia']) != 13 || !is_numeric($objeto['referencia']) || comprobarExistencia($conexion, $recurso, 'REFERENCIA' , $objeto['referencia']))){

				if(strlen($objeto['referencia']) != 13){
					$errores[] = 'La referencia del dispositivo debe tener 13 caracteres';
				}elseif (!is_numeric($objeto['referencia'])) {
					$errores[] = 'La referencia del dispositivo debe ser numérica';
				}else{
					$errores[] = 'La referencia del dispositivo no debe existir previamente';
				}
				
			}

		} else {

			if (strlen($objeto['nombre']) <= 0) {
				$errores[] = 'El nombre del fabricante no debe estar vacío';
			}

			if (strlen($objeto['direccion']) <= 0) {
				$errores[] = 'La dirección del fabricante no debe estar vacía';
			}

			if (!preg_match('/^[A-Z][A-Z][A-Z]$/', $objeto['pais'])) {
				$errores[] = 'El país del fabricante debe ser 3 letras mayúsculas';
			}

			if (!preg_match('/^((\+[0-9][0-9])|(00[0-9][0-9]))?[0-9]{3,15}$/', $objeto['tlf'])) {
				$errores[] = 'El teléfono del fabricante debe ser válido';
			}

			/*
				En el caso de un PUT en el que el valor no sea actualizado, cuando se comprueba la existencia
				del valor a actualizar el resultado será verdadero. Para evitar esto, debemos añadir a la sentencia
				SQL la PK del propio recurso ($opinfo) y añadir la condición de que sea otro recurso distinto. 
				En el caso de un POST, esta restricción no afecta pues el valor de $opinfo será 'POST', y la PK de un
				fabricante es numérica, por lo que queda garantizado que siempre será diferente y la restricción no afectará.
			*/
			if (comprobarExistencia($conexion, $recurso, 'NOMBRE', $objeto['nombre'], $opinfo)) {
					$errores[] = 'El nombre del fabricante no debe existir previamente';
			}

			if(comprobarExistencia($conexion, $recurso, 'TLF', $objeto['tlf'], $opinfo)){
				$errores[] = 'El tlf del fabricante no debe existir previamente';
			}
		}

		return $errores;
		
	}

	function comprobarExistencia($conexion, $recurso, $campo, $key, $identificador = ''){

		try {
			
			$query = "SELECT * FROM ".$recurso." WHERE ".$campo." = :key";

			if ($recurso == 'FABRICANTES') {
				$query .=  " AND F_OID <> :identificador";
			}

			$stmt = $conexion->prepare($query);
			$stmt->bindParam(':key', $key);

			if ($recurso == 'FABRICANTES') {
				$stmt->bindParam(':identificador', $identificador);
			}

			$stmt->execute();

			$res = $stmt->fetch();

			if(!$res){
				return false;
			}else{
				return true;
			}
			
		} catch (PDOException $e) {

			//Para facilitar la depuración guardamos un atributo en sesión con el error
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			//Si se produce una excepción, por precaución devolveremos TRUE
			return true;
		}

	}

?>