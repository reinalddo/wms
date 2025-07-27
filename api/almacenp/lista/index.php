<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $search = $_POST['criterio'];
    $sqlWhere = "";
    if(!empty($search)){
        $sqlWhere .= " AND (c_almacenp.nombre like '%$search%' OR c_almacenp.clave like '%$search%') ";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(clave) as cuenta from c_almacenp Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
    $usuario = $_POST['cve_usuario'];
    $sql_alm_asignado = " AND t.cve_usuario = '{$usuario}' ";
    //$sql_alm_asignado = "";
    $sql = "SELECT  DISTINCT c_almacenp.*,
                    tipo_almacen.desc_tipo_almacen,
                    c_compania.des_cia as empresa,
                    IF(IFNULL(c_almacenp.interno, 0) = 0, 'No', 'Si') AS alm_local
            FROM c_almacenp 
            LEFT JOIN c_compania on c_compania.cve_cia= c_almacenp.cve_cia
            LEFT JOIN tipo_almacen on c_almacenp.cve_talmacen= tipo_almacen.id
            INNER JOIN trel_us_alm t ON t.cve_almac = c_almacenp.clave 
            WHERE c_almacenp.Activo = '1' {$sqlWhere} {$sql_alm_asignado} 
            LIMIT $start, $limit;";

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
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    $proDefault = searchStoreDefault($_POST['id']);
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $default = 'star-empty';
        $responce->rows[$i]['id']=$row['id'];
        if($proDefault === $row['clave']) $default = 'star';
        $responce->rows[$i]['cell']=array(
          $default,
          $row['id'],
          htmlentities($row['clave']),
          htmlentities($row['nombre']),
          htmlentities($row['desc_tipo_almacen']),
          htmlentities($row['direccion']),
          htmlentities($row['empresa']),
          htmlentities($row['contacto']),
          htmlentities($row['correo']),
          htmlentities($row['telefono']),
          htmlentities($row['BL']),
          htmlentities($row['latitud']),
          htmlentities($row['longitud']),
          htmlentities($row['alm_local']),
          htmlentities($row['tipolp_traslado'])
          );
        $i++;
    }
    
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    /*Pedidos listos para embarcar*/
    $sql = "SELECT clave, nombre FROM c_almacenp WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND des_almac like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $cve_almac,
            'descripcion' => $des_almac
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}

/**
 * @author Ricardo Delgado.
 * Busca el almance predeterminado del usuario.
 * @param {String} Id del usuario.
 * @returns {String} Codigo del Almancen.
 */ 
function searchStoreDefault($id){

    $sql = 'SELECT cve_almac FROM t_usu_alm_pre WHERE id_user = '.$id;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($res)){

        $rawdata[$i] = $row;
        $i++;

    }

    return $rawdata[0]['cve_almac'];

    mysqli_close($conn);

}