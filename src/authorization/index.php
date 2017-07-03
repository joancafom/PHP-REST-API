<?php

  session_start();

  if(!isset($_SESSION['clientId']) || !isset($_SESSION['clientSecret'])){
    $_SESSION['clientId'] = 'authorizerForm';
    $_SESSION['clientSecret'] = 'authorizerFormSecret';
  }

  $nombreFabricante = isset($_SESSION['nombreFabricante']) ? $_SESSION['nombreFabricante'] : '';

  if (isset($_SESSION['erroresLogin'])) {
    $erroresLogin = $_SESSION['erroresLogin'];
  }

?>
<html lang="es">
  <head>
    <meta charset="utf-8"/>
    <title>Joscarfom - Obtener Autorización</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
  </head>
  <body>

    <?php

      if(isset($erroresLogin)){

        echo "<header>";
          
        echo $erroresLogin;

        echo "</header>";

      }

    ?>

    <main>
      <div class="authDiv">
      <img src="img/key.png" alt="Key Logo">
      <form class="authForm" action="obtainToken.php" method="post">
        <p>Inicia sesión para obtener un token temporal</p>
        <div class="inputsDiv">
        <input id="user" name="nombreFabricante" type="text" placeholder="Fabricante" autofocus>
        <input id="password" name="password" type="password" placeholder="Contraseña">
        </div>
        <button type="submit">Iniciar Sesión</button>
      </form>
    </div>
  </main>
  </body>
</html>
