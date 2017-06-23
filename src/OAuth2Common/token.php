<?php

	// Incluimos el objeto servidor de OAuth2.0
	require_once 'server.php';

	//Tipo de auth que estamos requiriendo
	$_POST['grant_type'] = 'client_credentials';
	$_SESSION['user_id_passthrough'] = $_POST['user_id_passthrough'];
	// Manejar una petición OAuth2 al servidor y responder al cliente
	$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();

?>