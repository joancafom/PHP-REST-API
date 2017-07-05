<?php

	// include our OAuth2 Server object
	require_once 'server.php';

	// Handle a request to a resource and authenticate the access token
	if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    	$server->getResponse()->send();
    	die;
	}
	echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
	
	/*
	 * Additional for user Identification
	 * 
	 */

	 $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	 echo "User ID associated with this token is {".$token['USER_ID']."}";
	 
?>