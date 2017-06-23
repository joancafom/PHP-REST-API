<?php

	require_once('../OAuth2/Autoloader.php');

	$dsn      = 'oci:dbname=localhost/XE;charset=UTF8';
	$username = 'MH';
	$password = 'MH';

	// Reporte de errores. Sólo para testeo
	ini_set('display_errors',1); error_reporting(E_ALL);


	OAuth2\Autoloader::register();

	// $dsn = Data Source Name 
	$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

	// Pass a storage object or array of storage objects to the OAuth2 server class
	$server = new OAuth2\Server($storage);

	// Add the "Client Credentials" grant type (it is the simplest of the grant types)
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

	// Add the "Authorization Code" grant type (this is where the oauth magic happens)
	$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

?>