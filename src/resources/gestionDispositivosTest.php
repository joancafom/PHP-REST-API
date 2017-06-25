<?php

	require_once '../BD/gestionBD.php';
	require_once 'gestionDispositivos.php';

	$conexion = crearConexionBD();

	//1 - Consulta Todos

	$consulta = consultaDispositivos($conexion);

	foreach ($consulta as $dispositivo) {
		foreach ($dispositivo as $celda) {
			echo $celda;
		}
	}
	
	echo "<br/>";
	echo "<br/>";
	
	//2 - Consulta con PK

	$consulta = consultaDispositivo($conexion, '1000000000001');

	foreach ($consulta as $celda) {
		echo $celda;
	}
	
	echo "<br/>";
	echo "<br/>";
	
	//3 - Modificación

	$dispositivo = array('marca' =>  $consulta['MARCA'], 'nombre' => 'plof', 'color' => $consulta['COLOR'], 'capacidad' => $consulta['CAPACIDAD'], 
	'referencia' => $consulta['REFERENCIA']);
	
	$res = actualizaDispositivo($conexion, $dispositivo);
	
	echo $res;
	
	$consulta = consultaDispositivo($conexion, '1000000000001');

	foreach ($consulta as $celda) {
		echo $celda;
	}
	
	
	echo "<br/>";
	echo "<br/>";
	
	//4 - Creación
	
	$dispositivo = array('marca' =>  'sandwich', 'nombre' => 'de pollo', 'color' => 'marron', 'capacidad' => 2, 
	'referencia' => '1000000000003', 'f_oid' => 1);
	
	$res = creaDispositivo($conexion, $dispositivo);
	
	echo $res;
	
	$consulta = consultaDispositivo($conexion, '1000000000003');

	echo $consulta['NOMBRE']. "<br/>";
	foreach ($consulta as $celda) {
		echo $celda;
	}
	
	
	echo "<br/>";
	echo "<br/>";
	
	//5 - Eliminación
	
	$res = eliminaDispositivo($conexion, '1000000000003');
	
	echo $res;
	
	$consulta = consultaDispositivo($conexion, '1000000000003');

	foreach ($consulta as $celda) {
		echo $celda;
	}
	
	
	echo "<br/>";
	echo "<br/>";
	
		
	//6 - Consulta Paginada

	$consulta = consultaDispositivosPaginado($conexion, 1, 2);

	foreach ($consulta[1] as $dispositivo) {
		foreach ($dispositivo as $celda) {
			echo $celda;
		}
	}
	
	echo "<br/>";
	echo "<br/>";

	cerrarConexionBD($conexion);

?>