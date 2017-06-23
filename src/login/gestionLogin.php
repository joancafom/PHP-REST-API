<?php
	
	function consultaLogin($conexion, $nombreFabricante, $password){

		$consulta = "SELECT COUNT(*) AS TOTAL FROM FABRICANTES WHERE NOMBRE = :nombre AND PASSWORD = :password";

		$stmt = $conexion->prepare($consulta);
		$stmt->bindParam(':nombre', $nombreFabricante);
		$stmt->bindParam(':password', $password);
		$stmt->execute();

		$resultado = $stmt->fetch();

		if($resultado['TOTAL'] == 1){
			return true;
		}else{
			return false;
		}

	}

?>