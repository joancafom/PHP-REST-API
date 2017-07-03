<?php

	require_once "utilidades.php";

	function crearConexionBD()
	{
		$host = "oci:dbname=localhost/XE;charset=UTF8";
		$usuario = "MH";
		$password = "MH";

		try{

			$conexion = new PDO($host,$usuario,$password,array(PDO::ATTR_PERSISTENT => true));
    		$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $conexion;

		}catch(PDOException $e){

			$_SESSION['excepcion'] = $e->GetMessage();
			replyToClient(array(),500,array(), 'html');

		}
	}

	function cerrarConexionBD($conexion){
		$conexion = null;
	}

?>
