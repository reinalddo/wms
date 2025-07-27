<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');


    $sql = "SELECT SUM((SELECT COUNT(ID_PLAN) FROM det_planifica_inventario) + (SELECT COUNT(ID_Inventario) FROM th_inventario)) AS total";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
    $sqlCiclico = "SELECT	DISTINCT cab.ID_PLAN AS consecutivo, 
    						DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y %H:%i:%s') AS fecha_inicio, 
    						DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y %H:%i:%s') AS fecha_final,
    						ap.nombre AS almacen, 
    						IFNULL(ap.des_almac,'--') AS `zona`,
    						(SELECT u.cve_usuario from c_usuario u, t_conteoinventariocicl cic where cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN order by cic.NConteo desc limit 1) AS usuario,
    						(CASE 
    							WHEN d.status = 'A' THEN 'Abierto'
    							WHEN d.status = 'T' THEN 'Cerrado'
    							ELSE 'Sin Definir'
    						END) AS status,
    						'--' AS diferencia,
    						'Cíclico' AS tipo,
                            '--' AS supervisor,
                            (CASE
                                WHEN cab.ID_PERIODO = 1 THEN 1
                                WHEN cab.ID_PERIODO = 2 THEN TIMESTAMPDIFF(DAY, cab.FECHA_INI, cab.FECHA_FIN)
                                WHEN cab.ID_PERIODO = 3 THEN TIMESTAMPDIFF(WEEK, cab.FECHA_INI, cab.FECHA_FIN)
                                WHEN cab.ID_PERIODO = 4 THEN TIMESTAMPDIFF(MONTH, cab.FECHA_INI, cab.FECHA_FIN)
                            END) AS n_inventario
    				FROM 	det_planifica_inventario d
                        LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                        LEFT JOIN c_almacenp ap ON a.cve_almac = ap.id 
                        LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN";

    $sql = "SELECT *, 'Físico' AS tipo, '--' AS supervisor, '1' AS n_inventario FROM V_AdministracionInventario
    		UNION {$sqlCiclico}
            ORDER BY consecutivo DESC
            LIMIT $start, $limit;";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
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
        extract($row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$consecutivo;
        $fecha_i = date('Y-m-d', strtotime($fecha_inicio));
        $fecha_f = $fecha_final !== '--' ? date('Y-m-d', strtotime($fecha_final)) : false;
        $efectuado = "";
        if(!$fecha_f){
            $efectuado = '<i class="fa fa-circle yellow"></i>';
        }else{
            if($fecha_i >= $fecha_f){
                $efectuado = '<i class="fa fa-circle green"></i>';
            }else{
                $efectuado = '<i class="fa fa-circle red"></i>';
            }
        }
        $responce->rows[$i]['cell']=[
            $consecutivo,
            $almacen, 
            $zona, 
            $usuario, 
            $fecha_inicio, 
            $fecha_final, 
            $supervisor, 
            $diferencia, 
            $status, 
            $tipo,
            $n_inventario, 
            $efectuado
        ];
        $i++;
    }
    echo json_encode($responce);exit;
}


if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getAvailableUsers')
{
    $id = $_GET['id'];
    $inventario = $_GET['id_plan'];
    $ubicacion = $_GET['cve_ubicacion'];
    $articulo = $_GET['clave'];
    $lote = $_GET['lote'];

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conn, 'utf8');
  $usuarios = '';
  $sql = "SELECT cve_usuario, nombre_completo FROM c_usuario WHERE Activo = 1 AND cve_usuario NOT IN (SELECT DISTINCT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND idy_ubica = '{$ubicacion}' AND cve_articulo = '{$articulo}' AND cve_lote = '{$lote}' AND cve_usuario != '') ORDER BY nombre_completo ASC;";
  $query = mysqli_query($conn, $sql);
  
  if($query->num_rows > 0)
  {
    $usuarios = mysqli_fetch_all($query, MYSQLI_ASSOC);
  }

  $sql = "SELECT DISTINCT t.NConteo AS Conteo, c.cve_usuario, c.nombre_completo 
          FROM c_usuario c, t_invpiezas t 
          WHERE t.ID_Inventario = {$inventario} AND t.idy_ubica = '{$ubicacion}' AND t.cve_articulo = '{$articulo}' AND t.cve_lote = '{$lote}' AND t.cve_usuario != '' AND t.cve_usuario = c.cve_usuario 
          ORDER BY Conteo";
  $query = mysqli_query($conn, $sql);
  $usuarios_cerrado = mysqli_fetch_all($query, MYSQLI_ASSOC);

  //echo var_dump($usuarios);
  //die();
  echo json_encode(array(
    "sql_usuarios" => $sql,
    "usuarios_cerrado" => $usuarios_cerrado,
    "usuarios" => $usuarios
  ));
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargar_usuario')
{
  $id_inventario = $_POST['id_inventario'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conn, 'utf8');
  $usuarios = '';
  $sql = "SELECT cve_usuario FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario};";
  $query = mysqli_query($conn, $sql);
  
  if($query->num_rows > 0)
  {
    $success = true;
    $usuarios = mysqli_fetch_all($query, MYSQLI_ASSOC);
  }
  echo var_dump($success);
  die();
  echo json_encode(array(
    "success"  => $success,
    "usuarios" => $usuarios
  ));
}


if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getAvailableUserss'){
    $id = $_GET['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $usuarios = '';
    $query = mysqli_query($conn, "SELECT cve_usuario, nombre_completo FROM c_usuario WHERE Activo = 1 AND cve_usuario NOT IN (SELECT cve_usuario FROM t_conteoinventario WHERE ID_Inventario = {$id}) and perfil=2;");

    if($query->num_rows > 0){
        $usuarios = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    echo json_encode(array(
        "usuarios" => $usuarios
    ));
}

if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getConteos')
{
  $id = $_GET['id'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conn, 'utf8');
  $conteos = '';
  $query = mysqli_query($conn, "SELECT DISTINCT NConteo AS conteo FROM t_conteoinventario WHERE NConteo > 0 AND ID_Inventario = {$id};");
  if($query->num_rows > 0)
  {
    $conteos = mysqli_fetch_all($query, MYSQLI_ASSOC);
  }
  //echo var_dump($conteos);
  //die();
  
  echo json_encode(array(
    "conteos" => $conteos
  ));
}

if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getPendingCount')
{
    $id = $_GET['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $total = '';
    $query = mysqli_query($conn, "SELECT COUNT(cve_articulo) AS total FROM `t_invpiezas` WHERE Cantidad = 0 AND ID_Inventario = {$id} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$id} AND NConteo <> 0)");

    if($query->num_rows > 0){
        $total = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['total'];
    }
        
    echo json_encode(array(
        "total" => $total
    ));
}

if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getAvailabeInventories'){
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $inventories = [];
    
    $query = mysqli_query($conn, "SELECT ID_Inventario AS n FROM th_inventario WHERE Activo = 1 AND cve_almacen = (SELECT clave FROM c_almacenp WHERE id = {$almacen}) AND Status = 'T'");
    if($query->num_rows > 0){
        $inventories = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    echo json_encode($inventories);
}