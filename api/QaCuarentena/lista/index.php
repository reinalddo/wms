<?php
include_once '../../../config.php';
include_once '../../../app/Utils.php';


use App\Utils;

error_reporting(0);

if (isset($_POST) AND !empty($_POST) AND $_POST['action'] == 'findProductos' ) {
    $page   = $_POST['page']; // get the requested page
    $limit  = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx   = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord   = $_POST['sord']; // get the direction

    //Se recibe los parametros POST del Grid
    $criterio   = Utils::pSQL($_POST['criterio']);

    if( $criterio == '' ){
        Utils::response([]);
    }

    /**
     * Fitros: 
     *  1: Producto (Default)
     *  2: Almacén                                       
     *  3: Lote
     *  4: Fecha de ingreso
     *  5: Todos
     */   
    $filtro     = Utils::pSQL($_POST['filtro']);

    switch ($filtro) {
        case '2': //Almacén  
            $where = "a.clave LIKE '%{$criterio}%' OR a.nombre LIKE '%{$criterio}%'";
        break;
        case '3': //Lote
            $where = "l.LOTE LIKE '%{$criterio}%'";
        break;
        case '4': //Fecha de ingreso
            $where = "p.fec_altaart LIKE '%{$criterio}%'";
        break;
        case '5': //Todos
            $where = "p.cve_articulo LIKE '%{$criterio}%' OR ".
                      "a.clave LIKE '%{$criterio}%' OR a.nombre '%{$criterio}%' OR ".
                      "l.LOTE LIKE '%{$criterio}%' OR ".
                      "p.fec_altaart LIKE '%{$criterio}%'";
        break;
        default: //Producto (Default)
            $where = "p.cve_articulo LIKE '%{$criterio}%'";
        break;
    }

    //Paginación
    $start = $limit * $page - $limit; // do not put $limit*($page - 1)
    if(!$sidx) $sidx =1;


    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlClaveProducto = "
        SELECT 
            p.cve_articulo AS clave_producto
            , p.des_articulo AS nombre_producto
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen
            , l.LOTE AS lote
        FROM c_articulo p
            LEFT JOIN c_almacenp a ON p.cve_almac = a.clave
            LEFT JOIN c_lotes l ON p.cve_articulo = l.cve_articulo
        WHERE {$where}
    ";


    //Conexión con la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');

    if (!($result = mysqli_query($conn, $sqlClaveProducto))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($result);

    $_page = 0;

    if (intval($page)>0){
        $_page = ($page-1) * $limit;
    }
   
    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }
    
    if ($page > $total_pages){
        $page = $total_pages;
    }
        

    $responce = [
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'rows' => []
    ];


    $i = 0;
    while ($row = mysqli_fetch_array($result)) {      
        $responce['rows'][$i]['cell'] = [
            $row['clave_producto'],
            $row['nombre_producto'],
            $row['clave_almacen'],
            $row['nombre_almacen'],
            $row['lote'],
            $row['fecha_ingreso'],
            ''
        ];
        $i++;
    }
    Utils::response($responce);
}



if (isset($_POST) AND !empty($_POST) AND $_POST['action'] == 'productosEnCuarentena' ) {

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');

    if (!($result = mysqli_query($conn, 'SELECT * FROM th_qacuarentena'))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    
    $count = mysqli_num_rows($result);

    $page   = $_POST['page']; // get the requested page
    $limit  = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx   = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord   = $_POST['sord']; // get the direction
    $_page = 0;

    if (intval($page)>0){
        $_page = ($page-1) * $limit;
    }   
    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }    
    if ($page > $total_pages){
        $page = $total_pages;
    }
        
    $responce = [
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'rows' => []
    ];

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {      
        $responce['rows'][$i]['cell'] = [
            $row['id'],
            $row['clave_producto'],
            $row['producto'], 
            $row['almacen'],           
            $row['lote'],
            $row['fecha_ingreso'],
            ''
        ];
        $i++;
    }

    Utils::response($responce);
}



if (isset($_POST) AND !empty($_POST) AND $_POST['action'] == 'producto' ) {

    $id = Utils::pSQL($_POST['id']);

    $sqlClaveProducto = "
        SELECT 
            c.*,
            u.nombre_completo responsable
        FROM th_qacuarentena c
            LEFT JOIN c_usuario u ON c.id_user = u.id_user
        WHERE c.id = {$id}
    ";


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');

    if (!($result = mysqli_query($conn, $sqlClaveProducto))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    
    $count = mysqli_num_rows($result);

    $page   = $_POST['page']; // get the requested page
    $limit  = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx   = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord   = $_POST['sord']; // get the direction
    $_page = 0;

    if (intval($page)>0){
        $_page = ($page-1) * $limit;
    }   
    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }    
    if ($page > $total_pages){
        $page = $total_pages;
    }
        
    $responce = [
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'data' => []
    ];

   
    $row = mysqli_fetch_array($result);     
    $responce['data'] = [
        'id'        => $row['id'],
        'clave_almacen'     => $row['clave_almacen'],
        'almacen'           => $row['almacen'],
        'clave_producto'    => $row['clave_producto'],
        'producto'          => $row['producto'],
        'lote'      => $row['lote'],
        'responsable'=>$row['responsable'],
        'creado'    => $row['creado'],
        'fecha_ingreso'=>$row['fecha_ingreso'],
        ''
    ];
   

    Utils::response($responce);
}



if (isset($_POST) AND !empty($_POST) AND $_POST['action'] == 'lotesDelProducto' )
{
    $producto = Utils::pSQL($_POST['producto']);
    $sql = "
        SELECT
            *
        FROM
            c_lotes
        WHERE
            cve_articulo = '{$producto}'
    ";


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');

    if (!($result = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    
    $response = [
        'lotes' => []
    ];

    while ($row = mysqli_fetch_array($result)) {  
        $response['lotes'][] = $row['LOTE'];
    }

    Utils::response($response);
}