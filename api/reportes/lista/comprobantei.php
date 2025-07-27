<?php
include '../../../config.php';

error_reporting(0);
 
if(isset($_GET) && !empty($_GET)){
    $page = $_GET['start'];
    $limit = $_GET['length']; 
    $search = $_GET['search']['value'];
    $fecha = $_GET['fecha'];
    $almacen = $_GET['almacen'];
 
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $ands = "";
    //if(isset($fecha) && !empty($fecha)){
    //    $ands = " WHERE ce.Fec_Entrada like '%$fecha%' ";
    //}
/*
    $sqlCount = "SELECT count(ce.Fol_Folio) AS total FROM th_entalmacen ce {$ands};";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }        
*/
    $sql = "SELECT  ce.Fol_Folio AS numero_recepcion,
                    DATE_FORMAT(ce.Fec_Entrada, '%d-%m-%Y') AS fecha_ingreso,
                    p.descripcion AS protocolo,
                    ce.fol_oep AS numero_pedimiento,
                    'Factura' AS manifiesto,
                    de.cve_articulo AS numero_parte,
                    ar.des_articulo AS descripcion,
                    de.CantidadRecibida AS cantidad,
                    sum(ar.peso * de.CantidadRecibida) AS peso,
                    c.des_cia AS empresa
            FROM td_entalmacen de
            INNER JOIN th_entalmacen ce on ce.Fol_Folio = de.fol_folio
            INNER JOIN th_aduana ad on ad.num_pedimento = de.Fol_Folio
            INNER JOIN c_articulo ar on ar.cve_articulo = de.cve_articulo
            INNER JOIN c_usuario u on ad.cve_usuario = u.id_user
            INNER JOIN c_compania c on c.cve_cia = u.cve_cia
            INNER JOIN t_protocolo p on ad.ID_Protocolo = p.ID_Protocolo
            WHERE ce.Cve_Almac = '$almacen'
            GROUP BY de.cve_articulo, ce.Fol_Folio
            ORDER BY ce.Fol_Folio DESC
            ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    $count = mysqli_num_rows($res);

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