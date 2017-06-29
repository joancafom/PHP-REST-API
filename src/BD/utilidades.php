<?php

  function stmtPaginado($conexion, $query, $offset, $limit){
    try{

      $primer = $offset;
      $ultimo = $offset + $limit - 1;

      $paginatedQuery = "SELECT * FROM ( "."SELECT ROWNUM RNUM, AUX.* FROM ( $query ) AUX "
                        ."WHERE ROWNUM <= :ultimo".") "."WHERE RNUM >= :primer";
      $stmt = $conexion->prepare($paginatedQuery);
      $stmt->bindParam(':primer', $primer);
      $stmt->bindParam(':ultimo', $ultimo);
      return $stmt;

    }catch (PDOException $e){
      $_SESSION['exception'] = $e->GetMessage();
      header("Location: exception.php");
    }
  }

  function total_consulta($conexion, $query, $paramName, $paramToBind){

     try {
         $total_consulta = "SELECT COUNT(*) AS TOTAL FROM ($query)";
         $stmt = $conexion->prepare($total_consulta);
         if($paramToBind != null && $paramName != null){
            if (count($paramName) == count($paramToBind)) {
              for ($i=0; $i < count($paramName); $i++) {
                $stmt->bindParam($paramName[$i],$paramToBind[$i]);
              }   
         } else {
           $_SESSION['excepcion'] = "El número de parámetros a enlazar no coincide con la cantidad de nombres usados para ello";
         }
         
      }

      $stmt->execute();
      $resultado = $stmt->fetch();
      $total = $resultado['TOTAL'];
      return  $total;
    }
    catch ( PDOException $e ) {
      $_SESSION['excepcion'] = $e->GetMessage();
      header("Location: excepcion.php");
    }
  }

  function validarLimitOffset($limit, $offset){

    $parametrosValidados = array('limit' => 10, 'offset' => 1);

    $offset = is_numeric($offset) ? intval($offset) : 1;
    $limit = is_numeric($limit) ? intval($limit) : 10;

    if($limit > 0){
      $parametrosValidados['limit'] = $limit;
    }

    if($offset > 0){
      $parametrosValidados['offset'] = $offset;
    }

    return $parametrosValidados;

  }

  function isValidJSON($str) {
      json_decode($str);
      return json_last_error() == JSON_ERROR_NONE;
  }

  //Terminal Operation, sends a response to the client
  function replyToClient($parametros = array(), $codigo = 200, $header = array(), $format){
    $response = new OAuth2\Response($parametros,$codigo,$header);
    $response->send($format);
    die();
  }

?>