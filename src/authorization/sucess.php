<?php

  session_start();

  if(!isset($_SESSION['user']) || !isset($_SESSION['token'])){
    header('Location: https://es.wikipedia.org/wiki/HTTP_403');
  }else{
    $user = $_SESSION['user'];
    $token = $_SESSION['token'];
  }

  $formUsuario = isset($_SESSION['formUsuario']) ? $_SESSION['formUsuario'] : '';

?>
<html lang="es">
  <head>
    <meta charset="utf-8"/>
    <title>Joscarfom - Token Temporal de Acceso</title>
  </head>
  <body>
    <main>
      <p>¡Bienvenido <?php echo $user; ?>! Su token temporal de acceso es:</p>
      <p><?php echo $token; ?></p>
      <p>Este token es válido hasta:</p>
      <p>EXPIRATION DATE</p>
  </main>
  </body>
</html>
