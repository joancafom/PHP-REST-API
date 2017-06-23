<?php

	require_once '../BD/gestionBD.php';
	require_once 'gestionDispositivos.php';

	$conexion = crearConexionBD();

	$consultaDispositivos = consultaDispositivos($conexion);

	foreach ($consultaDispositivos as $dispositivo) {
		foreach ($dispositivo as $celda) {
			echo $celda;
		}
	}

	cerrarConexionBD($conexion);

?>