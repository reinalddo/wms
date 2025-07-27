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
    $_usuario = $_POST['usuario'];
    $cve_proveedor = $_POST['cve_proveedor'];
    $cve_cliente   = $_POST['cve_cliente'];


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select COUNT(id_pedido) AS total from th_pedido Where Activo = '1' AND status = 't';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $ands = "";
    if($_usuario != "")
    {
      $ands.= "AND (c.Cve_Clte = '{$_usuario}' OR c.ID_Proveedor = '{$_usuario}')";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

  	$sql = "SELECT	p.id_pedido,
                	p.Fol_folio,
                    IFNULL(p.Pick_Num, '') AS oc_cliente,
                    DATE_FORMAT(p.Fec_Entrega, '%d-%m-%Y') AS Fec_Entrega,
                    DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y') AS Fec_Pedido,
                    c.RazonSocial,
                    c.Cve_Clte
           FROM th_pedido p
           LEFT JOIN c_cliente c ON c.Cve_Clte = p.Cve_clte
           WHERE p.Activo = 1 AND c.Cve_Clte = '{$cve_cliente}'
           {$ands}
           ORDER BY p.Fec_Pedido DESC
     ";
     #AND p.status = 'T'

     if($cve_proveedor)
     {
        $sql = "SELECT  p.ID_Aduana AS id_pedido,
                        p.num_pedimento AS Fol_folio,
                        IFNULL(p.Pick_Num, '') AS oc_cliente,
                        DATE_FORMAT(p.fech_pedimento, '%d-%m-%Y') AS Fec_Entrega,
                        DATE_FORMAT(p.fech_llegPed, '%d-%m-%Y') AS Fec_Pedido,
                        c.Nombre AS RazonSocial,
                        p.ID_Proveedor AS Cve_Clte
                FROM th_aduana p
                LEFT JOIN c_proveedores c ON c.ID_Proveedor = p.ID_Proveedor
                WHERE p.Activo = 1 AND p.ID_Proveedor = {$cve_proveedor}
                #AND p.status = 'T'
                ORDER BY p.Fec_Pedido DESC
         ";
     }

    if(!empty($_criterio) && !empty($cve_proveedor)){
      $sql .= " AND p.num_pedimento like '%$_criterio%';";
    }
    else if(!empty($_criterio)){
      $sql .= " AND p.Fol_folio like '%$_criterio%';";
    }
    
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
        $responce->rows[$i]['id_pedido']=$row['id_pedido'];
        $responce->rows[$i]['cell']=array($row['id_pedido'], $row['Fec_Pedido'], $row['Fol_folio'], $row['oc_cliente'], $row['Fec_Entrega'],
		     $row['RazonSocial'], $row['Cve_Clte']);
        $i++;
    }
    echo json_encode($responce);
}
