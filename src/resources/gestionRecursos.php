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

      		return array(0 => $total_consulta, 1 => $stmt->fetchAll());

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function consultaRecursos($conexion, $recurso){

		$consulta = "SELECT * FROM " . $recurso;

		try {

			$resultado = $conexion->query($consulta);

			return $resultado;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
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
			return $stmt->fetch();

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function creaRecurso($conexion, $recurso, $objeto){

		$consulta = "INSERT INTO ".$recurso." VALUES ( ";

		foreach ($objeto as $key => $value) {
			$consulta = $consulta.":".$key.", ";
		}

		$consulta .= ')';

		try {

			$stmt = $conexion->prepare($consulta);

			foreach ($objeto as $key => $value) {
				$stmt->bindParam(':'.$key, $value);
			}

			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function actualizaRecurso($conexion, $recurso, $objeto, $clave){

		if($recurso == 'DISPOSITIVOS'){
			$cell = 'REFERENCIA';
		}else{
			$cell = 'F_OID';
		}

		$consulta = "UPDATE ".$recurso." SET ";

		foreach ($objeto as $key => $value) {
			$consulta = $consulta.strtoupper($key).' = '.':'.$key.', ';
		}

		$consulta .= 'WHERE '. $cell . ' = :clave';
		
		try {

			$stmt = $conexion->prepare($consulta);

			foreach ($objeto as $key => $value) {
				$stmt->bindParam(':'.$key, $value);
			}

			$stmt->bindParam(':clave', $clave);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
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
			header('Location: excepcion.php');
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

?>