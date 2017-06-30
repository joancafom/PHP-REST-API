<?php

	require_once '../BD/utilidades.php';

	function consultaRecursosPaginado($conexion, $recurso, $offset, $limit){

		//No necesitamos hacer un bindParam() ya que sólo se llegará aquí tras comprobar
		//que el recurso es uno de los disponibles

		$consulta = "SELECT * FROM ". $recurso;

		try {

			$stmt = stmtPaginado($conexion, $consulta, $offset, $limit);
			$stmt->execute();

			$total_consulta = total_consulta($conexion,$consulta,null,null);

      		return array(0 => array('Resultados: ' => $total_consulta), 1 => $stmt->fetchAll(PDO::FETCH_ASSOC));

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 

			return null;
		}
	}

	function consultaRecursos($conexion, $recurso){

		$consulta = "SELECT * FROM " . $recurso;

		try {

			$resultado = $conexion->query($consulta);

			return $resultado;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return null;
		}
	}

	function consultaRecurso($conexion, $recurso, $key){

		//Determinamos a qué recurso estamos accediendo, y elegimos la tabla
		//que referencia a la $key

		if($recurso == 'DISPOSITIVOS'){
			$cell = 'REFERENCIA';
		}else{
			$cell = 'F_OID';
		}

		$consulta = "SELECT * FROM ".$recurso." WHERE ".$cell." = :key";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':key', $key);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return $stmt->fetch(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return null;
		}
	}

	function consultaFOID($conexion, $nombre){

		$consulta = "SELECT F_OID FROM FABRICANTES WHERE NOMBRE = :nombre";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':nombre', $nombre);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return $stmt->fetch(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return null;
		}
	}

	function creaDispositivo($conexion, $dispositivo, $user_id){

		$consulta = "INSERT INTO DISPOSITIVOS (MARCA, NOMBRE, COLOR, CAPACIDAD, F_OID, REFERENCIA) VALUES (:marca, :nombre, :color, :capacidad, (SELECT F_OID FROM FABRICANTES WHERE NOMBRE = :user_id), :referencia)";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':marca', $dispositivo['marca']);
			$stmt->bindParam(':nombre', $dispositivo['nombre']);
			$stmt->bindParam(':color', $dispositivo['color']);
			$stmt->bindParam(':capacidad', $dispositivo['capacidad']);
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':referencia', $dispositivo['referencia']);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return false;
		}
	}

	function creaRecurso($conexion, $recurso, $objeto){

		$consulta = "INSERT INTO ".$recurso." ";

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

			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 

			return false;
		}
	}

	function actualizaRecurso($conexion, $recurso, $objeto, $clave){

		if($recurso == 'DISPOSITIVOS'){
			$cell = 'REFERENCIA';
		}else{
			$cell = 'NOMBRE';
		}

		$consulta = "UPDATE ".$recurso." SET ";

		foreach ($objeto as $key => $value) {
			$consulta = $consulta.strtoupper($key).' = '.':'.$key.', ';
		}

		$consulta .= 'WHERE '. $cell . ' = :clave';
		
		try {

			$stmt = $conexion->prepare($consulta);

			foreach ($objeto as $key => $value) {
				$stmt->bindParam(':'.$key, $objeto[$key]);
			}

			$stmt->bindParam(':clave', $clave);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return false;
		}
	}

	function eliminaRecurso($conexion, $recurso, $key){

		if($recurso == 'DISPOSITIVOS'){
			$cell = 'REFERENCIA';
		}else{
			$cell = 'F_OID';
		}

		$consulta = "DELETE FROM ".$recurso." WHERE ".$cell." = :key";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':key', $key);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			
			return false;
		}
	}

	function verifyPrivileges($conexion, $user_id, $recurso, $identificador){
		//SELECT * FROM DISPOSITIVOS WHERE F_OID = (SELECT F_OID FROM FABRICANTES WHERE NOMBRE = 'Apple Inc.') AND REFERENCIA = '1000000000000';
		//SELECT * FROM FABRICANTES WHERE F_OID = 1 AND  NOMBRE = 'Apple Inc.';
		try {

			if ($recurso == 'DISPOSITIVOS') {
				$query = "SELECT * FROM DISPOSITIVOS WHERE F_OID = (SELECT F_OID FROM FABRICANTES WHERE NOMBRE = :user_id) AND REFERENCIA = :identificador";
			} else {
				$query = "SELECT * FROM FABRICANTES WHERE F_OID = :identificador AND  NOMBRE = :user_id";
			}

			$stmt = $conexion->prepare($query);
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':identificador', $identificador);
			$stmt->execute();

			$res = $stmt->fetch();

			if(!$res){
				return false;
			}else{
				return true;
			}
			
		} catch (PDOException $e) {
			return false;
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

			if(strlen($objeto['referencia']) != 13 || comprobarExistencia($conexion, $recurso, 'REFERENCIA' , $objeto['referencia'])){

				if(strlen($objeto['referencia']) != 13){
					$errores[] = 'La referencia del dispositivo debe tener 13 caracteres';
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
				$errores[] = 'El país del fabricante debe tener 3 letras mayúsculas';
			}

			if (!preg_match('/^((\+[0-9][0-9])|(00[0-9][0-9]))?[0-9]{3,15}$/', $objeto['tlf'])) {
				$errores[] = 'El teléfono del fabricante debe ser válido';
			}

			if(comprobarExistencia($conexion, $recurso, 'NOMBRE', $objeto['nombre'])){
				$errores[] = 'El nombre del fabricante no debe existir previamente';
			}

			if(comprobarExistencia($conexion, $recurso, 'TLF', $objeto['tlf'])){
				$errores[] = 'El tlf del fabricante no debe existir previamente';
			}
		}

		return $errores;
		
	}

	function comprobarExistencia($conexion, $recurso, $cell, $identificador){
		//SELECT * FROM DISPOSITIVOS WHERE F_OID = (SELECT F_OID FROM FABRICANTES WHERE NOMBRE = 'Apple Inc.') AND REFERENCIA = '1000000000000';
		//SELECT * FROM FABRICANTES WHERE F_OID = 1 AND  NOMBRE = 'Apple Inc.';
		try {

			$query = "SELECT * FROM ".$recurso." WHERE ".$cell." = :identificador";

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

?>