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

    if(isset($_POST['solicitud']) && $_POST['solicitud'] == 'pedidos'){
        $sqlCount = "SELECT COUNT(*) as total from th_pedido WHERE status = 'C';";
        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $almacen = $_POST['almacen'];
        $ruta = $_POST['ruta'];

        $row = mysqli_fetch_array($res);
        $count = $row['total'];
        $sql = "SELECT th_pedido.Fol_folio AS folio, IFNULL(c_cliente.RazonSocial, '--') as cliente, IFNULL(td_pedido.SurtidoXCajas, '0') as cajas, IFNULL(td_pedido.SurtidoXPiezas, '0') as piezas FROM th_pedido LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte LEFT JOIN td_pedido ON td_pedido.Fol_folio = th_pedido.Fol_folio WHERE th_pedido.status = 'C'";
        if(!empty($almacen)){
            $sql .= " AND th_pedido.cve_almac = '".$almacen."'";
        }
        if(!empty($ruta)){
            $sql .= " AND th_pedido.ruta = '".$ruta."'";
        }
        $sql .= " AND th_pedido.Fol_folio LIKE '%".$_criterio."%' ORDER BY th_pedido.Fol_folio $sord LIMIT $start, $limit";
        
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
            $html = '<input data-shipped="0" type="checkbox" onclick="addToShipment(this)" value="'.$row['folio'].'">';
            $arr[] = $row;
            $responce->rows[$i]['id']=$row['Fol_folio'];
            $responce->rows[$i]['cell']=array($html,$row['folio'], $row['cliente'], $row['cajas'], $row['piezas']);
            $i++;
        }
        
        echo json_encode($responce);
    }
    else{   

        // prepara la llamada al procedimiento almacenado Lis_Facturas
        $sqlCount = "Select * from th_pedido;";
        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $row = mysqli_fetch_array($res);
        $count = $row[0];
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
        -- th_pedido.Cve_Sucursal,
        th_pedido.BanEmpaque,
        th_pedido.Cve_CteProv,
        -- th_pedido.Activo,
        c_cliente.Cve_Clte,
        c_cliente.RazonSocial
        FROM
        th_pedido
        INNER JOIN c_cliente ON th_pedido.Cve_clte = c_cliente.Cve_Clte Where th_pedido.Fol_folio like '%".$_criterio."%'";
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
    mysqli_close();   
}

if(isset($_GET) && !empty($_GET)){
    $page = !empty($_GET['page']) ? $_GET['page'] : 0 ;
    $rows = empty($_GET['rows']) ? $_GET['rows'] : 0;
    $search = $_GET['search'];
    $folio = isset($_GET['folio']) ? $_GET['folio'] : '' ;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT th_pedido.Fol_folio AS folio, IFNULL(c_cliente.RazonSocial, '--') as cliente, IFNULL(td_pedido.SurtidoXCajas, '0') as cajas, IFNULL(td_pedido.SurtidoXPiezas, '0') as piezas FROM th_pedido LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte LEFT JOIN td_pedido ON td_pedido.Fol_folio = th_pedido.Fol_folio WHERE th_pedido.status = 'C'";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND th_pedido.Fol_folio like '%".$search."%'";
    }

    if(!empty($folio)){
        $sql .= " AND th_pedido.Fol_folio = '".$folio."' ";
    }

    if($page != '0' && $rows != '0'){
        $sql.= " LIMIT ".$page.", ".$rows.";";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result[] = array(
            'folio'     =>  $folio,
            'cliente'   =>  $cliente,
            'cajas'     =>  $cajas,
            'piezas'    =>  $piezas
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}
