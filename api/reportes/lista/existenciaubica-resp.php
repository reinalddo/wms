<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['start'];
    $limit = $_GET['length'];
    $search = $_GET['search']['value'];

    $articulo = $_GET["articulo"];
    $contenedor = $_GET["contenedor"];
    $almacen = $_GET["almacen"];
    $zona = $_GET["zona"];
    $proveedor = $_GET["proveedor"];
    $bl = $_GET["bl"];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,"utf8");
  
    $sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];

    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";
  
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";
    $sqlProveedor = !empty($proveedor) ? "AND x.proveedor = '{$proveedor}'" : "";
  
    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";

    $sqlCount = "SELECT
                    count(e.cve_articulo) as total
                  FROM V_ExistenciaGral e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                  WHERE e.cve_almac = '{$almacen}' AND e.tipo = 'ubicacion' AND e.Existencia > 0 {$sqlArticulo}  {$sqlZona} {$sqlProveedor} {$sqlbl}";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }

    /*Pedidos pendiente de acomodo*/
    $sql = "
      SELECT * FROM(
         SELECT
            
            '<input class=\"column-asignar\" type=\"checkbox\">' as acciones, 
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            if(e.Cuarentena = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            COALESCE(if(a.control_lotes ='S',l.LOTE,''), '--') as lote,
            COALESCE(if(a.control_lotes = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            COALESCE(if(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') as nserie,
            e.Existencia as cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            #COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(
                (select nombre from c_proveedores where ID_Proveedor = (
                    IFNULL(
                        (select ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                        IFNULL(
                            (select ID_Proveedor from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                            IFNULL(
                                (select ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                0
                            )
                        )
                    )
                )),'--'
            )as proveedor,
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
            CONCAT(
                CASE
                    WHEN u.Tipo = 'L' THEN 'Libre'
                    WHEN u.Tipo = 'R' THEN 'Reservada'
                    WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                END, '| Picking ',
                CASE 
                    WHEN u.Picking = 'S' THEN '<i class=\"fa fa-check text-success\"></i>'
                    WHEN u.Picking = 'N' THEN '<i class=\"fa fa-times text-danger\"></i>'
                END
            ) AS tipo_ubicacion,
            truncate(a.costoPromedio,2) as costoPromedio,
            truncate(a.costoPromedio*e.Existencia,2) as subtotalPromedio
            FROM
                V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
                WHERE e.cve_almac = '{$almacen}'  AND e.tipo = 'ubicacion' AND e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
            order by l.CADUCIDAD ASC
                )x
            where 1
            {$sqlProveedor}
            {$sqlbl}";
  
    $l = " LIMIT $page,$limit; ";
    $sql .= $l;
  
    //echo var_dump($sql);
    //die();
  
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $data[] = $row;
        $i++;
    }

    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $data,
        "sql" => $sql,
        "bl" => $responce
    );
    echo json_encode($output);exit;
}

if($_POST['action'] == "savemotivo")
{
    $motivos = $_POST['motivos'];
    $registros = $_POST['registros'];
    $almacen = $_POST['almacen'];
    $fecha = $_POST['fecha'];
    $id_usuario = $_POST['id_usuario'];

		if($registros[0][1] != "")
   	{
				$sql1 ="
						SELECT  
								c_charolas.IDContenedor AS id
						FROM c_charolas 
						WHERE clave_contenedor = '{$registros[0][1]}'";
						$res = getArraySQL($sql1);
						$id = $res[0]["id"];
  	 }
  
    $sql1 ="
				SELECT  
						c_ubicacion.idy_ubica AS idy_ubica
				FROM c_ubicacion 
				WHERE CodigoCSD = '{$registros[0][0]}'";
				$res = getArraySQL($sql1);
				$idy_ubica = $res[0]["idy_ubica"];
	
    $sql1 ="
          SELECT
            count(Fol_Folio) as conteo
          FROM t_movcuarentena";
          $res = getArraySQL($sql1);
          $res[0]["conteo"] ++;
          $num = str_pad($res[0]["conteo"],3,0,STR_PAD_LEFT);
          $nume = $fecha.$num;
          $folio =  'QA'.$nume;
  
    foreach($registros as $registro)
    {
        $lote = $registro[3];
        if($lote== ""){$lote = $registro[4];}
        if($registro[1] != "")
        {
          $sql2 ="
              UPDATE ts_existenciatarima
              SET Cuarentena = 1
              where cve_almac = '{$almacen}'
                and cve_articulo = '{$registro[2]}'
                and lote = '{$lote}'
                and existencia = '{$registro[5]}'";
                $res = getArraySQL($sql2);
        }
        else  
        {
          $sql2 ="
              UPDATE ts_existenciapiezas
              SET Cuarentena = 1
              where cve_almac = '{$almacen}'
                and cve_articulo = '{$registro[2]}'
                and cve_lote = '{$lote}'
                and Existencia = '{$registro[5]}'";
                $res = getArraySQL($sql2);
        }
    
        $sql ="
            INSERT INTO t_movcuarentena 
        (Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) 
            VALUES ('$folio', '$idy_ubica', '$id', '$registro[2]', '$registro[3]', '$registro[5]', '', NOW(), '$motivos', 'Q', '$id_usuario')";
        $res = getArraySQL($sql);
        $result = array(
        "success" => true,
            "sql" => $res,
           "sql2" => $res,
         );
    }
   echo json_encode($result);exit;
}

if($_POST['action'] == "traermotivos")
{
    $sql = "
     SELECT
          c_motivo.id,
          c_motivo.Tipo_Cat,
          c_motivo.Des_Motivo as descri
     FROM c_motivo
     WHERE c_motivo.Tipo_Cat = 'Q'
   ";
   $res = getArraySQL($sql);
   $result = array(
     "sql" => $res,
   );
  
   echo json_encode($result);exit;
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === "exportExcelExistenciaUbica"){

    $almacen = $_POST['almacen'];
    $zona = $_POST['zona'];
    $articulo = $_POST['articulo'];

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de existencia por ubicación.xlsx";

    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '$zona')" : "";
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '$articulo'" : "";
    $sql = "
        SELECT
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            COALESCE(l.LOTE, '--') as lote,
            COALESCE(l.CADUCIDAD, '--') as caducidad,
            COALESCE(s.numero_serie, '--') as nserie,
            e.Existencia as cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
            CONCAT(CASE
                        WHEN u.Tipo = 'L' THEN 'Libre'
                        WHEN u.Tipo = 'R' THEN 'Reservada'
                        WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                    END, '| Picking ',
                    CASE 
                        WHEN u.Picking = 'S' THEN '✓'
                        WHEN u.Picking = 'N' THEN '✕'
                    END
             ) AS tipo_ubicacion,
             a.costoPromedio as costoPromedio,
             a.costoPromedio*e.Existencia as subtotalPromedio,
            (SELECT SUM(a.costoPromedio*e.Existencia) FROM V_ExistenciaGralProduccion e LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo) as importeTotalPromedio,
            (SELECT BL FROM c_almacenp WHERE id = '$almacen' LIMIT 1) AS codigo_BL
      FROM V_ExistenciaGralProduccion e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_serie s ON s.cve_articulo = e.cve_articulo
      WHERE e.cve_almac = '$almacen' AND e.tipo = 'ubicacion' AND e.Existencia > 0  {$sqlArticulo}  {$sqlZona}
";
  
    //echo var_dump($sql);
    //die();
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
  
    $bl = 0;
    foreach($data as $d)
    {
       $bl = $d['codigo_BL'];
    }
  
    $bl1 = $bl;

    $header = array(
        'Almacén',
        'Zona de Almacenaje',
        'Codigo BL'." ".$bl1.'',
        'Clave',
        'Descripción',
        'Lote',
        'Caducidad',
        'N. Serie',
        'Cantidad',
        'Proveedor',
        'Fecha de Ingreso',
        'Tipo de Ubicación',
        'Costo Promedio',
        'Subtotal',
        'Importe'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header );

    $sum = 0;
    foreach($data as $d)
    {
       $sum+= $d['subtotalPromedio'];
    }
  
    $sum1 = $sum;
  
    foreach($data as $d){
      
        $row = array(
            $d['almacen'],
            $d['zona'],
            $d['codigo'],
            $d['clave'],
            $d['descripcion'],
            $d['lote'],
            $d['caducidad'],
            $d['nserie'],
            $d['cantidad'],
            $d['proveedor'],
            $d['fecha_ingreso'],
            $d['tipo_ubicacion'],
            $d['costoPromedio'],
            $d['subtotalPromedio'],
            $sum1
          
        );
        $sum1 = "";
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

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

if( $_POST['action'] == 'traer_BL' ) 
{
    $almacen = $_POST["almacen"];
    $responce = "";
    $sql = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'" and Activo = 1';
    $result = getArraySQL($sql);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
 
    echo json_encode($responce["bl"]);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
{
    $almacen = $_POST["almacen"];
    $articulo = $_POST["articulo"];
    $contenedor = $_POST["contenedor"];
    $zona = $_POST["zona"];
    $bl = $_POST["bl"];
    
    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";
  
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";
  
    $sqlbl = !empty($bl) ? "AND u.codigoCSD like '%{$bl}%'" : "";
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
     
    $sqlTotales ="
        SELECT
            count(distinct(e.cve_articulo)) as productos,
            truncate(sum(e.Existencia),4) as unidades
        FROM V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
		    WHERE e.tipo = 'ubicacion' 
            AND e.Existencia > 0 
            AND e.cve_almac = '".$almacen."'
            {$sqlZona}
            {$sqlArticulo}
            {$sqlContenedor}
            {$sqlbl}
    ";
    
    if (!($res = mysqli_query($conn, $sqlTotales))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    //echo var_dump($sqlTotales);
    
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();
    header('Content-type: application/json');
    $output = array("data" => $data); 
    echo json_encode($output);
}