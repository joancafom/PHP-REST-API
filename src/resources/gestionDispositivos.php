<?php

	require_once '../BD/utilidades.php';

	function consultaDispositivosPaginado($conexion, $page_number, $page_size){

		$consulta = "SELECT * FROM DISPOSITIVOS";

		try {

			$stmt = stmtPaginado($conexion, $consulta, $page_number, $page_size);
			$stmt->execute();

			$total_consulta = total_consulta($conexion,$consulta,null,null);

      		return array(0 => $total_consulta, 1 => $stmt);

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function consultaDispositivos($conexion){

		$consulta = "SELECT * FROM DISPOSITIVOS";

		try {

			$resultado = $conexion->query($consulta);

			return $resultado;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function consultaDispositivo($conexion, $referencia){

		$consulta = "SELECT * FROM DISPOSITIVOS WHERE REFERENCIA = :referencia";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':referencia', $referencia);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return $stmt->fetch();

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function creaDispositivo($conexion, $dispositivo){

		$consulta = "INSERT INTO DISPOSITIVOS VALUES (:marca, :nombre, :color, :capacidad, :f_oid, :referencia)";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':marca', $dispositivo['marca']);
			$stmt->bindParam(':nombre', $dispositivo['nombre']);
			$stmt->bindParam(':color', $dispositivo['color']);
			$stmt->bindParam(':capacidad', $dispositivo['capacidad']);
			$stmt->bindParam(':f_oid', $dispositivo['f_oid']);
			$stmt->bindParam(':referencia', $dispositivo['referencia']);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function actualizaDispositivo($conexion, $dispositivo){

		$consulta = "UPDATE DISPOSITIVOS SET MARCA = :marca, NOMBRE = :nombre, COLOR = :color, CAPACIDAD = :capacidad WHERE REFERENCIA = :referencia";
		
		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':marca', $dispositivo['marca']);
			$stmt->bindParam(':nombre', $dispositivo['nombre']);
			$stmt->bindParam(':color', $dispositivo['color']);
			$stmt->bindParam(':capacidad', $dispositivo['capacidad']);
			$stmt->bindParam(':referencia', $dispositivo['referencia']);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

	function eliminaDispositivo($conexion, $referencia){

		$consulta = "DELETE FROM DISPOSITIVOS WHERE REFERENCIA = :referencia";

		try {

			$stmt = $conexion->prepare($consulta);
			$stmt->bindParam(':referencia', $referencia);
			$stmt->execute();

			//Devolvemos sólo el único resultado
			return true;

		} catch (PDOException $e) {
			$_SESSION['excepcion'] = $e->GetMessage(); 
			header('Location: excepcion.php');
		}
	}

?>