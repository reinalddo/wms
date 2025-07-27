<?php
include '../../config.php';
session_start();
if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) {
        case 'enter-view-alma':

                $sql = 'SELECT id, clave, nombre FROM c_almacenp WHERE Activo = 1';
                $res = getArraySQL($sql);

                $array = [
                    "almacen"=>$res
                ];

                echo json_encode($array);
            break;
        case 'search-area-alma':

                  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                //**************************************************************************************
                //PROCESO PARA SABER SI LA INSTANCIA DEBE LLEGAR LOS PEDIDOS A AUDITORÍA COMPLETOS O NO
                // Si en Rel_ModuloTipo, ID_Permiso = 3, Id_Tipo = 1, entonces debe llegar completo
                //**************************************************************************************
                  $cve_almac = $_POST['alma'];
                    $sql = "SELECT COUNT(*) as existe FROM Rel_ModuloTipo WHERE ID_Permiso = 3 AND Id_Tipo = 1 AND Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = '{$cve_almac}')";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
                       }
                    $row = mysqli_fetch_array($res);
                    $pedido_completo = $row['existe'];
                //**************************************************************************************
                //**************************************************************************************
                $pedido_completo_post = "";
                if (isset($_POST['pedido_completo']))
                  $pedido_completo_post = $_POST['pedido_completo'];


                $sql = "SELECT ID_URevision, descripcion, IFNULL(fol_folio, '') AS fol_folio, IFNULL(sufijo, '') AS sufijo FROM t_ubicaciones_revision WHERE Activo = 1 and cve_almac = '{$cve_almac}'";
                if($pedido_completo || $pedido_completo_post)
                  $sql = "SELECT DISTINCT ID_URevision, descripcion, IFNULL(fol_folio, '') AS fol_folio, '' as sufijo FROM t_ubicaciones_revision WHERE Activo = 1 and cve_almac = '{$cve_almac}'";
                $sql_area = $sql;
                $area = getArraySQL($sql);

                $sql = 'SELECT a.id_user, a.nombre_completo FROM c_usuario a, c_almacenp b WHERE a.Activo = 1 and b.clave = "{$cve_almac}" and a.cve_cia = b.cve_cia';
                $users = getArraySQL($sql);

                $sql = "SELECT DISTINCT Sufijo AS id_pedido, Fol_folio FROM th_subpedido WHERE (STATUS = 'L' OR STATUS = 'R') 
                        AND (cve_almac = (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}') OR cve_almac = '".$cve_almac."') ORDER BY Fec_Entrada DESC";
                if($pedido_completo || $pedido_completo_post)
                  $sql = "SELECT DISTINCT Fol_folio AS id_pedido, Fol_folio AS Fol_folio 
                          FROM th_subpedido 
                          WHERE (SELECT GROUP_CONCAT(DISTINCT ths.status SEPARATOR '') FROM th_subpedido ths WHERE ths.Fol_folio = th_subpedido.fol_folio) IN ('L', 'R') 
                          AND (cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$cve_almac."') OR cve_almac = '".$cve_almac."') ORDER BY Fec_Entrada DESC";

                $pedidos = getArraySQL($sql);

                $array = [
                    "area"=>$area,
                    "users"=>$users,
                    "sql"=>$sql,
                    "sql_area"=>$sql_area,
                    "pedido_completo"=>$pedido_completo,
                    "pedidos"=>$pedidos
                ];

                echo json_encode($array);
            break;
        case 'enter-view':

                $sql = 'SELECT id_user, nombre_completo FROM c_usuario WHERE Activo = 1';
                $users = getArraySQL($sql);
                $cve_almac = $_POST['alma'];
                $id_almac = $_POST['alma'];
                  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                if(isset($_POST['id_alma']))//está en traslado.php para que me devuelva la clave a partir de id
                {
                    $sql = "SELECT clave FROM c_almacenp WHERE id='{$cve_almac}'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                       }
                    $row = mysqli_fetch_array($res);
                    $cve_almac = $row['clave'];
                }
                $pedido_completo_post = "";
                if (isset($_POST['pedido_completo']))
                  $pedido_completo_post = $_POST['pedido_completo'];
                //$sql = "SELECT id_pedido, Fol_folio FROM th_pedido WHERE Activo = 1 and (status = 'L' OR status = 'R') and cve_almac = '$cve_almac' GROUP BY id_pedido ORDER BY id_pedido DESC";

                //**************************************************************************************
                //PROCESO PARA SABER SI LA INSTANCIA DEBE LLEGAR LOS PEDIDOS A AUDITORÍA COMPLETOS O NO
                // Si en Rel_ModuloTipo, ID_Permiso = 3, Id_Tipo = 1, entonces debe llegar completo
                //**************************************************************************************
                    $sql = "SELECT COUNT(*) as existe FROM Rel_ModuloTipo WHERE ID_Permiso = 3 AND Id_Tipo = 1 AND Cve_Almac = '{$cve_almac}'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
                       }
                    $row = mysqli_fetch_array($res);
                    $pedido_completo = $row['existe'];
                //**************************************************************************************
                //**************************************************************************************

                $sql = "SELECT DISTINCT Sufijo AS id_pedido, Fol_folio AS Fol_folio FROM th_subpedido WHERE (STATUS = 'L' OR STATUS = 'R') 
                        AND (cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$cve_almac."') OR cve_almac = '".$cve_almac."') ORDER BY Fec_Entrada DESC";
                if($pedido_completo || $pedido_completo_post)
                  $sql = "SELECT DISTINCT Fol_folio AS id_pedido, Fol_folio AS Fol_folio 
                          FROM th_subpedido 
                          WHERE (SELECT GROUP_CONCAT(DISTINCT ths.status SEPARATOR '') FROM th_subpedido ths WHERE ths.Fol_folio = th_subpedido.fol_folio) IN ('L', 'R') 
                          AND (cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$cve_almac."') OR cve_almac = '".$cve_almac."') ORDER BY Fec_Entrada DESC";
                $pedidos = getArraySQL($sql);
                $sql_pedidos = $sql; 

                $sql = 'SELECT ID_URevision, descripcion FROM t_ubicaciones_revision WHERE Activo = 1 and (fol_folio IS NULL OR fol_folio = "")';
                $area = getArraySQL($sql);
/*
                $sql = "
                      SELECT * FROM (
                      SELECT
                          c_charolas.IDContenedor,
                          c_charolas.descripcion,
                          c_charolas.clave_contenedor AS clave_contenedor,
                          IF(c_charolas.Permanente = 1, 'N', 'S') AS Generico,
                          IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), 0,IF(c_cliente.Cve_Clte != '', 0, 1)) as status
                      FROM c_charolas
                        LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
                        LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
                        LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
                        LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
                        LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
                        LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
                        LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
                      WHERE c_charolas.Activo = 1 AND c_charolas.Permanente = 1 AND c_charolas.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0)
                        AND c_almacenp.id='$cve_almac'
                      GROUP BY c_charolas.IDContenedor  
                      #ORDER BY c_charolas.IDContenedor
                      ) AS t #WHERE t.status = 1

                      UNION

                      SELECT
                        ch.IDContenedor,
                        CONCAT(ch.descripcion, ' (Pallet Genérico)') AS descripcion,
                        ch.clave_contenedor AS clave_contenedor,
                        IF(ch.Permanente = 1, 'N', 'S') AS Generico,
                        '' AS STATUS
                      FROM c_charolas ch
                      LEFT JOIN c_almacenp ON c_almacenp.id = ch.cve_almac
                      WHERE ch.Activo = 1 AND ch.Permanente = 0  AND ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) AND LEFT(ch.clave_contenedor,2) != 'LP'
                      AND c_almacenp.id='$cve_almac'
                      GROUP BY ch.IDContenedor  
                      ORDER BY Generico
                ";
*/
/*
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "SELECT clave FROM c_almacenp WHERE id = '$cve_almac'";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                   }
                $row = mysqli_fetch_array($res);
                $clave_almacen = $row["clave"];

                $sql = "CALL SPWS_DameContenedoresDisponibles('$clave_almacen')";
*/
                $sql = "SELECT DISTINCT
                        ch.IDContenedor,
                        IF(ch.TipoGen = 1, CONCAT(ch.descripcion, ' (Pallet Genérico)'), ch.descripcion) AS descripcion,
                        ch.clave_contenedor AS clave_contenedor,
                        IF(ch.TipoGen = 1, 'S', 'N') AS Generico,
                        '' AS STATUS
                      FROM c_charolas ch
                      LEFT JOIN c_almacenp ON c_almacenp.id = ch.cve_almac 
                      WHERE 
                      (
                        #(
                          #ch.Activo = 1 AND 
                          #ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) AND 
                          #ch.IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND 
                          #(ch.clave_contenedor NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) OR ch.CveLP NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))
                        #) OR 
                        (ch.TipoGen = 1 AND ch.Activo = 1)
                      ) 
                      AND (c_almacenp.id='$id_almac' OR c_almacenp.clave='$id_almac') #AND ch.clave_contenedor NOT LIKE 'LP%'
                      GROUP BY ch.IDContenedor  
                      ORDER BY Generico DESC";
                $pallets = getArraySQL($sql);
                $sql_pallets = $sql;

                $array = [
                    "users"=>$users,
                    "sql" => $sql_pedidos,
                    "sql_pallets" => $sql_pallets,
                    "pallets" => $pallets,
                    "pedidos"=>$pedidos
                ];

                echo json_encode($array);
            break;
        
     //asignacion de la tabla id_pedido
    //if(th_pedido.status = "L","PENDIENTE DE AUDITAR", "AUDITANDO") as status 
      case 'pedidos_qa':
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          if($_POST['status'] == "L" or $_POST['status'] == "R" or $_POST['status'] == "P")
            $and = ' th_pedido.status = "'.$_POST['status'].'" ';
          else
            $and = ' (th_pedido.status = "L" or th_pedido.status = "R" or th_pedido.status = "P")';
            //t_ubicaciones_revision.descripcion as "Area", 
            $sql =  'SELECT
              id_pedido, 
              th_pedido.Fol_folio, 
              c_cliente.RazonSocial ,
              sum(Num_cantidad) as "cantidad",
              CASE th_pedido.status WHEN "R" THEN (SELECT descripcion FROM t_ubicaciones_revision WHERE fol_folio = th_pedido.Fol_folio) END AS "Area", 
              CASE th_pedido.status WHEN "L" THEN "PENDIENTE DE AUDITAR" WHEN "R" THEN "AUDITANDO" WHEN "P" THEN "PENDIENTE DE EMPAQUE" END AS status
              FROM `th_pedido`
              inner join c_cliente on c_cliente.Cve_clte = th_pedido.Cve_clte
              inner join td_pedido on td_pedido.Fol_folio = th_pedido.Fol_folio
              LEFT join t_ubicaciones_revision on t_ubicaciones_revision.fol_folio = th_pedido.Fol_folio
              WHERE '.$and.'
              group by th_pedido.Fol_folio
              '
            ;
            // AND (th_pedido.Fol_folio NOT IN (SELECT fol_folio FROM t_ubicaciones_revision))
        
          
        
          if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
        
          mysqli_close($conn);
          $responce = new stdClass();

          if( $count >0 ) {
          $total_pages = ceil($count/$limit);
          } else {
          $total_pages = 0;
          } if ($page > $total_pages)
          $page=$total_pages;

          $responce->page = $page;
          $responce->total = $total_pages;
          $responce->records = $count;
          $responce->query = $sql;
        
          $i = 0;
        

          while ($row = mysqli_fetch_array($res)) {
            //echo var_dump($row);
            $responce->rows[$i]['id']=$row['id_pedido'];
            $responce->rows[$i]['cell']=array(
                                              $row[""],
                                              $row["id_pedido"],
                                              $row["Fol_folio"],
                                              utf8_encode($row["RazonSocial"]),
                                              $row["cantidad"],
                                              $row["Area"],
                                              $row["status"]
                                            );
            $i++;
          }
        
        
           echo json_encode($responce);
      break;
        
      case 'detalle_qa':
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            //t_ubicaciones_revision.descripcion as "Area", 
            $sql =  "SELECT DISTINCT
                        UPPER(b.cve_articulo) AS cve_articulo,
                        b.des_articulo,
                        #d.Num_cantidad,
                        IF(b.control_peso = 'S', TRUNCATE(ts.Cantidad, 3), TRUNCATE(ts.Cantidad, 0)) AS Num_cantidad,
                        IF(b.control_peso = 'S', TRUNCATE(ts.revisadas, 3), TRUNCATE(ts.revisadas, 0)) AS Num_revisadas,
                        IF(b.control_peso = 'S', TRUNCATE(ts.Num_Empacados, 3), TRUNCATE(ts.Num_Empacados, 0)) AS Num_Empacados,
                        b.cve_codprov,
                        b.control_peso,
                        b.barras2,
                        a.status,
                        b.num_multiplo,
                        ts.LOTE AS cve_lote,
                        IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        b.control_lotes,
                        b.control_numero_series,
                        a.Fol_folio 
                      FROM
                        td_pedido a,
                        c_articulo b, td_subpedido d, td_surtidopiezas ts
                        LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo= c.cve_articulo
                      WHERE a.Fol_folio = '".$_POST['folio']."' AND a.Fol_folio = d.fol_folio AND d.fol_folio = ts.fol_folio AND d.Sufijo = ts.Sufijo AND a.Cve_articulo = b.cve_articulo AND d.Cve_articulo = a.Cve_articulo AND a.Cve_articulo = ts.Cve_articulo 
                      ORDER BY a.status ASC"
            ;
            // AND (th_pedido.Fol_folio NOT IN (SELECT fol_folio FROM t_ubicaciones_revision))
        
          
        
          if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
        
          mysqli_close($conn);
          $responce = new stdClass();

          if( $count >0 ) {
          $total_pages = ceil($count/$limit);
          } else {
          $total_pages = 0;
          } if ($page > $total_pages)
          $page=$total_pages;

          $responce->page = $page;
          $responce->total = $total_pages;
          $responce->records = $count;
          $responce->query = $sql;
        
          $i = 0;
        

          while ($row = mysqli_fetch_array($res)) {
            //echo var_dump($row);
            $responce->rows[$i]['id']=$row['id_pedido'];
            $responce->rows[$i]['cell']=array(
                                              $row["cve_articulo"],
                                              utf8_encode($row["des_articulo"]),
                                              $row["cve_lote"],
                                              $row["Caducidad"],
                                              $row["Num_cantidad"],
                                              ($row["Num_cantidad"]-$row["Num_revisadas"]),
                                              $row["Num_revisadas"]
                                            );
            $i++;
          }







        
           echo json_encode($responce);
      break;
        
        
      //cambio de status en la base de datos
       case 'cambio_status':

       $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $folio = $_POST['folios']; 

        for ($i = 0; $i < count($folio); $i++)
        {
          $sql="UPDATE `th_pedido` 
          SET `status`= '".$_POST['status']."'
          where fol_folio = '".$folio[$i]."'";
          if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }

          $sql="UPDATE `th_subpedido` 
          SET `status`= '".$_POST['status']."'
          where fol_folio = '".$folio[$i]."'";
          if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          
          $sql_revision="SELECT * FROM t_ubicaciones_revision WHERE fol_folio = '".$folio[$i]."'";
          if (!($res_revision = mysqli_query($conn, $sql_revision))) {
            //echo "X1";
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          
          if ($res_revision->num_rows > 0){
            //echo "X2";
             $sql_prueba="UPDATE `t_ubicaciones_revision` 
            SET `fol_folio`= NULL
            where fol_folio = '".$folio[$i]."'";
            //echo $sql_prueba;
             if (!($res = mysqli_query($conn, $sql_prueba))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
             }
          }
        }
        $responce->query=$sql_revision;
        $responce->success=true;
        echo json_encode($responce);
        
        break;
       
        
        
      //cambio de status a auditado
        case 'cambio_auditado':

       $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $folio = $_POST['folios']; 

        for ($i = 0; $i < count($folio); $i++)
        {
          //una query que no deje cambiar cuando sea status R 
       
          /*$sql="UPDATE `th_pedido` 
          SET `status`= '".$_POST['status']."'
          where fol_folio = '".$folio[$i]."'
          and `status`= '"L"'";
          */
          $sql="UPDATE `th_pedido` 
          SET `status`= 'P'
          where fol_folio = '".$folio[$i]."'
          and `status`= 'L'";
          
          if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          
          $sql_revision="SELECT * FROM t_ubicaciones_revision WHERE fol_folio = '".$folio[$i]."'";
          if (!($res_revision = mysqli_query($conn, $sql_revision))) {
            //echo "X1";
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          //banderazo
          $sql_banderazo="UPDATE `td_pedido` 
                          SET `Auditado`= 'N'
                          where Fol_folio = '".$folio[$i]."'";
          if (!($res_revision = mysqli_query($conn, $sql_banderazo))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
        }
        $responce->query=$sql_revision;
        $responce->success=true;
        echo json_encode($responce);
        break;
        
        
        
        //muestra las mesas o bien areas de revision disponibles
        case 'mesas_disponibles':

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql="SELECT
              t_ubicaciones_revision.descripcion
              FROM t_ubicaciones_revision
              WHERE t_ubicaciones_revision.fol_folio IS NULL
              AND t_ubicaciones_revision.cve_almac = 100
              AND t_ubicaciones_revision.Activo = 1";
          if (!($res = mysqli_query($conn, $sql))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
             }       
        $i=0;
        while ($row = mysqli_fetch_array($res)) {
            $responce[$i]=$row["descripcion"];
            $i++;
          }
        echo json_encode($responce);
        break;

        case 'auditar':
              $array = "";
              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

              //$id_pedido = $_POST['pedido'];
              $sufijo = $_POST['pedido'];//pedido se cambió por el sufijo porque antes se basaba todo en th_pedido

              //if($id_pedido == "-")
              //{
                $sql = "SELECT id_pedido FROM th_pedido WHERE Fol_folio = '".$_POST['folio']."'";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                   }
                $row = mysqli_fetch_array($res);
                $id_pedido = $row["id_pedido"];
              //}

              $sql = "SELECT fol_folio, sufijo, cve_ubicacion FROM t_ubicaciones_revision WHERE ID_URevision = ".$_POST['area'];
              if (!($res = mysqli_query($conn, $sql))) {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                 }
              $row = mysqli_fetch_array($res);
              $fol_folio_ocupado = $row["fol_folio"];
              $sufijo_ocupado = $row["sufijo"];
              $cve_ubicacion = $row["cve_ubicacion"];


              $sql = "SELECT cve_usuario FROM c_usuario WHERE id_user = ".$_POST['respon'];
              if (!($res = mysqli_query($conn, $sql))) {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                 }
              $row = mysqli_fetch_array($res);
              $cve_usuario_reviso = $row["cve_usuario"];


              $ocupado = 1;
              if($fol_folio_ocupado != "" && $sufijo_ocupado != "")
              {

                $sql_sufijo = "";
                //if($sufijo)
                if($_POST['folio'] != $sufijo && $sufijo)
                {
                  $sql_sufijo = " AND sufijo = '".$sufijo."' ";
                }
                $sql = "SELECT COUNT(*) ocupado FROM t_ubicaciones_revision WHERE fol_folio = '".$_POST['folio']."' {$sql_sufijo} AND  ID_URevision = ".$_POST['area'];
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                   }
                $row = mysqli_fetch_array($res);
                $ocupado = $row["ocupado"];
              }

              if($ocupado == 0)
              {
                $sql = 'UPDATE t_ubicaciones_revision SET fol_folio = "'.$_POST['folio'].'", sufijo = "'.$sufijo.'" WHERE ID_URevision = '.$_POST['area'];
                executeSQL($sql);


                //, Cve_Usuario = "'.$_POST['respon'].'"
                $sql = 'UPDATE th_pedido SET status = "R"  WHERE id_pedido = '.$id_pedido;
                executeSQL($sql);

                $sql = "UPDATE th_subpedido SET HIE = NOW(), HIR = NOW(), Reviso = '{$cve_usuario_reviso}', buffer = '{$cve_ubicacion}' WHERE fol_folio = '".$_POST['folio']."'";
                executeSQL($sql);
              }

                $sql = "SELECT b.RazonSocial FROM th_pedido a, c_cliente b WHERE a.id_pedido = '".$id_pedido."' and a.Cve_clte = b.Cve_clte";
                $cliente = getArraySQL($sql);

                //$sql = "SELECT b.cve_articulo, b.des_articulo, a.Num_cantidad, a.Num_revisadas, a.status, a.cve_lote, a.Fol_folio FROM td_pedido a, c_articulo b WHERE a.Fol_folio = '".$_POST['folio']."' and a.Cve_articulo = b.cve_articulo";
                $sql_sufijo = "";
                //if($sufijo)
                if($_POST['folio'] != $sufijo && $sufijo)
                {
                  $sql_sufijo = " AND d.Sufijo = $sufijo ";
                }

                //********************************************************************************************
                //CASO 1: El pedido está en td_pedido pero no en td_surtidopiezas y No pertenece a una OLA
                //********************************************************************************************

                $sql = "SELECT COUNT(*) AS caso1 FROM td_pedido d WHERE d.Fol_folio = '".$_POST['folio']."' AND CONCAT(d.Fol_folio, d.Cve_articulo, IFNULL(d.cve_lote, '')) NOT IN (SELECT CONCAT(fol_folio, cve_articulo, IFNULL(Lote, '')) FROM td_surtidopiezas WHERE fol_folio = d.Fol_folio)";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                   }
                $row = mysqli_fetch_array($res);
                $caso1 = $row["caso1"];

                $sql_caso1 = " ORDER BY a.status ASC ";
                if($caso1)
                {
                    /*
                    $sql_caso1 = " 

                            UNION

                          SELECT DISTINCT
                            UPPER(b.cve_articulo) AS cve_articulo,
                            CONVERT(b.des_articulo USING utf8) AS des_articulo,
                            #d.Num_cantidad,
                            IF(b.control_peso = 'S', TRUNCATE(a.Num_cantidad, 3), TRUNCATE(a.Num_cantidad, 0)) AS Num_cantidad,
                            #0 AS Num_cantidad,
                            0 AS Num_revisadas,
                            0 AS Num_Empacados,
                            b.cve_codprov,
                            b.control_peso,
                            b.barras2,
                            a.status,
                            b.num_multiplo,
                            um.mav_cveunimed AS codigo_unidad,
                            IFNULL(a.cve_lote, '') AS cve_lote,
                            IF(b.control_lotes = 'S' AND b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                            b.control_lotes,
                            b.control_numero_series,
                            a.Fol_folio,
                            'caso1' as caso
                          FROM
                            td_pedido a
                            LEFT JOIN c_lotes c ON c.LOTE = IFNULL(a.cve_lote, '') AND a.Cve_articulo = c.cve_articulo
                            ,c_articulo b
                            LEFT JOIN c_unimed um ON um.id_umed = b.unidadMedida
                          WHERE a.Fol_folio = '".$_POST['folio']."' AND a.Cve_articulo = b.cve_articulo 
                          AND CONCAT(a.Fol_folio, a.Cve_articulo, IFNULL(a.cve_lote, '')) NOT IN (SELECT CONCAT(fol_folio, cve_articulo, IFNULL(Lote, '')) FROM td_surtidopiezas WHERE fol_folio = a.Fol_folio)
                          #AND d.fol_folio = ts.fol_folio AND d.Sufijo = ts.Sufijo AND a.Cve_articulo = ts.Cve_articulo 
                          ORDER BY status ASC";
                          */
                    $sql_caso1 = " 

                            UNION

                          SELECT DISTINCT
                            UPPER(b.cve_articulo) AS cve_articulo,
                            CONVERT(b.des_articulo USING utf8) AS des_articulo,
                            #d.Num_cantidad,
                            IF(b.control_peso = 'S', TRUNCATE(a.Num_cantidad, 3), TRUNCATE(a.Num_cantidad, 0)) AS Num_cantidad,
                            #0 AS Num_cantidad,
                            0 AS Num_revisadas,
                            0 AS Num_Empacados,
                            b.cve_codprov,
                            b.control_peso,
                            b.barras2,
                            a.status,
                            b.num_multiplo,
                            um.mav_cveunimed AS codigo_unidad,
                            IFNULL(a.cve_lote, '') AS cve_lote,
                            IF(b.control_lotes = 'S' AND b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                            b.control_lotes,
                            b.control_numero_series,
                            a.Fol_folio,
                            'caso1' as caso
                          FROM
                            td_pedido a
                            LEFT JOIN c_lotes c ON c.LOTE = IFNULL(a.cve_lote, '') AND a.Cve_articulo = c.cve_articulo
                            ,c_articulo b
                            LEFT JOIN c_unimed um ON um.id_umed = b.unidadMedida
                          WHERE a.Fol_folio = '".$_POST['folio']."' AND a.Cve_articulo = b.cve_articulo 
                          AND CONCAT(a.Fol_folio, a.Cve_articulo) NOT IN (SELECT CONCAT(fol_folio, cve_articulo) FROM td_surtidopiezas WHERE fol_folio = a.Fol_folio)
                          #AND d.fol_folio = ts.fol_folio AND d.Sufijo = ts.Sufijo AND a.Cve_articulo = ts.Cve_articulo 
                          ORDER BY status ASC";
                }
                //********************************************************************************************


                $sql = "
                      SELECT DISTINCT
                        UPPER(b.cve_articulo) AS cve_articulo,
                        CONVERT(b.des_articulo USING utf8) AS des_articulo,
                        #d.Num_cantidad,
                        IF(b.control_peso = 'S', TRUNCATE(ts.Cantidad, 3), TRUNCATE(ts.Cantidad, 0)) AS Num_cantidad,
                        IF(b.control_peso = 'S', TRUNCATE(ts.revisadas, 3), TRUNCATE(ts.revisadas, 0)) AS Num_revisadas,
                        IF(b.control_peso = 'S', TRUNCATE(ts.Num_Empacados, 3), TRUNCATE(ts.Num_Empacados, 0)) AS Num_Empacados,
                        b.cve_codprov,
                        b.control_peso,
                        b.barras2,
                        a.status,
                        b.num_multiplo,
                        um.mav_cveunimed AS codigo_unidad,
                        ts.LOTE AS cve_lote,
                        IF(b.control_lotes = 'S' AND b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                        b.control_lotes,
                        b.control_numero_series,
                        a.Fol_folio, 
                        'caso0' as caso
                      FROM
                        td_pedido a,
                        c_articulo b
                        LEFT JOIN c_unimed um ON um.id_umed = b.unidadMedida
                        , td_subpedido d, td_surtidopiezas ts
                        LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo= c.cve_articulo
                      WHERE a.Fol_folio = '".$_POST['folio']."' {$sql_sufijo} AND a.Fol_folio = d.fol_folio AND d.fol_folio = ts.fol_folio AND d.Sufijo = ts.Sufijo AND a.Cve_articulo = b.cve_articulo AND d.Cve_articulo = a.Cve_articulo AND a.Cve_articulo = ts.Cve_articulo 
                      {$sql_caso1}";
                $table = getArraySQL($sql);
                $sql_table = $sql;


                $sql_sufijo = "";$sql_sufijo2 = "";$sql_sufijo3 = "";
                //if($sufijo)
                if($_POST['folio'] != $sufijo && $sufijo)
                {
                  $sql_sufijo = " AND res_tipo1.Sufijo = $sufijo ";
                  $sql_sufijo2 = " AND res_tipo2.Sufijo = $sufijo ";
                  $sql_sufijo3 = " AND res_tipo3.Sufijo = $sufijo ";
                  
                }

                $sql = "
                      SELECT * FROM (
                          SELECT DISTINCT
                              CONVERT(UPPER(b.cve_articulo) USING utf8) AS cve_articulo,
                              d.Sufijo AS Sufijo,
                              CONVERT(b.des_articulo USING utf8) AS des_articulo,
                              a.Num_cantidad,
                              ts.cantidad AS surtidas,
                              IF(tc.Num_Empacados = 0, tc.Cantidad, tc.Num_Empacados) AS Num_Empacados,
                              CONVERT((SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = th.cve_tipocaja) USING utf8) AS tipo_caja,
                              #IFNULL((SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = b.tipo_caja), '') AS tipo_caja,
                              th.NCaja,
                              a.status,
                              CONVERT(ts.LOTE USING utf8) AS cve_lote,
                              IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                              b.control_lotes,
                              b.control_numero_series,
                              CONVERT((SELECT c_charolas.clave_contenedor FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima) USING utf8) AS pallet,
                              CONVERT(IFNULL((SELECT c_charolas.CveLP FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima), '' ) USING utf8) AS CveLP,
                              CONVERT(a.Fol_folio USING utf8) AS Fol_folio 
                          FROM
                          t_tarima tt
                          LEFT JOIN td_subpedido a ON tt.Fol_Folio = a.Fol_folio AND tt.cve_articulo = a.Cve_articulo AND tt.Sufijo = a.Sufijo 
                          LEFT JOIN th_cajamixta th ON th.fol_folio = a.Fol_folio AND (th.cve_tipocaja IN (SELECT id_tipocaja FROM c_tipocaja ) OR (th.cve_tipocaja = 0))
                          LEFT JOIN td_cajamixta tc ON th.Cve_CajaMix = tc.Cve_CajaMix AND tc.Cve_CajaMix IN (SELECT Caja_ref FROM t_tarima )
                          LEFT JOIN td_subpedido d ON d.Cve_articulo = tt.Cve_articulo AND d.fol_folio = a.Fol_folio
                          LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                          LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
                          ,c_articulo b
                          WHERE a.Fol_folio = '".$_POST['folio']."' AND tt.Caja_ref = tc.Cve_CajaMix
                          AND a.Cve_articulo = b.cve_articulo
                          GROUP BY NCaja
                          ) AS res_tipo1 WHERE res_tipo1.Num_Empacados != '' {$sql_sufijo}

                          UNION

                          SELECT * FROM (
                              SELECT DISTINCT
                                  UPPER(b.cve_articulo) AS cve_articulo,
                                  d.Sufijo AS Sufijo,
                                  b.des_articulo,
                                  a.Num_cantidad,
                                  ts.cantidad AS surtidas,
                                  tt.Num_Empacados,
                                  '' AS tipo_caja,
                                  '' AS NCaja,
                                  a.status,
                                  ts.LOTE AS cve_lote,
                                  IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                                  b.control_lotes,
                                  b.control_numero_series,
                                  (SELECT c_charolas.clave_contenedor FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima ) AS pallet,
                                  IFNULL((SELECT c_charolas.CveLP FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima), '' ) AS CveLP,
                                  a.Fol_folio 
                              FROM
                              td_pedido a
                              LEFT JOIN t_tarima tt ON tt.Fol_Folio = a.Fol_folio AND tt.cve_articulo = a.Cve_articulo AND tt.lote = IFNULL(a.cve_lote, '') AND tt.Caja_ref = 0
                              LEFT JOIN td_subpedido d ON d.Cve_articulo = tt.Cve_articulo AND d.fol_folio = a.Fol_folio
                              LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                              LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
                              ,c_articulo b
                              WHERE a.Fol_folio = '".$_POST['folio']."'
                              AND a.Cve_articulo = b.cve_articulo
                              ) AS res_tipo2 WHERE res_tipo2.Num_Empacados != '' {$sql_sufijo2}

                          UNION

                          SELECT * FROM (
                              SELECT DISTINCT
                                  UPPER(b.cve_articulo) AS cve_articulo,
                                  d.Sufijo AS Sufijo,
                                  b.des_articulo,
                                  a.Num_cantidad,
                                  ts.cantidad AS surtidas,
                                  tc.Num_Empacados,
                                  IFNULL((SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = th.cve_tipocaja), '') AS tipo_caja,
                                  th.NCaja,
                                  a.status,
                                  ts.LOTE AS cve_lote,
                                  IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                                  b.control_lotes,
                                  b.control_numero_series,
                                  '' AS pallet,
                                  '' AS CveLP,
                                  a.Fol_folio 
                              FROM
                              td_pedido a
                              LEFT JOIN th_cajamixta th ON th.fol_folio = a.Fol_folio 
                              LEFT JOIN td_cajamixta tc ON th.Cve_CajaMix = tc.Cve_CajaMix AND tc.Cve_CajaMix NOT IN (SELECT Caja_ref FROM t_tarima) AND tc.Cve_articulo = a.Cve_articulo
                              LEFT JOIN td_subpedido d ON d.Cve_articulo = a.Cve_articulo AND d.fol_folio = a.Fol_folio AND tc.Cve_articulo = a.Cve_articulo
                              LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                              LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
                              ,c_articulo b
                              WHERE a.Fol_folio = '".$_POST['folio']."'
                              AND a.Cve_articulo = b.cve_articulo
                              ) AS res_tipo3 WHERE res_tipo3.Num_Empacados != '' {$sql_sufijo3}
                              ORDER BY NCaja";
                $table2 = getArraySQL($sql);
                $sql_table2 = $sql;

                $sql = "SELECT  SUM(Num_cantidad) cant, SUM(Num_revisadas) revi FROM td_pedido WHERE Fol_folio = '".$_POST['folio']."'";
                $sum = getArraySQL($sql);

              if($ocupado == 0)
              {
                $array = [
                    "cliente"=>$cliente,
                    "sql_table" => $sql_table,
                    "sql_table2" => $sql_table2,
                    "table"=>$table,
                    "table2"=>$table2,
                    "continuar"=>0,
                    "ocupado"=>$ocupado,
                    "sum"=>$sum
                ];
              }
              else
              {
                $array = [
                    "cliente"=>$cliente,
                    "table"=>$table,
                    "table2"=>$table2,
                    "sql_table" => $sql_table,
                    "sql_table2" => $sql_table2,
                    "sum"=>$sum,
                    "ocupado"=>$ocupado,
                    "continuar"=>1
                  ];
              }

                echo json_encode($array);

                // R = AUDITANDO
                // L = PENDIENTE DE AUDITAR

            break;
        case 'verificar_usuario':
            $usuario = $_POST['usuario'];
            $passw = $_POST['passw'];

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $sql = "SELECT COUNT(*) AS correcto FROM c_usuario WHERE cve_usuario = '$usuario' AND pwd_usuario = '$passw'";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
               }
            $row = mysqli_fetch_array($res);
            $correcto = $row["correcto"];
            $array = [
                "correcto"=>$correcto
              ];

            echo json_encode($array);

        break;
        case 'VerificarCaso':

            $folio = $_POST['folio'];
            $articulo = $_POST['articulo'];
            $lote = "";
              if(empty($_POST['lote']))
                  $lote = "";
              else
                  $lote = $_POST['lote'];

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $sql = "SELECT COUNT(*) AS caso1 FROM td_pedido d WHERE d.Fol_folio = '".$_POST['folio']."' AND d.Cve_articulo = '$articulo' AND d.cve_lote = '$lote' AND CONCAT(d.Fol_folio, d.Cve_articulo, IFNULL(d.cve_lote, '')) NOT IN (SELECT CONCAT(fol_folio, cve_articulo, IFNULL(Lote, '')) FROM td_surtidopiezas WHERE fol_folio = d.Fol_folio)";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
               }
            $row = mysqli_fetch_array($res);
            $caso1 = $row["caso1"];

            $array = [
                "caso1"=>$caso1,
                "sql_caso1"=>$sql
              ];

            echo json_encode($array);

        break;
        case 'VerificarYSurtir':
            $folio = $_POST['folio'];
            $articulo = $_POST['articulo'];
            $lote = "";
              if(empty($_POST['lote']))
                  $lote = "";
              else
                  $lote = $_POST['lote'];

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

             //***********************************************************************************************
             //AQUI VERIFICO SI LA INSTANCIA TRABAJA CON RECORRIDO SURTIDO O NO.
             //SI ID_Permiso = 2 y Id_Tipo = 2, Entonces trabaja con ruta de surtido
             //SI ID_Permiso = 2 y Id_Tipo = 3, Entonces NO trabaja con ruta de surtido
             //***********************************************************************************************
             $sql = "
                 SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = (SELECT cve_almac FROM th_pedido WHERE fol_folio = '$folio')) AS cr WHERE cr.con_recorrido != -1;
             ";
             if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
             $row = mysqli_fetch_array($res);
             $con_recorrido = $row['con_recorrido'];
             //***********************************************************************************************

             $sql_ruta_surtido = "";
             if($con_recorrido)
             {
                $sql_ruta_surtido = " AND vs.Idy_Ubica IN (SELECT idy_ubica FROM td_ruta_surtido WHERE Activo = 1 AND idr IN (SELECT idr FROM th_ruta_surtido WHERE Activo = 1 AND cve_almac = (SELECT cve_almac FROM th_pedido WHERE fol_folio = '$folio'))) ";
             }

             $sql = "SELECT vs.*, ve.Cve_Contenedor, IFNULL(ch.IDContenedor, '') AS ntarima, u.CodigoCSD AS BL, (SELECT Num_cantidad FROM td_pedido WHERE fol_folio = '$folio' AND cve_articulo = '$articulo' AND cve_lote = '$lote') AS Cantidad_Surtir
                     FROM VS_ExistenciaParaSurtido vs
                     LEFT JOIN V_ExistenciaGralProduccion ve ON ve.cve_ubicacion = vs.Idy_Ubica AND ve.cve_articulo = vs.cve_articulo AND ve.cve_lote = vs.cve_lote AND ve.cve_almac = vs.cve_almac
                     LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
                     LEFT JOIN c_ubicacion u ON u.idy_ubica = vs.Idy_Ubica
                     WHERE vs.cve_articulo = '$articulo' AND vs.cve_lote = '$lote' AND vs.Cve_Almac = (SELECT cve_almac FROM th_pedido WHERE fol_folio = '$folio') AND vs.Existencia > (SELECT Num_cantidad FROM td_pedido WHERE fol_folio = '$folio' AND cve_articulo = '$articulo' AND cve_lote = '$lote') {$sql_ruta_surtido} LIMIT 1;";
             if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
             $row = mysqli_fetch_array($res);
             $cve_almac       = $row['Cve_Almac'];
             $idy_ubica       = $row['Idy_Ubica'];
             $Cve_Contenedor  = $row['Cve_Contenedor'];
             $ntarima         = $row['ntarima'];
             $BL              = $row['BL'];
             $Cantidad_Surtir = $row['Cantidad_Surtir'];

             $surtir = false;
             if($idy_ubica != "")
             {
                $surtir = true;

                if($Cve_Contenedor)
                {
                   $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $Cantidad_Surtir WHERE cve_articulo = '$articulo' AND lote = '$lote' AND idy_ubica = '$idy_ubica' AND ntarima = '$ntarima' AND cve_almac = '$cve_almac'";
                   if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                   $sql = "INSERT IGNORE INTO t_registro_surtido (idy_ubica, cve_almac, fol_folio, Sufijo, Cve_articulo, cve_usuario, claverp, ClaveEtiqueta, cve_lote, Cantidad, Activo) VALUES ('$idy_ubica', '$cve_almac', '$folio', 1, '$articulo', (SELECT Cve_Usuario FROM th_pedido WHERE fol_folio = '$folio'), '$BL', '$Cve_Contenedor', '$lote', $Cantidad_Surtir, 1)";
                   if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                      $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES('$articulo', '$lote', NOW(), '$idy_ubica', '$folio', $Cantidad_Surtir, 8, (SELECT Cve_Usuario FROM th_pedido WHERE fol_folio = '$folio'), $cve_almac, 1, NOW())";
                      if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}

                      $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES((SELECT MAX(id) as max_id FROM t_cardex), (SELECT clave FROM c_almacenp WHERE id = '$cve_almac'), (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), NOW(), '$idy_ubica', '{$folio}', 8, (SELECT Cve_Usuario FROM th_pedido WHERE fol_folio = '$folio'), 'O')";
                      if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}

                }
                else
                {
                   $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $Cantidad_Surtir WHERE cve_articulo = '$articulo' AND cve_lote = '$lote' AND idy_ubica = '$idy_ubica' AND cve_almac = '$cve_almac'";
                   if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                   $sql = "INSERT IGNORE INTO t_registro_surtido (idy_ubica, cve_almac, fol_folio, Sufijo, Cve_articulo, cve_usuario, claverp, ClaveEtiqueta, cve_lote, Cantidad, Activo) VALUES ('$idy_ubica', '$cve_almac', '$folio', 1, '$articulo', (SELECT Cve_Usuario FROM th_pedido WHERE fol_folio = '$folio'), '$BL', '0', '$lote', $Cantidad_Surtir, 1)";
                   if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                      $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES('$articulo', '$lote', NOW(), '$idy_ubica', '$folio', $Cantidad_Surtir, 8, (SELECT Cve_Usuario FROM th_pedido WHERE fol_folio = '$folio'), $cve_almac, 1, NOW())";
                      if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                }
            
                $sql = "INSERT IGNORE INTO td_surtidopiezas (fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, Num_Empacados, status) VALUES ('$folio', $cve_almac, 1, '$articulo', '$lote', $Cantidad_Surtir, 0, 0, 'S')";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}

            }

            $array = [
                "surtir"=>$surtir,
                "sql"=>$sql
              ];

            echo json_encode($array);
        break;
        case 'empacar':

                if(empty($_POST['lote']))
                    $lote = "";
                else
                    $lote = $_POST['lote'];
                    //$lote = '"'.$_POST['lote'].'"';

                $Num_revisadas = $_POST['reviT'];
                $reviT = $_POST['reviT'];
                $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                $alcanza_cajas   = true;
                $alcanza_pallets = true;
                //$sql = "SELECT Num_Empacados FROM td_pedido WHERE Fol_folio = '".$_POST['folio']."' AND Cve_articulo = '".$_POST['articulo']."' AND IFNULL(cve_lote, '') = '".$lote."'";
                $sql = "SELECT DISTINCT ts.Num_Empacados AS Num_Empacados, ts.revisadas AS Num_revisadas, ts.Cantidad AS Cantidad_Revisar
                        FROM td_pedido tp, td_surtidopiezas ts, c_articulo a 
                        WHERE tp.Fol_folio = ts.fol_folio AND tp.Cve_articulo = ts.Cve_articulo AND a.cve_articulo = tp.Cve_articulo AND 
                        tp.Fol_folio = '".$_POST['folio']."' AND (tp.Cve_articulo = '".$_POST['articulo']."' OR (tp.Cve_articulo = (SELECT cve_articulo FROM c_articulo WHERE cve_codprov = '".$_POST['articulo']."' AND cve_codprov!='')) OR (tp.Cve_articulo = (SELECT cve_articulo FROM c_articulo WHERE barras2 = '".$_POST['articulo']."' AND barras2!=''))) AND IFNULL(ts.LOTE, '') = '".$lote."'";
                $sql1 = $sql;
                if(!$result = mysqli_query($conexion, $sql)) 
                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                $row = mysqli_fetch_assoc($result);

                $Num_Empacados = $row['Num_Empacados'];
                $Num_revisadas = $row['Num_revisadas'];
                $Cantidad_Revisar = $row['Cantidad_Revisar'];
                $cantT = $Num_revisadas + $_POST["cantT"];
                $empacados = $_POST["cantT"] + $row['Num_Empacados'];
                $val_revisar = true;
                $Num_Empacados = $row['Num_Empacados'];


          if($Num_Empacados != $Cantidad_Revisar)
          {
                if($_POST['unidadMedida'] == 2 /*|| $_POST['unidadMedida'] == 3*/)
                {
                    $sql = "SELECT num_multiplo, cajas_palet FROM c_articulo WHERE cve_articulo = '".$_POST['articulo']."'";
                    if(!$result = mysqli_query($conexion, $sql)) 
                        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                    $row = mysqli_fetch_assoc($result);

                    $num_multiplo = $row['num_multiplo'];
                    $cajas_palet = $row['cajas_palet'];

                    //SELECT tp.Num_cantidad AS Num_cantidad, tp.Num_revisadas AS Num_revisadas 
                    $sql = "SELECT ts.Cantidad AS Num_cantidad, ts.revisadas AS Num_revisadas 
                            FROM td_pedido tp, td_surtidopiezas ts 
                            WHERE tp.Fol_folio = ts.fol_folio AND tp.Cve_articulo = ts.Cve_articulo AND 
                            tp.Fol_folio = '".$_POST['folio']."' AND tp.Cve_articulo = '".$_POST['articulo']."' AND IFNULL(ts.LOTE, '') = '".$lote."'";
                    if(!$result = mysqli_query($conexion, $sql)) 
                        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                    $row = mysqli_fetch_assoc($result);

                    $pedidas   = $row['Num_cantidad'];
                    $revisadas = $row['Num_revisadas'];

                    $a_revisar_cajas = $revisadas + $num_multiplo;
                    $a_revisar_pallets = $a_revisar_cajas * $cajas_palet;

                    if($a_revisar_cajas > $pedidas)
                       $alcanza_cajas = false;
                    else if($num_multiplo > 0)
                    {
                       $Num_revisadas = $a_revisar_cajas;
                       $cantT = ($_POST["cantT"]*$num_multiplo)+$revisadas;
                       //$empacados *= $num_multiplo;
                       $empacados = ($_POST["cantT"]*$num_multiplo)+$Num_Empacados;
                    }
/*
                    if($_POST['unidadMedida'] == 3 && $cajas_palet > 0)
                    {
                        if($a_revisar_pallets > $pedidas)
                           $alcanza_pallets = false;
                         else
                         {
                           $Num_revisadas = $a_revisar_pallets;
                           $cantT *= $cajas_palet;
                         }
                    }
*/
                    if(!$alcanza_cajas || !$alcanza_pallets) $Num_revisadas = $reviT;
                }

                //$cantT += $Num_Empacados;
                //$cantT += $Num_revisadas;


                $sql = 'UPDATE td_pedido SET Num_revisadas = "'.$cantT.'", Num_Empacados = "'.$empacados.'", status = "'.$_POST['status'].'" WHERE Fol_folio = "'.$_POST['folio'].'" and Cve_articulo = "'.$_POST['articulo'].'"';
                //AND IFNULL(cve_lote, "") = "'.$lote.'"
                $sql2 = $sql;
                executeSQL($sql);

                $sql = 'UPDATE td_subpedido SET Num_Revisda = "'.$cantT.'", Status = "'.$_POST['status'].'" WHERE fol_folio = "'.$_POST['folio'].'" and Cve_articulo = "'.$_POST['articulo'].'"';
                $sql2 = $sql;
                executeSQL($sql);

                $sql = 'UPDATE td_surtidopiezas SET revisadas = "'.$cantT.'", Num_Empacados = "'.$empacados.'" WHERE fol_folio = "'.$_POST['folio'].'" and Cve_articulo = "'.$_POST['articulo'].'" AND IFNULL(LOTE, "") = "'.$lote.'"';
                $sql2 = $sql;
                executeSQL($sql);
          }
          else 
          {
            $val_revisar = false;
          }
                $array = [
                    "sql1" => $sql1,
                    "sql2" => $sql2,
                    "val_revisar" => $val_revisar,
                    "alcanza_cajas" => $alcanza_cajas,
                    "alcanza_pallets" => $alcanza_pallets
                ];

                echo json_encode($array);

                // R = AUDITANDO
                // L = PENDIENTE DE AUDITAR

            break;
        case 'verificar_pedido':

                $folio = $_POST['folio'];
                $area  = $_POST['area'];
                $sufijo  = $_POST['sufijo'];

                if($area != "")
                {
                    //$sql = 'UPDATE t_ubicaciones_revision SET fol_folio = "'.$_POST['folio']."-".$sufijo.'" WHERE ID_URevision = '.$_POST['area'];
                    $sql = 'UPDATE t_ubicaciones_revision SET fol_folio = "'.$_POST['folio'].'", sufijo="'.$sufijo.'" WHERE ID_URevision = '.$_POST['area'];
                    if($sufijo == "")
                    $sql = 'UPDATE t_ubicaciones_revision SET fol_folio = "'.$_POST['folio'].'" WHERE ID_URevision = '.$_POST['area'];

                    executeSQL($sql);
                }


                $result = "";
                $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "SELECT ID_URevision, fol_folio, sufijo, descripcion FROM t_ubicaciones_revision WHERE fol_folio = '$folio' AND sufijo= '$sufijo'";

                if($sufijo == "")
                  $sql = "SELECT DISTINCT ID_URevision, fol_folio, '' as sufijo, descripcion FROM t_ubicaciones_revision WHERE fol_folio = '$folio'";

                if(!$result = mysqli_query($conexion, $sql)) 
                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                $row = mysqli_fetch_assoc($result);
                $fol_folio = $row['fol_folio'];
                $sufijo = $row['sufijo'];
                $id_mesa = $row['ID_URevision'];
                $mesa = $row['descripcion'];

                $ocupado = false;
                if($fol_folio != "" /*&& $sufijo != ""*/)
                   $ocupado = true;

                $array = [
                    "ocupado"=>$ocupado,
                    "id_mesa"=>$id_mesa,
                    "mesa"=>$mesa
                ];

                echo json_encode($array);

                // R = AUDITANDO
                // L = PENDIENTE DE AUDITAR

            break;
        case 'reiniciar_auditoria':

        $folio  = $_POST['folio'];
        $sufijo = $_POST['sufijo'];

        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_sufijo = "";
        if($sufijo)
          $sql_sufijo = " AND Sufijo = '$sufijo' ";

        $sql = "UPDATE td_pedido SET status = 'A', Num_revisadas = 0 WHERE Fol_folio = '$folio'";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        $sql = "UPDATE td_subpedido SET Status = 'A', Num_Revisda = 0 WHERE fol_folio = '$folio' {$sql_sufijo}";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        $sql = "UPDATE td_surtidopiezas SET revisadas = 0, Num_Empacados = 0 WHERE fol_folio = '$folio' {$sql_sufijo}";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        $sql = "DELETE FROM t_tarima WHERE Fol_Folio = '$folio' {$sql_sufijo}";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        $sql = "DELETE FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '$folio' {$sql_sufijo})";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        $sql = "DELETE FROM th_cajamixta WHERE fol_folio = '$folio' {$sql_sufijo}";
        if(!$res = mysqli_query($conexion, $sql)) echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

        echo true;
        break;
        case 'cerrar':
 
/*
                $sql = 'UPDATE th_pedido SET Num_cajas = "'.$_POST['cajas'].'", status = "C" WHERE Fol_folio = "'.$_POST['folio'].'"';

                executeSQL($sql);
*/
                $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                $folio = $_POST['folio'];
                $sufijo = $_POST['sufijo'];
                $id_caja = $_POST['cajas'];
                $almacen = $_POST['almacen'];
                $pallet_abierto = $_POST['pallet_abierto'];
                $cod_articulos_empacados = $_POST['cod_articulos_empacados'];
                $val_articulos_empacados = $_POST['val_articulos_empacados'];
                $zona_embarque = $_POST['zona_embarque'];
                $pallet = $_POST['pallet'];
                $total_pendientes = $_POST['total_pendientes'];
                $terminar_auditoria_user = $_POST['terminar_auditoria_user'];

                //if($pallet == "")
                if($sufijo == 0) $sufijo = 1;
                $sql01 = ""; $sql02 = ""; $sql03 = ""; $sql04 = ""; $sql05 = ""; $sql06 = ""; $art_empac = ""; $sqlGuardar = "";

                //*****************************************************************************************
                //                               GENERAR GUÍA DE LA CAJA
                //*****************************************************************************************
                $sql = "SELECT IFNULL((MAX(cm2.Cve_CajaMix) + 1), 1) AS id_cajamix FROM th_cajamixta cm2";
                if(!$result_guia = mysqli_query($conexion, $sql)) 
                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                $row_guia = mysqli_fetch_assoc($result_guia);
                $id_cajamix = $row_guia['id_cajamix'];
                $guia_caja = $folio.str_pad($id_cajamix, 6, "0", STR_PAD_LEFT);
                //*****************************************************************************************

                if($id_caja != "")
                {
                    $sqlGuardar = "INSERT IGNORE INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja, abierta, embarcada, Guia) VALUES ($id_cajamix, '$folio', $sufijo, (SELECT (IFNULL(MAX(cm2.NCaja), 0)+1) FROM th_cajamixta cm2 WHERE cm2.fol_folio = '$folio' AND cm2.Sufijo = '$sufijo'), 1, $id_caja, 'N', 'S', '$guia_caja')";
                    //ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '{$caja['id_tipocaja']}';";
                    $sql01 = $sqlGuardar;
                    executeSQL($sqlGuardar);
                }

                $result = "";
                $completo = false;
                $caja_vacia = false;
                $sql_sufijo = "";
                if($sufijo)
                  $sql_sufijo = " AND Sufijo = $sufijo ";
                $sql ="SELECT * FROM td_surtidopiezas WHERE fol_folio = '$folio' {$sql_sufijo} AND Cve_articulo NOT IN (SELECT cve_articulo FROM t_tarima WHERE Fol_Folio = '$folio' {$sql_sufijo} AND cantidad = td_surtidopiezas.Cantidad)";

                $sql06 = $sql;
                if(!$result = mysqli_query($conexion, $sql)) 
                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                while($row = mysqli_fetch_assoc($result))
                {
                    $cve_articulo = $row['Cve_articulo'];
                    $cantidad = $row['Cantidad'];
                    $cve_lote = $row['LOTE'];
                    if(!$cve_lote) $cve_lote = "";

                    $registrar = false;
                    $completo = true;

                    for($i = 0; $i < count($val_articulos_empacados); $i++)
                    {
                        $art_empac .= $val_articulos_empacados[$i]." - ";
                        if($val_articulos_empacados[$i] == 0 || $val_articulos_empacados[$i] == 3 /*|| $zona_embarque == '000X'*/)
                        {
                           $completo = false;
                           //break;
                        }
                    }

                    for($i = 0; $i < count($cod_articulos_empacados); $i++)
                    {
                        if($cod_articulos_empacados[$i] == $cve_articulo && $val_articulos_empacados[$i] > 0)
                        {
                            $registrar = true;
                            //break;
                        }

                    }
                    if($registrar)
                    {
                      $sqlGuardar = "";

                      $sql = "SELECT ts.Num_Empacados AS Num_Empacados 
                              FROM td_pedido tp, td_surtidopiezas ts 
                              WHERE tp.Fol_folio = ts.fol_folio AND tp.Cve_articulo = ts.Cve_articulo AND 
                               tp.Fol_folio = '".$folio."' AND tp.Cve_articulo = '".$cve_articulo."' AND IFNULL(ts.LOTE, '') = '".$cve_lote."'";
                        if(!$result_emp = mysqli_query($conexion, $sql)) 
                            echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                        $sql02 = $sql;
                        $row_emp = mysqli_fetch_assoc($result_emp);

                        $Num_Empacados = $row_emp['Num_Empacados'];

                      $registro_caja = false;

                      $sql = "SELECT tipo_caja, num_multiplo FROM c_articulo WHERE cve_articulo = '".$cve_articulo."'";
                      if(!$result_art = mysqli_query($conexion, $sql)) 
                          echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                      $sql02 = $sql;
                      $row_art = mysqli_fetch_assoc($result_art);
                      $caja_id = $row_art['tipo_caja'];
                      $piezasxcaja = $row_art['num_multiplo'];
                      if($piezasxcaja == 0 || $piezasxcaja == "") $piezasxcaja = 1;

                      if($id_caja == "" || $id_caja == $caja_id)
                      {
                        $empacados_origen = 0;
                        $cajas_a_generar = ceil($Num_Empacados/$piezasxcaja);

                        if($pallet)
                        {
                        $sql ="SELECT IFNULL(CveLP, '') as CveLP FROM c_charolas WHERE IDContenedor = $pallet";
                        if(!$result_clave_con = mysqli_query($conexion, $sql)) 
                            echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                        $row_clave_con = mysqli_fetch_assoc($result_clave_con);
                        $CveLP = $row_clave_con['CveLP'];
                        }

                        if($CveLP == "" && $pallet)
                        {
                            $label_lp = "LP".str_pad($pallet, 6, "0", STR_PAD_LEFT).$folio;

                            $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = $pallet";
                            executeSQL($sqlGuardar);
                            $CveLP = $label_lp;
                        }

                        for($i_thcaja = 0; $i_thcaja < $cajas_a_generar; $i_thcaja++)
                        {
                            //if($id_caja == "" || $id_caja == $caja_id)
                            //{
                                //if($id_caja == "")
                                //{

                                  //*****************************************************************************************
                                  //                               GENERAR GUÍA DE LA CAJA
                                  //*****************************************************************************************
                                  $sql = "SELECT IFNULL((MAX(cm2.Cve_CajaMix) + 1), 1) AS id_cajamix FROM th_cajamixta cm2";
                                  if(!$result_guia = mysqli_query($conexion, $sql)) 
                                      echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                  $row_guia = mysqli_fetch_assoc($result_guia);
                                  $id_cajamix = $row_guia['id_cajamix'];
                                  $guia_caja = $folio.str_pad($id_cajamix, 6, "0", STR_PAD_LEFT);
                                  //*****************************************************************************************

                                  $id_caja = $caja_id;
                                  $sqlGuardar = "INSERT IGNORE INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja, abierta, embarcada, Guia) VALUES ($id_cajamix, '$folio', $sufijo, (SELECT (IFNULL(MAX(cm2.NCaja), 0)+1) FROM th_cajamixta cm2 WHERE cm2.fol_folio = '$folio' AND cm2.Sufijo = '$sufijo'), 1, $id_caja, 'N', 'S', '$guia_caja')";
                                  executeSQL($sqlGuardar);
                                //}

                                  $caja_vacia = true;
                            //}

                            if($id_caja != "" && $Num_Empacados)
                            {
                                //$sqlGuardar = "INSERT INTO  td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) VALUES ((SELECT MAX(cm2.Cve_CajaMix) FROM th_cajamixta cm2), '$cve_articulo', $cantidad, '$cve_lote', $Num_Empacados);";

                              //for($i_tdcaja = 0; $i_tdcaja < $Num_Empacados; $i_tdcaja++)
                              for($i_tdcaja = 0; $i_tdcaja < $piezasxcaja; $i_tdcaja++) 
                              {
                                $sqlGuardar = "INSERT INTO td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) VALUES ((SELECT MAX(cm2.Cve_CajaMix) FROM th_cajamixta cm2), '$cve_articulo', 1, '$cve_lote', 1);";
                                $sql03 = $sqlGuardar;
                                executeSQL($sqlGuardar);

                                $empacados_origen++;
                                if($empacados_origen == $Num_Empacados) break;
                              }
                                $registro_caja = true;
                                if($caja_vacia)
                                  $id_caja = "";
                            }
//*******************************************************************************************************
//*******************************************************************************************************
                              $caja_ref = 0;
                              if($registro_caja) 
                              {
                                  $sql ="SELECT MAX(cm2.Cve_CajaMix) as id FROM th_cajamixta cm2";
                                  if(!$result_pallet = mysqli_query($conexion, $sql)) 
                                      echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                  $sql04 = $sql;
                                  $row_pallet = mysqli_fetch_assoc($result_pallet);

                                $caja_ref = $row_pallet['id'];
                              }
                              //$sqlGuardar = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Abierta) VALUES($pallet, '$folio', $sufijo, '$cve_articulo', '$cve_lote', $cantidad, $Num_Empacados, $caja_ref, 1);";
                              if($Num_Empacados)
                              {
                                //**********************************************************************
                                // REVISAR SI UNA TARIMA ES GENÉRICA O NO
                                /**********************************************************************
                                  $sql ="SELECT Permanente FROM c_charolas where IDContenedor = $pallet";
                                  if(!$result_permanente = mysqli_query($conexion, $sql)) 
                                      echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                  $sql04 = $sql;
                                  $row_permanente = mysqli_fetch_assoc($result_permanente);
                                  $permanente = $row_permanente['Permanente'];

                                  $abierto = 0;
                                  if($permanente == 0)
                                    $abierto = 1;
                                //**********************************************************************/


                                $sql ="SELECT num_multiplo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                                if(!$result_num_mult = mysqli_query($conexion, $sql)) 
                                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                $row_num_mult = mysqli_fetch_assoc($result_num_mult);
                                $num_multiplo = $row_num_mult['num_multiplo'];

                                $sqlGuardar = "INSERT IGNORE INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, Activo, PzsXCaja, Abierto) VALUES((SELECT (MAX(tar.fol_folio)+1) FROM td_entalmacenxtarima tar), '$cve_articulo', '$cve_lote', '$CveLP', $Num_Empacados, 'N', 1, $num_multiplo, $pallet_abierto);";
                                $sqlPallet = $sqlGuardar;
                                executeSQL($sqlGuardar);


                                $sqlGuardar = "INSERT IGNORE INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Abierta) VALUES($pallet, '$folio', $sufijo, '$cve_articulo', '$cve_lote', $num_multiplo, $num_multiplo, $caja_ref, $pallet_abierto);";
                                //$sqlGuardar = "INSERT IGNORE INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Abierta) VALUES($pallet, '$folio', $sufijo, '$cve_articulo', '$cve_lote', $Num_Empacados, $Num_Empacados, $caja_ref, $pallet_abierto);";
                                $sql05 = $sqlGuardar;
                                executeSQL($sqlGuardar);
                              }

//*******************************************************************************************************
//*******************************************************************************************************

                        }
                      }
                      else
                      {
                          if($id_caja != "" && $Num_Empacados)
                          {
                              //$sqlGuardar = "INSERT INTO td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) VALUES ((SELECT MAX(cm2.Cve_CajaMix) FROM th_cajamixta cm2), '$cve_articulo', $cantidad, '$cve_lote', $Num_Empacados);";
                              $sqlGuardar = "INSERT IGNORE INTO td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) VALUES ((SELECT MAX(cm2.Cve_CajaMix) FROM th_cajamixta cm2), '$cve_articulo', $Num_Empacados, '$cve_lote', $Num_Empacados);";
                              $sql03 = $sqlGuardar;
                              executeSQL($sqlGuardar);
                              $registro_caja = true;

                              if($caja_vacia)
                                $id_caja = "";
                          }

                          $sqlPallet = "";
                          if($pallet != "")
                          {
                              $caja_ref = 0;
                              if($registro_caja) 
                              {
                                  $sql ="SELECT MAX(cm2.Cve_CajaMix) as id FROM th_cajamixta cm2";
                                  if(!$result_pallet = mysqli_query($conexion, $sql)) 
                                      echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                  $sql04 = $sql;
                                  $row_pallet = mysqli_fetch_assoc($result_pallet);

                                $caja_ref = $row_pallet['id'];
                              }
                              //$sqlGuardar = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Abierta) VALUES($pallet, '$folio', $sufijo, '$cve_articulo', '$cve_lote', $cantidad, $Num_Empacados, $caja_ref, 1);";
                              if($Num_Empacados)
                              {
                                //**********************************************************************
                                // REVISAR SI UNA TARIMA ES GENÉRICA O NO
                                /**********************************************************************
                                  $sql ="SELECT Permanente FROM c_charolas where IDContenedor = $pallet";
                                  if(!$result_permanente = mysqli_query($conexion, $sql)) 
                                      echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                  $sql04 = $sql;
                                  $row_permanente = mysqli_fetch_assoc($result_permanente);
                                  $permanente = $row_permanente['Permanente'];

                                  $abierto = 0;
                                  if($permanente == 0)
                                    $abierto = 1;
                                //**********************************************************************/

                                $sql ="SELECT IFNULL(CveLP, '') as CveLP FROM c_charolas WHERE IDContenedor = $pallet";
                                if(!$result_clave_con = mysqli_query($conexion, $sql)) 
                                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                $row_clave_con = mysqli_fetch_assoc($result_clave_con);
                                $CveLP = $row_clave_con['CveLP'];

                                if($CveLP == "")
                                {
                                    $label_lp = "LP".str_pad($pallet, 6, "0", STR_PAD_LEFT).$folio;

                                    $sqlGuardar = "UPDATE c_charolas SET CveLP = '$label_lp' WHERE IDContenedor = $pallet";
                                    executeSQL($sqlGuardar);
                                    $CveLP = $label_lp;
                                }

                                $sql ="SELECT num_multiplo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                                if(!$result_num_mult = mysqli_query($conexion, $sql)) 
                                    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                                $row_num_mult = mysqli_fetch_assoc($result_num_mult);
                                $num_multiplo = $row_num_mult['num_multiplo'];

                                $sqlGuardar = "INSERT IGNORE INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, Activo, PzsXCaja, Abierto) VALUES((SELECT (MAX(tar.fol_folio)+1) FROM td_entalmacenxtarima tar), '$cve_articulo', '$cve_lote', '$CveLP', $Num_Empacados, 'N', 1, $num_multiplo, $pallet_abierto);";
                                $sqlPallet = $sqlGuardar;
                                executeSQL($sqlGuardar);


                                $sqlGuardar = "INSERT IGNORE INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Abierta) VALUES($pallet, '$folio', $sufijo, '$cve_articulo', '$cve_lote', $Num_Empacados, $Num_Empacados, $caja_ref, $pallet_abierto);";
                                $sql05 = $sqlGuardar;
                                executeSQL($sqlGuardar);
                              }


                          }

                      }

                    }
                }

                $sql_cerrar_pallet01 = "";$sql_cerrar_pallet02 = "";$sql_cerrar_pallet03 = "";
                $sql_cerrar_pallet04 = "";$sql_cerrar_pallet05 = "";

                if($pallet_abierto == 0 && $pallet != '')
                {
                    //$sql ="SELECT TipoGen, CveLP FROM c_charolas where IDContenedor = $pallet";
                    //if(!$result_gen = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                    //$row_gen = mysqli_fetch_assoc($result_gen);
                    //$generico = $row_gen['TipoGen'];
                    //$CveLP = $row_gen['CveLP'];

                    //if($generico == 1)
                    //{
                        $sql = "SELECT * FROM c_charolas WHERE IDContenedor = $pallet";
                        $sql_cerrar_pallet01 = $sql;
                        if(!$res_pallet=mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        $row_pallet = mysqli_fetch_assoc($res_pallet);

                        $cve_almac        = $row_pallet['cve_almac'];
                        $clave_contenedor = $row_pallet['clave_contenedor'];
                        $descripcion      = utf8_encode($row_pallet['descripcion']);
                        $tipo             = $row_pallet['tipo'];
                        $Activo           = $row_pallet['Activo'];
                        $alto             = $row_pallet['alto'];
                        $ancho            = $row_pallet['ancho'];
                        $fondo            = $row_pallet['fondo'];
                        $peso             = $row_pallet['peso'];
                        $pesomax          = $row_pallet['pesomax'];
                        $capavol          = $row_pallet['capavol'];
                        $CveLP            = $row_pallet['CveLP'];
                        $TipoGen          = $row_pallet['TipoGen'];

                        $sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES
                               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";
                        $sql_cerrar_pallet02 = $sql;
                        if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        $row_autoid = mysqli_fetch_assoc($res_id);
                        $nextid = $row_autoid['id'];

                        $label_lp = "LP".str_pad($nextid, 6, "0", STR_PAD_LEFT).$folio;

                        if($TipoGen == 0) 
                        $clave_contenedor = $label_lp;
                        else
                          $clave_contenedor .= "-".$nextid;

                        $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$cve_almac', '$clave_contenedor', '$descripcion', 0, '$tipo', '$Activo', '$alto', '$ancho', '$fondo', '$peso', '$pesomax', '$capavol', '$label_lp', $TipoGen)";
                        $sql_cerrar_pallet03 = $sqlGuardar;
                        executeSQL($sqlGuardar);
                    //}
                    $sql_sufijo = "";
                    if($sufijo)
                      $sql_sufijo = " AND Sufijo = $sufijo ";

                    $sql = 'UPDATE t_tarima SET Abierta = 0 WHERE Fol_Folio = "'.$_POST['folio'].'" {$sql_sufijo} " AND ntarima = '.$pallet.'';
                    $sql_cerrar_pallet04 = $sql;
                    executeSQL($sql);
                    $sql = 'UPDATE td_entalmacenxtarima SET Abierto = 0 WHERE ClaveEtiqueta = "'.$CveLP.'"';
                    $sql_cerrar_pallet05 = $sql;
                    executeSQL($sql);
                }

                $sql1 = ""; $sql2 = ""; $sql3 = ""; $sql4 = ""; $sql5 = ""; $sql6 = ""; $sql7 = "";

                  $sql = "SELECT ID_URevision, descripcion FROM t_ubicaciones_revision WHERE Activo = 1 and fol_folio = '".$_POST['folio']."' {$sql_sufijo} ";
                  $sql1 = $sql;

                  $validate = getArraySQL($sql);

                  //***************************************************************************************
                  // de esta manera mantengo la mesa de revisión al cerrar la auditoría del presente pedido
                  //***************************************************************************************
                  $_SESSION['IDarea'] = $validate[0]["ID_URevision"];
                  //*************************************

                if($completo || $total_pendientes <= 0 || $terminar_auditoria_user == 1)
                {

                    if((!empty($validate) && is_array($validate)) || $total_pendientes <= 0 || $terminar_auditoria_user == 1){

                        $IDarea = $validate[0]["ID_URevision"];

                        $sql = 'UPDATE th_pedido SET status="C" WHERE Fol_folio = "'.$_POST['folio'].'"';
                        $sql2 = $sql;
                        executeSQL($sql);

                        $sql_sufijo = "";
                        if($sufijo)
                          $sql_sufijo = " AND Sufijo = $sufijo ";

                        $sql = "UPDATE th_subpedido SET status='C', HFE=NOW(), HFR = NOW() WHERE Fol_folio = '".$_POST['folio']."' {$sql_sufijo} ";
                        $sql3 = $sql;
                        executeSQL($sql);

                        $sql = 'UPDATE t_ubicaciones_revision SET fol_folio = "", sufijo = 0 WHERE ID_URevision = "'.$IDarea.'"';
                        $sql4 = $sql;

                        executeSQL($sql);

                        $result = "";
                        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                        $sql = "SELECT id FROM c_almacenp WHERE clave = '$almacen'";
                        $sql5 = $sql;
                        $clave_almacen_sp = $almacen;
                        if(!$result = mysqli_query($conexion, $sql)) 
                            echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
                        $row = mysqli_fetch_assoc($result);
                        $almacen = $row['id'];
                        $sql = 'INSERT INTO rel_uembarquepedido(cve_ubicacion, fol_folio, Sufijo, cve_almac, Activo) VALUES("'.$zona_embarque.'", "'.$folio.'", '.$sufijo.', '.$almacen.', 1)';
                        $sql6 = $sql;

                        executeSQL($sql);

                        $sql = 'UPDATE td_pedido SET Num_Empacados = 0 WHERE Fol_folio = "'.$_POST['folio'].'"';
                        $sql7 = $sql;
                        executeSQL($sql);

                        $sql_sufijo = "";
                        if($sufijo)
                          $sql_sufijo = " AND Sufijo = $sufijo ";

                        $sql = "UPDATE td_surtidopiezas SET empacados = 0 WHERE fol_folio = '".$_POST['folio']."' {$sql_sufijo} ";
                        executeSQL($sql);

                        $sql = 'UPDATE t_tarima SET Abierta = 0 WHERE Fol_Folio = "'.$_POST['folio'].'" AND Sufijo = "'.$sufijo.'"';
                        executeSQL($sql);

                        $sql = 'UPDATE td_entalmacenxtarima SET Abierto = 0 WHERE ClaveEtiqueta IN (SELECT CveLP FROM c_charolas WHERE IDContenedor IN (SELECT ntarima FROM t_tarima WHERE Fol_Folio = "'.$_POST['folio'].'" AND Sufijo = "'.$sufijo.'"))';
                        executeSQL($sql);





                        //***********************************************************************
                        // PROCESO PARA ENVIAR A EMBARQUES EL PEDIDO RELACIONADO DE UNA OT
                        // O PARA ENVIAR A ACOMODO UNA OT SIN PEDIDO RELACIONADO
                        //***********************************************************************
                        $sql ="SELECT COUNT(*) AS es_ot FROM t_ordenprod WHERE Folio_Pro = SUBSTR('$folio', 4)";
                        if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        $row_cerrar_ot = mysqli_fetch_assoc($res_id);
                        $es_ot = $row_cerrar_ot['es_ot'];

                        if($es_ot)
                        {
                            $sql ="UPDATE th_pedido SET status = 'T' WHERE Fol_folio = '$folio'";
                            if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";

                            $sql ="UPDATE th_subpedido SET status = 'T' WHERE Fol_folio = '$folio'";
                            if(!$res_id = mysqli_query($conexion, $sql)) echo "Falló la preparación: (".mysqli_error($conexion).")";
                        }

                        //***********************************************************************
                        //***********************************************************************
                    }
                }

                        //***********************************************************************
                        // PROCESO PARA REVISAR EL ETIQUETADO PDF DE UNA EMPRESA PREFERENCIAL
                        //***********************************************************************
                        $abrir_logistica = false;
                        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                        $sql = "SELECT id FROM c_almacenp WHERE clave = '$almacen'";
                        $sql5 = $sql;
                        $clave_almacen_sp = $almacen;

                        $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
                        if (!($res_instancia = mysqli_query($conexion, $sql_instancia))) echo "Falló la preparación Charset: (" . mysqli_error($conexion) . ") ";
                        $instancia = mysqli_fetch_array($res_instancia)['instancia'];

                        $sql = "SELECT COUNT(*) AS existe FROM t_configuraciongeneral WHERE cve_conf = 'generar_cajas_vacias_packing' AND Valor = '1'";

                        if (!($res_cajas = mysqli_query($conexion, $sql))){
                            echo "Falló la preparación(24): (" . mysqli_error($conexion) . $sql. ") ";
                        }
                        $row_packing = mysqli_fetch_array($res_cajas);
                        $existe_pack = $row_packing['existe'];

                        $sql = "SELECT Guia FROM th_cajamixta WHERE fol_folio = '$folio' AND NCaja = 1";

                        if (!($res = mysqli_query($conexion, $sql))){
                            echo "Falló la preparación(24): (" . mysqli_error($conexion) . $sql. ") ";
                        }
                        $row_packing = mysqli_fetch_array($res);
                        $Guia = $row_packing['Guia'];

                          $ncajas = 1;
                          if(isset($_POST['ncajas']))
                          {
                             $ncajas = $_POST['ncajas'];

                              if($existe_pack)
                              {
                                  for($nc = 1; $nc <= ($ncajas-1); $nc++)
                                  {
                                      $sql = "INSERT IGNORE INTO th_cajamixta (Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, cve_tipocaja, Guia) VALUES ((SELECT (MAX(t.Cve_CajaMix)+1) FROM th_cajamixta t), '{$folio}', 1, ($nc+1), 'N', 'S', 1, '$Guia')";

                                      if (!($res = mysqli_query($conexion, $sql))){
                                          echo "Falló la preparación(24): (" . mysqli_error($conexion) . $sql. ") ";
                                      }

                                  }
                              }
                          }


                        if($instancia == 'asl')
                        {
                            $sql_sp = "Call SPWS_ValidaZonaCtePaqueteria('$folio')";
                            if (!($res_sp = mysqli_query($conexion, $sql_sp))) echo "Falló la preparación SP: (" . mysqli_error($conexion) . ") ";
                            $row_sp = mysqli_fetch_array($res_sp);
                            $id_paqueteria      = $row_sp['Id_Paqueteria'];
                            $operador_logistico = $row_sp['Operador_Logistico'];

                            if($id_paqueteria == 1 && $operador_logistico == 'Exxe Logística')
                            {
                              $abrir_logistica = true;
                            }
                        }

                        //***********************************************************************
                        //***********************************************************************

                      $sql = 'UPDATE td_pedido SET Num_Empacados = 0 WHERE Fol_folio = "'.$_POST['folio'].'"';
                      $sql7 = $sql;
                      executeSQL($sql);

                      $sql_sufijo = "";
                      if($sufijo)
                        $sql_sufijo = " AND Sufijo = $sufijo ";

                      $sql = "UPDATE td_surtidopiezas SET Num_Empacados = 0 WHERE fol_folio = '".$_POST['folio']."' {$sql_sufijo} ";
                      executeSQL($sql);

                $array = [
                    "clave_almacen_sp" => $clave_almacen_sp,
                    "abrir_logistica" => $abrir_logistica,
                    "sql01" => $sql01,  
                    "sql06" => $sql06, 
                    "sql02" => $sql02,  
                    "sql03" => $sql03,  
                    "sql04" => $sql04,  
                    "sql05" => $sql05, 
                    "art_empac" => $art_empac,
                    "sql1" => $sql1,  
                    "sql2" => $sql2,  
                    "sql3" => $sql3,  
                    "sql4" => $sql4,  
                    "sqlPallet" => $sqlPallet,
                    "sql5" => $sql5,  
                    "sql6" => $sql6,  
                    "sql7" => $sql7,
                    "sql_cerrar_pallet01" => $sql_cerrar_pallet01,
                    "sql_cerrar_pallet02" => $sql_cerrar_pallet02,
                    "sql_cerrar_pallet03" => $sql_cerrar_pallet03,
                    "sql_cerrar_pallet04" => $sql_cerrar_pallet04,
                    "sql_cerrar_pallet05" => $sql_cerrar_pallet05,
                    "cerrar"=>$completo,
                    "id_cajamix"=>$id_cajamix,
                    "mesa" => $_SESSION['IDarea'],
                    "zona_embarque"=>$zona_embarque
                ];

                echo json_encode($array);

                // R = AUDITANDO.
                // L = PENDIENTE DE AUDITAR.
                // C = CERRADO.

            break;
        case 'ImprimirEtiquetas':

        $folio  = $_POST['folio'];
        $clave_almacen_sp = $_POST['clave_almacen_sp'];
        $fichero = "";
        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


        //***********************************************************************
        // PROCESO PARA ADAPTAR EL ETIQUETADO PDF DE UNA EMPRESA PREFERENCIAL
        //***********************************************************************
            $track = "";
              $sql = "SELECT Url, Empresa, NOW() AS hora_movimiento FROM c_datos_sap WHERE Empresa = 1 AND Activo = 1";
              $query = mysqli_query($conexion, $sql);
              $row_infinity = mysqli_fetch_array($query);
              $Url_inf = $row_infinity['Url'];
              $Empresa_inf = $row_infinity['Empresa'];
              $mensaje_error = "";
              $json = "[";

                $json .= "{";
                $json .= '"function":"Genera_Guia",';
                $json .= '"Almacen":"'.$clave_almacen_sp.'",';
                $json .= '"Folio":"'.$folio.'"';
                $json .= "}";
              //$json[strlen($json)-1] = ' ';
              $json .= "]";

                  $curl = curl_init();
                  $url_curl = $Url_inf;

                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "{$url_curl}",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json'
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);

                  curl_close($curl);
                  //echo $response;
                  $data = json_decode($response, true);

                  $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '$json', '$response', 'QA', '$folio', 'Genera_Guia')";
                  $query = mysqli_query($conexion, $sql);

                  $track .= "\ndata = ".$data["Error"]."\n\n";

                  if($data["Error"] == "1")
                  {

                    $track .= "\nEntro Error = 1\n\n";

                      $json = "[";

                        $json .= "{";
                        $json .= '"function":"GeneraEtiqueta",';
                        $json .= '"Folio":"'.$folio.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";

                          $curl = curl_init();
                          $url_curl = $Url_inf;

                          curl_setopt_array($curl, array(
                            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                            CURLOPT_URL => "{$url_curl}",
                            //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS =>
                            // Aquí cambia la cadena JSON
                            $json,
                            CURLOPT_HTTPHEADER => array(
                              'Content-Type: application/json'
                            )
                            ,CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_SSL_VERIFYPEER => false
                          ));

                          $response = curl_exec($curl);

                          curl_close($curl);
                          //echo $response;
                          $data = json_decode($response, true);

                          $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '$json', '$response', 'QA', '$folio', 'GeneraEtiqueta')";
                          $query = mysqli_query($conexion, $sql);

                          $track .= "\ndata2 = ".$data["Error"]."\n\n";
                          $track .= "\nsql = ".$sql."\n\n";

                          if($data["Error"] == "1")
                          {
                              $sql = "SELECT Etiqueta FROM th_cajaetiqueta WHERE Fol_Folio='" . $folio . "';";
                              $query = mysqli_query($conexion, $sql);
                              $row_etiq = mysqli_fetch_assoc($query);
                              $etiqueta = $row_etiq['Etiqueta'];

                              $bin = base64_decode($etiqueta, true);
                              if (strpos($bin, '%PDF') !== 0) {
                                echo('Missing the PDF file signature');
                                $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '$json', 'Missing the PDF file signature', 'QA', '$folio', 'GenerarPDF')";
                                $query = mysqli_query($conexion, $sql);
                              }
                              else
                              {
                                //file_put_contents('https://'.$_SERVER['HTTP_HOST'].'/api/qaauditoria/PDF_Etiquetas/Etiqueta_'.$folio.'.pdf', $bin);
                                //file_put_contents('Etiqueta_'.$folio.'.pdf', $bin);
                                $fichero = 'Etiqueta_'.$folio.'.pdf';
                                file_put_contents($fichero, $bin);
                                //header('PDF_Etiquetas/Etiqueta_'.$folio.'.pdf');
                              }
                          }
                          else 
                            $mensaje_error = $data["Msg"];
                  }
                  else 
                    $mensaje_error = $data["Msg"];

        //***********************************************************************
        //***********************************************************************

                $array = [
                    "archivo" => $fichero,
                    "mensaje_error" => $mensaje_error,
                    "track" => $track
                ];

                echo json_encode($array);
        break;


    }

}

function getArraySQL($sql)
{
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") SQL = ".$sql;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}

function executeSQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    $result = mysqli_query($conexion, $sql);

    if($result) {
        $res = "success";
    }
    else{
        $res = "Error: " . $sql . "<br>" . mysqli_error($conexion);
    }

    $array = ["res" => $res];

    return $array;

    disconnectDB($conexion);
}