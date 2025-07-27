<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Ubicaciones\Ubicaciones();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarUbicacion($_POST);
} if( $_POST['action'] == 'exists' ) {
    $ga->idy_ubica = $_POST["codigo"];
    $ga->__get("idy_ubica");

    $success = false;

    if (!empty($ga->data->idy_ubica)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'delete' ) {
    $ga->borrarUbicacion($_POST);
    $ga->Cve_Clte = $_POST["idy_ubica"];
    $ga->__get("idy_ubica");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->idy_ubica = $_POST["codigo"];
    $ga->__get("idy_ubica");
    $arr = array(
        "success" => true,
    );

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}

if( $_POST['action'] == 'ubicacionNoVacias' ) 
{
  //$data= $ga->getUbicacionesNoVaciasxZona($_POST["zona"], $_POST['excludeInventario']);
  $data= $ga->getUbicacionesNoVaciasxZona($_POST["almacen"], $_POST['excludeInventario'], $_POST['traslado']);
  $arr = array(
    "success" => true,
    "ubicaciones"=>$data  
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'ubicacionNoLlenas-acomodo' ) 
{
  //$_POST["zona"], $_POST['excludeInventario'], $_POST['ubicacion_origen'], $_POST['tipo_pca'], $_POST['descripcion_pca']
  $data= $ga->getUbicacionesNoLlenasxZona($_POST);

  //$data= $ga->getUbicacionesNoLlenasxZona($_POST["almacen"], $_POST['excludeInventario'], $_POST['ubicacion_origen'], $_POST['tipo_pca'], $_POST['descripcion_pca']);
  $arr = array(
    "success" => true,
    "ubicaciones"=>$data
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'ubicacionNoLlenas' ) 
{
  //$data= $ga->getUbicacionesNoLlenasxZona($_POST["zona"], $_POST['excludeInventario'], $_POST['ubicacion_origen'], $_POST['tipo_pca'], $_POST['descripcion_pca']);
  $data= $ga->getUbicacionesNoLlenasxAlmacen($_POST["almacen"], $_POST['excludeInventario'], $_POST['ubicacion_origen'], $_POST['tipo_pca'], $_POST['descripcion_pca']);
  $arr = array(
    "success" => true,
    "ubicaciones"=>$data
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'ubicacionesVacias' ) 
{
  //$data= $ga->getUbicacionesVaciasxZona($_POST["zona"], $_POST['excludeInventario'], $_POST['ubicacion_origen']);
  $data= $ga->getUbicacionesVaciasxZona($_POST["almacen"], $_POST['excludeInventario'], $_POST['ubicacion_origen']);
  $arr = array(
    "success" => true,
    "ubicaciones"=>$data
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'articulosenUbicacion' ) 
{
  $data = $ga->getArticulosdeUbicacion($_POST["idy_ubica"]);
  $capacidad = $ga->getCapacidadDeUbicacion($_POST["idy_ubica"]);
  $zona = $ga->getZonaUbicacion($_POST["idy_ubica"]);
  $arr = array(
    "success" => true,
    "articulos"=>$data,
    "capacidad"=>$capacidad,
    "zona"=>$zona
  );

  echo json_encode($arr);
}

if( $_POST['action'] == 'getArticulosPendientesAcomodo' ) 
{
  if($_POST['tipo'] === 'area')
  {
    $data= $ga->getArticulosRecepcion($_POST["cve_ubicacion"], $_POST["filtro_buscador"], $_POST["filtro_lp"], $_POST["filtro_clave"], $_POST["filtro_lote_serie"]);
  }
  else
  {
    $data= $ga->getArticulosPendientesAcomodo($_POST["cve_ubicacion"]);
  }
  $arr = array(
    "success" => true,
    "articulos"=>$data
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'getArticulosAlmacen' ) {
   $data= $ga->getArticulosdeAlmacen($_POST["cve_almac"]);

    $arr = array(
        "success" => true,
        "articulos"=>$data
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'getArticulosYZonasAlmacenConExistencia' ) {
   $data= $ga->getArticulosYZonasAlmacenConExistencia($_POST["cve_almac"]);

    echo json_encode($data);

}

if( $_POST['action'] == 'getArticulosYZonasAlmacenConSinExistencia' ) {
   $data= $ga->getArticulosYZonasAlmacenConSinExistencia($_POST["cve_almac"], $_POST['clave_almacen'], $_POST["check_almacen"]);

    echo json_encode($data);

}

if( $_POST['action'] == 'getCapacidadUbicacion' ) {
   $data= $ga->getCapacidadDeUbicacion($_POST["idy_ubica"]);



    echo json_encode($data);

}


if( $_POST['action'] == 'acomodo' ) 
{

  if($_POST['enviar_folios_entrada'] == 1)
  {
    $f  = $_POST['folio'];
    $zr = $_POST['zonarecepcioni'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT * FROM (
SELECT DISTINCT
          V_EntradasContenedores.Fol_Folio AS folio,
          #IF(V_EntradasContenedores.Clave_Contenedor != '', 'Contenedor','Articulo') AS tipo,
          IFNULL(V_EntradasContenedores.Clave_Contenedor, 'Articulo') AS tipo,
          IFNULL(c_articulo.control_abc, '') AS control_abc,
          IFNULL(IF(c_articulo.control_lotes = 'S' AND IFNULL(tde.cve_lote, '') != '', tde.cve_lote,''), '') AS lote,
          IF(c_articulo.control_lotes = 'S' AND c_articulo.Caduca = 'S' AND IFNULL(tde.cve_lote, '') != '', IFNULL(IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = '00-00-0000', '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), ''), '') AS caducidad,          IF(c_articulo.control_numero_series = 'S', V_EntradasContenedores.Cve_lote,'') AS numero_serie,
          #IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000) * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          tde.cve_articulo AS clave,
          a.des_articulo AS descripcion,
          a.control_peso,
          IFNULL(tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0), 0) AS num_existencia,
          #IFNULL(t_pendienteacomodo.Cantidad, 0) AS num_existencia,
          /*c_articulo.des_articulo AS descripcion,*/
          /*IFNULL(V_EntradasContenedores.Clave_Contenedor,V_EntradasContenedores.Cve_Articulo) AS pallet_contenedor,*/
          IFNULL(V_EntradasContenedores.Clave_Contenedor, '') AS pallet_contenedor,
          '' AS LP,
          #IFNULL(CAST((c_articulo.peso * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,2)), 0) AS peso_total,
          IFNULL(CAST((c_articulo.peso) AS DECIMAL(10,2)), 0) AS peso_total,
          a.id AS id_articulo,
          c_proveedores.Nombre AS proveedor,
          V_EntradasContenedores.Cve_Proveedor AS  id_proveedor
          FROM V_EntradasContenedores
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_EntradasContenedores.Cve_articulo
          LEFT JOIN t_pendienteacomodo ON t_pendienteacomodo.cve_articulo = V_EntradasContenedores.Cve_articulo AND t_pendienteacomodo.cve_lote = V_EntradasContenedores.Cve_Lote AND t_pendienteacomodo.cve_ubicacion = '$zr' AND t_pendienteacomodo.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_serie ON c_serie.numero_serie = V_EntradasContenedores.Cve_Lote AND c_serie.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN td_entalmacen tde ON tde.fol_folio = V_EntradasContenedores.Fol_Folio AND tde.cve_articulo = V_EntradasContenedores.Cve_articulo AND tde.cve_lote = V_EntradasContenedores.Cve_Lote AND tde.status != 'Q' AND tde.status != 'M'
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND t_pendienteacomodo.cve_articulo = a.cve_articulo
          WHERE 0 = 0 
          AND V_EntradasContenedores.Cve_Ubicacion = '$zr'
          AND a.id <> ''
          AND V_EntradasContenedores.Cantidad_C = 0
          AND V_EntradasContenedores.Cve_Lote IN (SELECT cve_lote FROM V_ExistenciaGralProduccion WHERE tipo = 'area')

          #AND V_EntradasContenedores.CantidadUbicada >= 0 

          #AND (((SELECT SUM(cantidad) FROM td_aduana WHERE num_orden = (SELECT id_ocompra FROM th_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)) != (SELECT SUM(IFNULL(CantidadUbicada, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)) OR 
          #((SELECT SUM(IFNULL(CantidadUbicada, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)!=(SELECT SUM(IFNULL(CantidadPedida, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio) AND V_EntradasContenedores.tipo != 'OC')
          #OR CONCAT(V_EntradasContenedores.Cve_Articulo,V_EntradasContenedores.Cve_Lote) IN (SELECT CONCAT(cve_articulo,cve_lote) FROM V_ExistenciaGral WHERE tipo = 'area'))

          #AND IFNULL(t_pendienteacomodo.Cantidad, 0) > 0 
          AND IFNULL(tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0), 0) > 0
          AND IFNULL(V_EntradasContenedores.Clave_Contenedor, '') = ''
          GROUP BY folio, clave, lote, numero_serie
                UNION             
            
              SELECT DISTINCT V_EntradasContenedores.Fol_Folio AS folio,
              IFNULL(c_charolas.tipo, 'Articulo') AS tipo,
              IFNULL(a.control_abc, '') AS control_abc,
              IF(a.control_lotes = 'S', c_lotes.Lote,'') AS lote,
              IF(a.Caduca = 'S', IFNULL(IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = '00-00-0000', '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), ''), '') AS caducidad,
              IF(a.control_numero_series = 'S', V_EntradasContenedores.Cve_lote,'') AS numero_serie,
              SUM(IFNULL(CAST(((a.alto/1000)*(a.ancho/1000)*(a.fondo/1000)*V_EntradasContenedores.Cantidad_C) AS DECIMAL(10,6)),0)) AS volumen_ocupado,
              V_EntradasContenedores.Cve_Articulo AS clave,
              a.des_articulo AS descripcion,
              a.control_peso,
              V_EntradasContenedores.Cantidad_C AS num_existencia,
              #V_EntradasContenedores.CantidadRecibida AS num_existencia,
              #V_EntradasContenedores.Clave_Contenedor AS pallet_contenedor,
              #c_charolas.CveLP AS LP,
              IFNULL(c_charolas.clave_contenedor, '') AS pallet_contenedor,
              IFNULL(c_charolas.CveLP, '') AS LP,
              SUM(IFNULL(CAST((a.peso*V_EntradasContenedores.Cantidad_C) AS DECIMAL(10,2)),0)) AS peso_total,
              V_EntradasContenedores.Clave_Contenedor AS id_articulo,
              c_proveedores.Nombre AS proveedor,
              V_EntradasContenedores.Cve_Proveedor AS id_proveedor
              FROM V_EntradasContenedores JOIN c_articulo a ON a.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
              #LEFT JOIN c_charolas ON c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor
              #LEFT JOIN c_charolas ON c_charolas.CveLP = V_EntradasContenedores.Clave_Contenedor
              LEFT JOIN c_charolas ON V_EntradasContenedores.Clave_Contenedor = IFNULL(c_charolas.clave_contenedor, c_charolas.CveLP)
              LEFT JOIN ts_existenciatarima tr ON tr.Fol_Folio = V_EntradasContenedores.Fol_Folio AND tr.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tr.lote = V_EntradasContenedores.Cve_Lote
              LEFT JOIN td_entalmacenxtarima tt ON tt.fol_folio = V_EntradasContenedores.Fol_Folio AND tt.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tt.cve_lote = V_EntradasContenedores.Cve_Lote AND tt.ClaveEtiqueta = V_EntradasContenedores.Clave_Contenedor
              WHERE V_EntradasContenedores.Cve_Ubicacion = '$zr' 
              #AND tr.ntarima NOT IN (SELECT ntarima FROM ts_existenciatarima WHERE Fol_Folio = tr.Fol_Folio)
              AND V_EntradasContenedores.Clave_Contenedor != ''
              AND V_EntradasContenedores.Cantidad_C > 0
              AND IFNULL(tt.Ubicada, 'N') != 'S'
              #AND V_EntradasContenedores.Cantidad_C != V_EntradasContenedores.CantidadUbicada
              GROUP BY V_EntradasContenedores.Fol_Folio,V_EntradasContenedores.Cve_Articulo,a.des_articulo, V_EntradasContenedores.Cve_Lote, V_EntradasContenedores.Cantidad_C,V_EntradasContenedores.Clave_Contenedor,c_proveedores.Nombre,V_EntradasContenedores.Cve_Proveedor
              ORDER BY folio DESC
              ) AS f WHERE f.folio = '$f'";

          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
          }
          while($row = mysqli_fetch_array($res))
          {
              //$_POST['tipo'] = ;
              $_POST['cantidad'] = $row['num_existencia'];
              $_POST['cantidad_max'] = $row['num_existencia'];
              //$_POST['idiorigen'] = $row[''];
              //$_POST['ididestino'] = $row[''];
              $_POST['pallet_contenedor'] = $row['pallet_contenedor'];
              $_POST['tipo_pallet_contenedor_articulo'] = $row['tipo'];
              //$_POST['enviar_folios_entrada'] = $row[''];
              $_POST['lp_val'] = $row['LP'];
              //$_POST['pallet_val'] = ;
              $_POST['cve_articulo'] = $row['clave'];
              $_POST['ID_Proveedor'] = $row['id_proveedor'];
              //$_POST['cve_almaci'] = ;
              //$_POST['cve_almacf'] = ;
              $_POST['cve_lote'] = ($row['lote']!='')?($row['lote']):($row['numero_serie']);
              //$_POST['piezaxcaja'] = ;
              //$_POST['piezaxpallet'] = ;
              $_POST['cantidadTotal'] = $row['num_existencia'];
              //$_POST['cve_usuario'] = ;
              //$_POST['maximo_val'] = $row['num_existencia'];
              //$_POST['zonarecepcioni'] = $row[''];
              $data= $ga->moverAcomodo($_POST);
          }
  }
  else
  {
      $data= $ga->moverAcomodo($_POST);
  }

   if($_SERVER['HTTP_HOST'] == 'fc.assistpro-adl.com')
   {
         $folio = $_POST['folio'];

          $sql = "SELECT tipo FROM th_entalmacen WHERE Fol_Folio = '$folio';";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
          }
            $row = mysqli_fetch_array($res);
            $tipo = $row['tipo'];

          if($tipo == 'TR')
          {
              $instanciasap = true;


              $sql = "SELECT * FROM c_datos_ws WHERE Activo = 1 AND Servicio = 'wms_entr';";
              if (!($res = mysqli_query($conn, $sql))) 
              {
                echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
              }

                $row = mysqli_fetch_array($res);
                $endPoint = $row['Url'].'wms_entr';
                $usuario  = $row['User'];
                $password = $row['Pswd'];

              $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
              if (!($res_charset = mysqli_query($conn, $sql_charset)))
                  echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
              $charset = mysqli_fetch_array($res_charset)['charset'];
              mysqli_set_charset($conn , $charset);

              $json = "";
/*
              $sql = "SELECT e.cve_articulo, 
                             (IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = e.cve_articulo AND tipo = 'ubicacion'), 0)+IFNULL(SUM(e.CantidadRecibida), 0)) AS Cantidad, 
                             u.cve_umed AS UM, 
                             NOW() AS fecha_operacion, 
                             th.Cve_Almac
                      FROM td_entalmacen e
                      LEFT JOIN th_entalmacen th ON th.Fol_Folio = e.fol_folio
                      LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                      LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                      WHERE e.fol_folio = (SELECT MAX(Fol_Folio) FROM th_entalmacen) 
                      GROUP BY cve_articulo";
*/
               $sql = "SELECT e.cve_articulo, 
                             (IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = e.cve_articulo AND tipo = 'ubicacion'), 0)+IFNULL(SUM(e.CantidadRecibida), 0)) AS Cantidad, 
                             u.cve_umed AS UM, 
                             th.Fol_OEP as Factura,
                             NOW() AS fecha_operacion, 
                             th.Cve_Almac
                        FROM td_entalmacen e
                        LEFT JOIN th_entalmacen th ON th.Fol_Folio = e.fol_folio
                        LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                        LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                        WHERE e.fol_folio = '{$folio}'
                        GROUP BY Cve_Articulo
                        ORDER BY e.id";
/*
               if($_POST["pallet_contenedor"])
               {
                    $clave_contenedor = $_POST['pallet_val'];
                    $sql = "SELECT e.cve_articulo, 
                                 (IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = e.cve_articulo AND tipo = 'ubicacion'), 0)+IFNULL(SUM(e.Cantidad), 0)) AS Cantidad, 
                                 u.cve_umed AS UM, 
                                 NOW() AS fecha_operacion, 
                                 th.Cve_Almac
                            FROM td_entalmacenxtarima e
                            LEFT JOIN th_entalmacen th ON th.Fol_Folio = e.fol_folio
                            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            WHERE e.fol_folio = '{$folio}'
                            GROUP BY Cve_Articulo";
               }
*/

              if (!($res = mysqli_query($conn, $sql))) 
              {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
              }
              $json = '[';
              $i = 1;
              while($row = mysqli_fetch_array($res))
              {
                $json .= '{';

                  $cve_articulo    = $row["cve_articulo"];
                  $Cantidad        = $row["Cantidad"];
                  $UM              = $row["UM"];
                  $fecha_operacion = $row["fecha_operacion"];
                  $Factura         = $row["Factura"];
                  $Cve_Almac       = $row["Cve_Almac"];

                  $json .= '"item": "'.$cve_articulo.'",';
                  $json .= '"um": "'.$UM.'",';
                  $json .= '"qty": '.$Cantidad.',';
                  $json .= '"typeMov": "T",';
                  $json .= '"warehouse": "'.$Cve_Almac.'",';
                  $json .= '"serdoc": "'.$Factura.'",';
                  $json .= '"rowdoc": "'.$i.'",';
                  $json .= '"dataOpe": "'.$fecha_operacion.'"';
                  $i++;
                $json .= '},';
              }
              $json[strlen($json)-1] = ' ';


              $json .= ']';

              mysqli_close($conn);
              //$sesion = ConectarSAP('Post');

        //****************************************************************************************
        //****************************************************************************************

            //$sesion_id = $_POST['sesion_id'];
            $curl = curl_init();

            curl_setopt_array($curl, array(

          CURLOPT_URL => $endPoint,//wms_tras

          CURLOPT_RETURNTRANSFER => true,

          CURLOPT_ENCODING => '',

          CURLOPT_MAXREDIRS => 10,

          CURLOPT_TIMEOUT => 0,

          CURLOPT_FOLLOWLOCATION => true,

          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

          CURLOPT_CUSTOMREQUEST => $metodo,

          CURLOPT_POSTFIELDS =>$json,

        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky',
            'Content-Type: application/json'
          ),

          CURLOPT_SSL_VERIFYHOST => false,
          
          CURLOPT_SSL_VERIFYPEER => false,

        ));
        //'Content-Type: text/plain',
        //e148fc02-6d94-11ec-8000-0a244a1700f3
        //application/json
        $response = curl_exec($curl);

         curl_close($curl);

          //echo $response;
        //****************************************************************************************
        //****************************************************************************************

          }

   }

  $arr = array(
    "success" => 1,
    "sql" => $data
  );
  echo json_encode($arr);
}

/*if( $_POST['action'] == 'saveconte' ) 
{
  $data= $ga->saveconte($_POST);
  $arr = array(
    "success" => $data
  );
  echo json_encode($arr);
}*/


if( $_POST['action'] == 'traslado' ) {
   $data= $ga->moverTraslado($_POST);


   if($_SERVER['HTTP_HOST'] == 'fc.assistpro-adl.com')
   {
         $traslado_almacen = $_POST['traslado_almacen'];

          if($traslado_almacen)
          {
              $instanciasap = true;

              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

              $sql = "SELECT * FROM c_datos_ws WHERE Activo = 1 AND Servicio = 'wms_trasp';";
              if (!($res = mysqli_query($conn, $sql))) 
              {
                echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
              }

                $row = mysqli_fetch_array($res);
                $endPoint = $row['Url'].'wms_trasp';
                $usuario  = $row['User'];
                $password = $row['Pswd'];

              $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
              if (!($res_charset = mysqli_query($conn, $sql_charset)))
                  echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
              $charset = mysqli_fetch_array($res_charset)['charset'];
              mysqli_set_charset($conn , $charset);

              $json = "";
/*
              $sql = "SELECT e.cve_articulo, 
                             (IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = e.cve_articulo AND tipo = 'ubicacion'), 0)+IFNULL(SUM(e.CantidadRecibida), 0)) AS Cantidad, 
                             u.cve_umed AS UM, 
                             NOW() AS fecha_operacion, 
                             th.Cve_Almac
                      FROM td_entalmacen e
                      LEFT JOIN th_entalmacen th ON th.Fol_Folio = e.fol_folio
                      LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                      LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                      WHERE e.fol_folio = (SELECT MAX(Fol_Folio) FROM th_entalmacen) 
                      GROUP BY cve_articulo";
*/
                $cve_articulo = $_POST['cve_articulo'];
               $sql = "SELECT e.cve_articulo, SUM(e.Existencia) AS Cantidad, u.cve_umed AS UM, NOW() AS fecha_operacion
                        FROM V_ExistenciaGral e
                        LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                        LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                        LEFT JOIN c_almacenp al ON al.id = e.cve_almac
                        WHERE e.cve_articulo = '$cve_articulo'
                        GROUP BY cve_articulo";

               if($_POST["pallet_val"])
               {
                    $clave_contenedor = $_POST['pallet_val'];
                    $sql = "SELECT e.cve_articulo, SUM(e.Existencia) AS Cantidad, u.cve_umed AS UM, NOW() AS fecha_operacion
                            FROM V_ExistenciaGral e
                            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN c_almacenp al ON al.id = e.cve_almac
                            WHERE e.Cve_Contenedor= '$clave_contenedor'
                            GROUP BY cve_articulo";
               }


              if (!($res = mysqli_query($conn, $sql))) 
              {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
              }
              $json = '[';
              while($row = mysqli_fetch_array($res))
              {
                $json .= '{';

                  $cve_articulo    = $row["cve_articulo"];
                  $Cantidad        = $row["Cantidad"];
                  $UM              = $row["UM"];
                  $fecha_operacion = $row["fecha_operacion"];
                  $Cve_Almac       = $row["Cve_Almac"];

                  $json .= '"item": "'.$cve_articulo.'",';
                  $json .= '"um": "'.$UM.'",';
                  $json .= '"qty": '.$Cantidad.',';
                  $json .= '"typeMov": "T",';
                  $json .= '"warehouse": "'.$Cve_Almac.'",';
                  $json .= '"dataOpe": "'.$fecha_operacion.'"';

                $json .= '},';
              }
              $json[strlen($json)-1] = ' ';


              $json .= ']';

              mysqli_close($conn);
              //$sesion = ConectarSAP('Post');

        //****************************************************************************************
        //****************************************************************************************

            //$sesion_id = $_POST['sesion_id'];
            $curl = curl_init();

            curl_setopt_array($curl, array(

          CURLOPT_URL => $endPoint,//wms_tras

          CURLOPT_RETURNTRANSFER => true,

          CURLOPT_ENCODING => '',

          CURLOPT_MAXREDIRS => 10,

          CURLOPT_TIMEOUT => 0,

          CURLOPT_FOLLOWLOCATION => true,

          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

          CURLOPT_CUSTOMREQUEST => $metodo,

          CURLOPT_POSTFIELDS =>$json,

        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky',
            'Content-Type: application/json'
          ),

          CURLOPT_SSL_VERIFYHOST => false,
          
          CURLOPT_SSL_VERIFYPEER => false,

        ));
        //'Content-Type: text/plain',
        //e148fc02-6d94-11ec-8000-0a244a1700f3
        //application/json
        $response = curl_exec($curl);

         curl_close($curl);

          //echo $response;
        //****************************************************************************************
        //****************************************************************************************

          }

   }

    $arr = array(
        "success" => true,
        "data" => $data
    );
     echo json_encode($arr);

}

if( $_POST['action'] == 'calcularImporte' ) {
    $data = $ga->importeTotal($_POST);
    $arr = array(
        "importe" => number_format($data["importeTotalPromedio"], 2),
        "success" => true
    );
    echo json_encode($arr);
    //echo var_dump($arr);
    //die();
}

if(isset($_GET['action']) && $_GET['action'] === 'getBLSelect'){
    $cliente = $_GET['cliente'];
    $almacen = $_GET['almacen'];
    $traslado_almacen = 0;

    $sql_Traslado = ""; 
    if(isset($_GET['traslado_almacen']))
    {
      if($_GET['traslado_almacen'] == 1)
      {
          //$traslado_almacen = 1;
          $sql_Traslado = " AND IFNULL(Status, '') = 'T' ";
      }
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');
    $seleccionado = '';
    //$combo = '<option value="">Seleccione</option>';
    $combo = '';
    $direccion = '';

    $lista_select = "";
    $sqlBL = "";
    if($cliente != '' && $cliente != 'Borrar_La_Lista_de_Clientes')
      $sqlBL = " (CodigoCSD LIKE '%$cliente%') AND ";

    $sql = "SELECT idy_ubica AS value, CodigoCSD AS texto FROM c_ubicacion WHERE {$sqlBL} cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $almacen) {$sql_Traslado}";
    $query = mysqli_query($conn, $sql);
    $datos = mysqli_fetch_all($query, MYSQLI_ASSOC);

    $find = false;
    $firsTValue = 0;
    foreach($datos as $cliente){
        //$cliente = array_map("utf8_encode", $cliente);
        extract($cliente);
        //$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
        ob_start();
        ?><option value="<?php echo $value; ?>"> <?php echo utf8_encode($texto); ?> </option><?php
        $combo .= ob_get_clean();
        if(!$find) $firsTValue = $value;
        $find = true;
    }

    mysqli_close($conn);
    echo json_encode(array(
        "find" => $find,
        "firsTValue" => $firsTValue,
        "combo" => $combo,
        "sql" => $sql
    ));

}

if(isset($_POST['action']) && $_POST['action'] === 'verificar_abc'){

    $clasif_articulo = $_POST['clasif_articulo'];
    $almacen = $_POST['almacen'];
    $ubicacion = $_POST['ubicacion'];
    $cve_articulo = $_POST['cve_articulo'];
    $cantidad = $_POST['cantidad'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');

    $sql = "SELECT cve_almac FROM c_almacen WHERE clasif_abc = '$clasif_articulo' AND cve_almacenp = '$almacen' AND Activo = 1 ORDER BY cve_almac ASC LIMIT 1";
    $query = mysqli_query($conn, $sql);
    $datos = mysqli_fetch_assoc($query);
    $zona = $datos['cve_almac'];

    if($zona)
    {
        //$sql_secuencia = "";
        //if($ubicacion)
        //    $sql_secuencia = " AND orden_secuencia > (SELECT orden_secuencia FROM td_ruta_acomodo WHERE idy_ubica = '$ubicacion' AND id_zona = '$zona') ";
        //$sql = "SELECT * FROM td_ruta_acomodo WHERE id_zona = '$zona' AND Activo = 1 {$sql_secuencia} ORDER BY orden_secuencia LIMIT 1";
        $sql = "SELECT DISTINCT 
                (IFNULL(a.peso, 0)*e.Existencia) AS peso_ocupado,
                u.PesoMaximo, 
                (IFNULL(TRUNCATE(((a.alto*a.ancho*a.fondo)/1000000000), 4), 0)*e.Existencia) AS volumen_ocupado,
                IFNULL(TRUNCATE(((u.num_alto*u.num_ancho*u.num_largo)/1000000000), 4), 0) AS volumen_maximo, 
                u.CodigoCSD, 
                u.idy_ubica
                FROM td_ruta_acomodo r
                LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_ubicacion = r.idy_ubica
                LEFT JOIN c_ubicacion u ON u.idy_ubica = r.idy_ubica
                , c_articulo a
                LEFT JOIN V_ExistenciaGralProduccion ep ON ep.cve_articulo = a.cve_articulo
                WHERE r.id_zona = {$zona}
                AND u.PesoMaximo > ((IFNULL(a.peso, 0)*{$cantidad})+(IFNULL(a.peso, 0)*e.Existencia)) AND a.cve_articulo = '$cve_articulo'
                GROUP BY r.idy_ubica
                ORDER BY r.orden_secuencia
                LIMIT 1";

        $query = mysqli_query($conn, $sql);
        $datos = mysqli_fetch_assoc($query);
        $ubicacion = $datos['idy_ubica'];
        $BL = $datos['CodigoCSD'];
    }

    mysqli_close($conn);
    echo json_encode(array(
        "zona" => $zona,
        "ubicacion" => $ubicacion,
        "BL" => $BL,
        "clasif_articulo" => $clasif_articulo
    ));

}

if(isset($_POST['action']) && $_POST['action'] === 'getPalletsFusionar'){

    $ubicacion = $_POST['ubicacion'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');

    $sql = "SELECT DISTINCT clave_contenedor FROM c_charolas WHERE Tipo != 'Caja' and cve_almac IN (select DISTINCT cve_almac from ts_existenciatarima where idy_ubica = '$ubicacion') AND TipoGen = 0";
    $query = mysqli_query($conn, $sql);

    $options = "";

    while($row = mysqli_fetch_assoc($query))
    {
        extract($row);
        $options .= "<option value='".$clave_contenedor."'>".$clave_contenedor."</option>";
    }

    mysqli_close($conn);
    echo json_encode(array(
        "options" => $options
    ));

}

if(isset($_POST['action']) && $_POST['action'] === 'getPalletsFusionar_cajas'){

    $id_almacen = $_POST['id_almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');

    $sql = "SELECT DISTINCT CveLP FROM c_charolas WHERE IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND Activo = 1 AND Tipo != 'Caja' AND cve_almac = '$id_almacen' AND TipoGen = 0 AND CveLP IS NOT NULL";
    $query = mysqli_query($conn, $sql);

    $options = "";

    while($row = mysqli_fetch_assoc($query))
    {
        extract($row);
        $options .= "<option value='".$CveLP."'>".$CveLP."</option>";
    }

    mysqli_close($conn);
    echo json_encode(array(
        "options" => $options
    ));

}
