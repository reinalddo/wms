<?php 
include '../../../config.php'; 
 
error_reporting(0); 
 
if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
 $FolPedidoCon= $_GET["FolPedidoCon"]; 
    $FacturaMadre= $_GET["FacturaMadre"]; 
 
        
            if ($FolPedidoCon) $ands.=" and t_consolidado.Fol_PedidoCon='".$FolPedidoCon."' AND td_consolidado.Fact_Madre='".$FacturaMadre."' "; 
 
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
 
    $sqlCount = 'SELECT 
                count(t_consolidado.Fol_PedidoCon) as total 
                FROM 
                t_consolidado 
                INNER JOIN th_consolidado ON t_consolidado.Fol_PedidoCon = th_consolidado.Fol_PedidoCon 
                INNER JOIN td_consolidado ON th_consolidado.Fol_PedidoCon = td_consolidado.Fol_PedidoCon And t_consolidado.Fol_Folio=td_consolidado.Fol_Folio 
                INNER JOIN th_pedido ON td_consolidado.Fol_Folio = th_pedido.Fol_folio 
                INNER JOIN td_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio 
                LEFT JOIN c_cliente ON th_pedido.Cve_clte = c_cliente.Cve_Clte where 0=0 '.$ands.' '; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 
 
         
 
    $sql = 'SELECT 
                t_consolidado.Fol_PedidoCon, 
                c_cliente.RazonSocial, 
                c_cliente.RazonComercial, 
                td_consolidado.Fact_Madre, 
                td_consolidado.Fol_Folio AS facturahija, 
                td_consolidado.No_OrdComp, 
                td_consolidado.Tot_Cajas, 
                td_pedido.Num_cantidad, 
                t_consolidado.Fol_PedidoCon as Fol_PedidoCon1, 
                c_cliente.CalleNumero, 
                c_cliente.Ciudad, 
                (SELECT CEIL(art.num_multiplo/td_p.Num_cantidad) as cajas FROM td_pedido as td_p INNER JOIN c_articulo as art ON td_p.Cve_articulo = art.cve_articulo Where td_pedido.Fol_folio = td_p.Fol_folio) as cajas, 
                th_consolidado.Nom_CteCon 
                FROM 
                t_consolidado 
                INNER JOIN th_consolidado ON t_consolidado.Fol_PedidoCon = th_consolidado.Fol_PedidoCon 
                INNER JOIN td_consolidado ON th_consolidado.Fol_PedidoCon = td_consolidado.Fol_PedidoCon And t_consolidado.Fol_Folio=td_consolidado.Fol_Folio 
                INNER JOIN th_pedido ON td_consolidado.Fol_Folio = th_pedido.Fol_folio 
                INNER JOIN td_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio 
                LEFT JOIN c_cliente ON th_pedido.Cve_clte = c_cliente.Cve_Clte where 0=0 '.$ands.' '; 
 
 
    $sql .= " LIMIT $page,$limit; "; 
 
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 
 
    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res)) { 
        $row = array_map('utf8_encode', $row); 
        $data[] = $row; 
        $i++; 
    }  
         
    mysqli_close(); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_GET["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data 
    );  
    echo json_encode($output); 
}