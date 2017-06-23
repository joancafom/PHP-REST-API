<?php
  
  session_start();

  require_once('../BD/gestionBD.php');
  require_once('../login/gestionLogin.php');

  if( !isset($_SESSION['clientId']) || !isset($_SESSION['clientSecret']) || !isset($_REQUEST['nombreFabricante']) || !isset($_REQUEST['password']) ){
    header('Location: index.php');
    die();
  }

  $clientId = $_SESSION['clientId'];
  $clientSecret = $_SESSION['clientSecret'];
  $nombreFabricante = $_REQUEST['nombreFabricante'];
  $password = $_REQUEST['password'];

  unset($_SESSION['clientId']);
  unset($_SESSION['clientSecret']);

  $conexion = crearConexionBD();

  $exitoLogin = consultaLogin($conexion, $nombreFabricante, $password);

  if(!$exitoLogin){
    $_SESSION['erroresLogin'] = '<p>El usuario o la contraseña proporcionados no son correctos</p>';
    header('Location: index.php');
    die();
  }

  cerrarConexionBD($conexion);

  $resultado = performCurlCommand($clientId, $clientSecret);

  if($resultado == null){
    $_SESSION['erroresLogin'] = '<p>Ha ocurrido un error al intentar obtener el token. Puede que el clientId o clientSecret sean erróneos.</p>';
    header('Location: index.php');
    die();
  }

  $token = json_decode($resultado, true);
  //->{''}

  $_SESSION['user'] = $nombreFabricante;
  $_SESSION['token'] = $token;

  header('Location: success.php');

  /*
    Métodos Auxiliares
  */

  function performCurlCommand($clientId, $clientSecret){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost/PHP_API/OAuth2Common/token.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&user_id_passthrough=".$nombreFabricante);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId. ":" . $clientSecret);

    $headers = array();
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch) || (strpos($result, '404') !== false)) {
      $result = null;
    }

    curl_close ($ch);

    return $result;

  }

?>
