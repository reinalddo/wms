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

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select * from th_pedido Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT
        th_pedido.Fol_folio,
        th_pedido.Fec_Pedido,
        th_pedido.Cve_clte,
        th_pedido.`status`,
        th_pedido.Fec_Entrega,
        th_pedido.cve_Vendedor,
        th_pedido.Num_Meses,
        th_pedido.Observaciones,
        th_pedido.statusaurora,
        th_pedido.ID_Tipoprioridad,
        th_pedido.Fec_Entrada,
        th_pedido.transporte,
        th_pedido.ruta,
        th_pedido.bloqueado,
        th_pedido.fechadet,
        th_pedido.fechades,
        th_pedido.cve_almac,
        th_pedido.destinatario,
        th_pedido.subido,
        th_pedido.cve_ubicacion,
        th_pedido.Pick_Num,
        th_pedido.Cve_Usuario,
        th_pedido.Ship_Num,
        th_pedido.Cve_Sucursal,
        th_pedido.BanEmpaque,
        th_pedido.Cve_CteProv,
        th_pedido.Activo,
        c_cliente.Cve_Clte,
        c_cliente.RazonSocial
        FROM
        th_pedido
        INNER JOIN c_cliente ON th_pedido.Cve_clte = c_cliente.Cve_Clte Where th_pedido.Fol_folio like '%".$_criterio."%' and th_pedido.Activo = '1';";
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

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['Fol_folio'];
        $responce->rows[$i]['cell']=array($row['Fol_folio'], $row['RazonSocial'], $row['Observaciones']);
        $i++;
    }
    echo json_encode($responce);
}