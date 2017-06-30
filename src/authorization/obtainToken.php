<?php
  
  session_start();

  require_once '../resources/gestionRecursos.php';
  require_once '../BD/gestionBD.php' ;
  require_once '../login/gestionLogin.php' ;

  //Comprobamos que hemos llegado a esta página desde el formulario anterior, donde teníamos definidos todos esto parámetros

  if( !isset($_SESSION['clientId']) || !isset($_SESSION['clientSecret']) || !isset($_REQUEST['nombreFabricante']) || !isset($_REQUEST['password']) ){
    header('Location: index.php');
    die();
  }

  //Guardamos los parámetros en variables para un acceso más fácil
  $clientId = $_SESSION['clientId'];
  $clientSecret = $_SESSION['clientSecret'];
  $nombreFabricante = $_REQUEST['nombreFabricante'];
  $password = $_REQUEST['password'];

  //Por seguridad, eliminamos de la sesión las credenciales del cliente
  unset($_SESSION['clientId']);
  unset($_SESSION['clientSecret']);

  //Creamos una conexión a la BD para consultar las credenciales
  $conexion = crearConexionBD();

  $exitoLogin = consultaLogin($conexion, $nombreFabricante, $password);

  if(!$exitoLogin){
    $_SESSION['erroresLogin'] = '<p>El usuario o la contraseña proporcionados no son correctos</p>';
    header('Location: index.php');
    die();
  }

  //Si coinciden las credenciales, obtenemos la id del fabricante
  $idFabricante = consultaFOID($conexion, $nombreFabricante);

  //Una vez terminadas todas las operaciones con la BD, cerramos la conexión
  cerrarConexionBD($conexion);

  //Comprobomos que el id del fabricante ha sido obtenido con éxito
  if($idFabricante == null){
    $_SESSION['erroresLogin'] = '<p>Ha ocurrido un error al intentar obtener la identifición de este usuario.</p>';
    header('Location: index.php');
    die();
  }

  //Llamamos al archivo que se encarga de obtener los tokens con las credenciales y la id
  $resultado = performCurlCommand($clientId, $clientSecret, $idFabricante);

  //Comprobamos la validez del resultado de la petición cURL
  if($resultado == null){
    $_SESSION['erroresLogin'] = '<p>Ha ocurrido un error al intentar obtener el token. Puede que el clientId o clientSecret sean erróneos.</p>';
    header('Location: index.php');
    die();
  }

  //Si es válida, decodificamos el token como un array 
  $token = json_decode($resultado, true);

  //Guardamos en sesión el nombre del usuario y el token para la siguiente pantalla donde se mostrarán
  $_SESSION['user'] = $nombreFabricante;
  $_SESSION['token'] = $token;

  //Redireccionamos a la pantalla de éxito
  header('Location: success.php');


  

  /*
    Métodos Auxiliares
  */

  function performCurlCommand($clientId, $clientSecret, $idFabricante){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost/PHP_API/OAuth2Common/token.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&user_id_passthrough=".$idFabricante);
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
