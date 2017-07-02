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
      <p>Inicia sesión para obtener un token temporal</p>
      <form action="obtainToken.php" method="get">
        <label for="nombreFabricante">Usuario:</label>
        <input id="nombreFabricante" name="nombreFabricante" type="text" value="<?php echo $nombreFabricante; ?>">
        <label for="password">Contraseña:</label>
        <input id="password" name="password" type="password">
        <button type="submit">Iniciar Sesión</button>
      </form>
  </main>
  </body>
</html>
