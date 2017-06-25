<?php

  function stmtPaginado($conexion, $query, $offset, $limit){
    try{

      $primer = $offset;
      $ultimo = $offset + $limit;

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

?>