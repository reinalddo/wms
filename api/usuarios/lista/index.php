<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] != 'loadUsuariosBitacora' && $_POST['action'] != 'liberar_usuario' && $_POST['action'] != 'liberar_usuario_apk' && $_POST['action'] != 'liberar_usuario_disp') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $sqlSearch = "";
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $cve_almacen = $_POST['cve_almacen'];
    if(!empty($_criterio)){
        $sqlSearch = " AND (c_usuario.cve_usuario like '%$_criterio%' OR c_usuario.nombre_completo like '%$_criterio%' OR c_usuario.email like '%$_criterio%')";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  
    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
/*
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(*) as cuenta from c_usuario Where Activo = '1'";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];

    mysqli_close($conn);
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $_page = 0;

     $sql_master = " AND (trel_us_alm.cve_almac = '{$cve_almacen}' OR (SELECT COUNT(*) FROM trel_us_alm WHERE cve_usuario = c_usuario.cve_usuario) = 0) ";
     if($_POST['usuario_sesion'] == 'wmsmaster')
        $sql_master = "";
 
 
    if (intval($page)>0) $_page = ($page-1)*$limit;
    $sql = "SELECT DISTINCT 
                c_usuario.id_user,
                c_usuario.cve_usuario,
                c_usuario.cve_cia,
                c_usuario.nombre_completo,
                c_usuario.email,
                c_usuario.des_usuario,
                c_usuario.fec_ingreso,
                c_usuario.pwd_usuario,
                c_usuario.ban_usuario,
                c_usuario.`status`,
                c_usuario.Activo,
                c_usuario.`timestamp`,
                c_usuario.identifier,
                c_usuario.image_url,
                GROUP_CONCAT(c_almacenp.clave SEPARATOR ',') AS almacenes,
                c_compania.des_cia AS empresa,
                IF(c_usuario.es_cliente=1, 'Cliente', IF(c_usuario.es_cliente=2, 'Proveedor', t_roles.rol)) AS perfil
                FROM
                c_usuario
                LEFT JOIN c_compania ON c_compania.cve_cia = c_usuario.cve_cia
                LEFT JOIN t_roles ON c_usuario.perfil = t_roles.id_role
                LEFT JOIN trel_us_alm ON trel_us_alm.cve_usuario = c_usuario.cve_usuario
                LEFT JOIN c_almacenp ON c_almacenp.clave = trel_us_alm.cve_almac
            Where c_usuario.Activo = '1' AND c_usuario.cve_usuario != 'wmsmaster' {$sql_master}
            {$sqlSearch} 
            GROUP BY c_usuario.cve_usuario 
            ORDER BY c_usuario.cve_usuario 
            ";
            #OR (c_usuario.cve_usuario IN (SELECT cve_usuario FROM trel_us_alm))

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $_page, $limit; ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
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

    $i = 0;

    while ($row = mysqli_fetch_array($res))
    {
      $row = array_map('utf8_encode', $row);
      //$alma = getAlmacenAsignados();
      $responce->rows[$i]['id']=$row['id_user'];
      $responce->rows[$i]['cell']=array(
        '',
        $row['id_user'], 
        $row['cve_usuario'], 
        $row['nombre_completo'], 
        $row['email'],
        $row['perfil'], 
        $row['almacenes'], //$alma[$row['cve_usuario']],
        $row['empresa']);
      $i++;
    }
    //echo var_dump($responce);
    mysqli_close($conn);
    echo json_encode($responce);
}


if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action'] === 'loadUsuariosBitacora'){
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $almacenSeleccionado = $_POST['almacen'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) {
        $sidx =1;
    }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT     COUNT(DISTINCT o.Fol_folio) AS cuenta 
                FROM th_pedido o 
                LEFT JOIN c_usuario u ON u.cve_usuario = o.Cve_Usuario
               WHERE o.Activo = 1 AND o.status = 'S' AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') = DATE_FORMAT(CURDATE(), '%d-%m-%Y')";
    if (!empty($almacen)) {
        $sqlCount .= " AND o.cve_almac = '$almacen'";
    }
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

          $row = mysqli_fetch_array($res);
          $count = $row['cuenta'];
*/
          $sql = "SELECT c.cve_usuario, c.nombre_completo, DATE_FORMAT(u.fecha_inicio, '%d-%m-%Y %H:%m:%i') AS inicio_sesion, 
                         DATE_FORMAT(u.fecha_cierre, '%d-%m-%Y %H:%m:%i') AS cierre_sesion, u.IP_Address
                  FROM users_bitacora u, c_usuario c
                  WHERE u.cve_usuario = c.cve_usuario AND u.cve_almacen = '$almacenSeleccionado' ORDER BY u.id DESC";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start}, {$limit};";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages) {
        $page=$total_pages;
    }

    $responce = new stdClass();
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['cve_usuario']= $i;
        $responce->rows[$i]['cell']=array($cve_usuario, $nombre_completo, $inicio_sesion, $cierre_sesion, $IP_Address);
        $i++;
    }
    echo json_encode($responce);
}


if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action'] === 'liberar_usuario'){

    $usuario = $_POST['usuario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

          $sql = "DELETE FROM users_online WHERE id_usuario = '{$usuario}'";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación ABC: (" . mysqli_error($conn) . ") ";
    }

    echo true;
}

if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action'] === 'liberar_usuario_disp'){

    $usuario = $_POST['usuario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

          $sql = "DELETE FROM t_eda_sessions WHERE Usuario = '{$usuario}'";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación ABC: (" . mysqli_error($conn) . ") ";
    }

    echo true;
}

if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action'] === 'liberar_usuario_apk'){

    $usuario = $_POST['usuario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

          $sql = "DELETE FROM users_bitacora WHERE cve_usuario = '{$usuario}'";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación ABC: (" . mysqli_error($conn) . ") ".$sql;
    }

    echo 1;
}

/**
 * @author Ricardo Delgado.
 * Busca todos los almacenes y los ordena por usuario.
 * @returns {json}.
 */ 
function getAlmacenAsignados(){

    $sql = 'SELECT a.cve_usuario, b.nombre FROM c_usuario a, c_almacenp b, trel_us_alm c WHERE a.cve_usuario = c.cve_usuario AND b.clave = c.cve_almac';

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
    }

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($res)){

        $rawdata[$i] = $row;
        $i++;

    }
    $arrayMap = [];
    $arrayFinal = [];

    for($i = 0; $i < count($rawdata); $i++){

        $user = $rawdata[$i]['cve_usuario'];

        if (!array_key_exists($user, $arrayMap))
           $arrayMap[$user] = [];

        array_push($arrayMap[$user], $rawdata[$i]['nombre']);

    }

    foreach ($arrayMap as $nombre => $valor)
        $arrayFinal[$nombre] = implode(',',$valor);
    mysqli_close($conn);
    $arrayFinal = array_map('utf8_encode', $arrayFinal);
    return $arrayFinal;

}