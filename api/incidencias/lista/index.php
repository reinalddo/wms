<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'apertura_cierre_change') 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $status = $_POST['status'];

    if(!$status) $status = 'A';

    $sql = "SELECT * FROM c_motivo WHERE Tipo_Cat = '$status' AND Activo = 1";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
    }

    $options = "<option value=''>Seleccione Motivo</option>";
    while($row = mysqli_fetch_array($res))
    {
        $options .= "<option value='".$row['id']."'>".utf8_encode($row['Des_Motivo'])."</option>";
    }

    echo json_encode($options);
}
else if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $tipo_reporte_busq = $_POST['tipo_reporte_busq'];
    $almacen = $_POST['almacen'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $SqlTipoReporte = "";
    if($tipo_reporte_busq)
       $SqlTipoReporte = " AND tipo_reporte = '$tipo_reporte_busq' ";

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(*) as cuenta from th_incidencia Where (Fol_folio like '%".$_criterio."%' or Descripcion like '%".$_criterio."%') and  Activo = '1' {$SqlTipoReporte};";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];

	  $_page = 0;

	   if (intval($page)>0) $_page = ($page-1)*$limit;


    $filtro_clientes = "";

    if(isset($_POST['cve_cliente']))
    {
      if($_POST['cve_cliente'])
      {
          $cve_cliente = $_POST['cve_cliente'];
          $filtro_clientes = "AND c.Cve_Clte = '{$cve_cliente}'";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND c.ID_Proveedor = {$proveedor}";
      }
    }


    $sql = "
            SELECT
                    i.ID_Incidencia AS numero,
                    a.nombre AS almacen,
                    c.Cve_Clte AS clave,
                    p.Nombre AS proveedor,
                    c.RazonSocial AS razon_social,
                    i.Fol_folio AS folio,
                    (
                        CASE
                                WHEN i.tipo_reporte = 'P' THEN 'Petici&oacute;n'
                                WHEN i.tipo_reporte = 'Q' THEN 'Queja'
                                WHEN i.tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN i.tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    DATE_FORMAT(i.Fecha, '%d-%m-%Y') AS fecha_inicio,
                    u.nombre_completo AS usuario_registro,
                    IF(i.status = 'C', DATE_FORMAT(i.Fecha_accion, '%d-%m-%Y'), '') AS fecha_fin,
                    IF(i.status = 'C', (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.responsable_recibo), '') AS usuario_cierre,
                    (
                        CASE
                              WHEN i.status = 'A' THEN 'Abierto'
                              WHEN i.status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia i
            LEFT JOIN c_almacenp a ON a.clave = i.centro_distribucion
            LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
            LEFT JOIN c_usuario u ON u.cve_usuario = i.reportador
  	        WHERE i.Activo = 1 AND i.centro_distribucion = '{$almacen}' {$filtro_clientes} {$SqlTipoReporte}";

    if(!empty($_criterio)){
      $sql .= " AND (i.Fol_folio like '%$_criterio%' OR c.RazonSocial LIKE '%$_criterio%' OR c.Cve_Clte LIKE '%$_criterio%' OR p.ID_Proveedor LIKE '%$_criterio%' OR p.Nombre LIKE '%$_criterio%' OR p.cve_proveedor LIKE '%$_criterio%') ";
    }

    $sql .= " ORDER BY i.ID_Incidencia DESC LIMIT $_page, $limit;";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']=$row['numero'];
        $responce->rows[$i]['cell']=array('',$numero, $clave, $proveedor, $razon_social, $folio, $tipo_reporte, $fecha_inicio, $usuario_registro, $fecha_fin, $usuario_cierre, $status, $almacen);
        $i++;
    }
    echo json_encode($responce);
}
if(isset($_GET) && !(empty($_GET))){
    $id = $_GET['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT
                    Fol_folio AS folio,
                    (SELECT nombre FROM c_almacenp WHERE clave = centro_distribucion) AS centro,
                    (SELECT RazonSocial FROM c_cliente WHERE Cve_Clte = cliente) AS cliente,
                    ID_Incidencia AS numero,
                    (
                        CASE
                                WHEN tipo_reporte = 'P' THEN 'Petici&oacute;n'
                                WHEN tipo_reporte = 'Q' THEN 'Queja'
                                WHEN tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    reportador AS reportador,
                    cargo_reportador,
                    DATE_FORMAT(Fecha, '%d-%m-%Y') AS fecha_reporte,
                    Descripcion  AS descripcion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_recibo) AS responsable_recibo,
                    responsable_caso,
                    plan_accion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_plan) AS responsable_plan,
                    DATE_FORMAT(Fecha_accion, '%d-%m-%Y') AS fecha_plan,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_verificacion) AS responsable_verificacion,
                    (
                        CASE
                                WHEN status = 'A' THEN 'Abierto'
                                WHEN status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia
            WHERE ID_Incidencia = {$id} AND Activo = 1;";
    $query = mysqli_query($conn, $sql);
    if($query->num_rows > 0){
        $res = mysqli_fetch_array($query);
        $res = array_map('utf8_encode', $res);
        echo json_encode(array('data' => $res));
    }else{
        echo json_encode(array('data' => ''));
    }
}
