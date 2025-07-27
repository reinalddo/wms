<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select 
    COUNT(*) as total
    from 
    t_roles 
    Where rol like '%".$_criterio."%' and Activo = '1';";

//  $sqlCount = "Select * from t_roles;";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];


    $_page = 0;
    
    if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "SELECT * FROM t_roles Where rol like '%".$_criterio."%' and activo = '1' LIMIT $_page, $limit;";
    //$sql = "Select * from th_pedido INNER JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor Where c_proveedores.Empresa like '%".$_criterio."%' and th_aduana.Activo = '1';";

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

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;

        $responce["rows"][$i]['id']=$row['id_role'];
        $responce["rows"][$i]['cell']=array($row['id_role'], $row['rol']); //, $row['activo']
        $i++;
    }
    echo json_encode($responce);

}