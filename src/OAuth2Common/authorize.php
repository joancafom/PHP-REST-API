<?php

// include our OAuth2 Server object
require_once 'server.php';

$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

// validate the authorize request
if (!$server->validateAuthorizeRequest($request, $response)) {
    $response->send();
    die;
}
// display an authorization form
if (empty($_POST)) {
  exit('
<form method="post">
  <label>Do You Authorize TestClient?</label><br />
  <input type="submit" name="authorized" value="yes">
  <input type="submit" name="authorized" value="no">
</form>');
}

/*
 * Additional Info for identifying the user
 * 
 */
 
 $userId = 'Bella';

// print the authorization code if the user has authorized your client
$is_authorized = ($_POST['authorized'] === 'yes');
$server->handleAuthorizeRequest($request, $response, $is_authorized,$userId);
if ($is_authorized) {
  // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
  $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
  	 $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	 echo "User ID associated with this token is {".$token['USER_ID']."}";
  exit("SUCCESS! Authorization Code: $code");
}
$response->send();

?>