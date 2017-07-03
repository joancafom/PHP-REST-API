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
    <link rel="stylesheet" type="text/css" href="css/success.css">
  </head>
  <body>
    <main>
      <div class="authDiv">
      <p class="pCentrado">¡Bienvenido <?php echo $user; ?>!</p>
      <p>Su token temporal de acceso es:</p>
      <p class="pCentrado"><?php echo $token['access_token']; ?></p>
      <p>Este token es válido hasta:</p>
      <p class="pCentrado"><?php echo ($token['expires_in']/60).' minutos'; ?></p>
    </div>
  </main>
  </body>
</html>
