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
    $fechai= !empty($_POST['fechaInicio']) ? date('d-m-Y', strtotime($_POST['fechaInicio'])) : '';
    $fechaf= !empty($_POST['fechaFin']) ? date('d-m-Y', strtotime($_POST['fechaFin'])) : '';
    $status_interfase = $_POST['status_interfase'];
    $recepcion_interfase = $_POST['recepcion_interfase'];
    $dispositivo_interfase = $_POST['dispositivo_interfase'];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql_where = "";
    if($_criterio != '')
        $sql_where = " AND Mensaje LIKE '%$_criterio%' OR Referencia LIKE '%$_criterio%' OR Proceso LIKE '%$_criterio%' OR Dispositivo LIKE '%$_criterio%' OR Respuesta LIKE '%$_criterio%' OR Id LIKE '%$_criterio%' ";

        $sqlFecha = "";
        $SQLstatus_interfase = "";
        $SQLrecepcion_interfase = "";
        $SQLdispositivo_interfase = "";

    if($_criterio == '')
    {
        if(!empty($fechai) && !empty($fechaf)){
            if($fechai === $fechaf){
              //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') like '%$fechai%'";
                $sqlFecha .= " AND DATE_FORMAT(fecha, '%d-%m-%Y') = '$fechai' ";
            }else{
              //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') BETWEEN '$fechai' AND '$fechaf'";
                $sqlFecha .= " AND DATE(fecha) BETWEEN STR_TO_DATE('$fechai', '%d-%m-%Y') AND STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
                //$sqlFecha .= " AND DATE_FORMAT(fecha, '%d-%m-%Y') >= DATE_FORMAT('$fechai', '%d-%m-%Y') AND DATE_FORMAT(fecha, '%d-%m-%Y') <= DATE_FORMAT('$fechaf', '%d-%m-%Y') ";

            }
        }
        else{
            if(!empty($fechai)){
                //buscar por fecha mayor
                //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') >= '$fechai'";
                $sqlFecha .= " AND DATE_FORMAT(fecha, '%d-%m-%Y') >= '$fechai' ";
            }
            if(!empty($fechaf)){
                //buscar por fecha menor
                //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') <= '$fechaf'";
                $sqlFecha .= " AND DATE_FORMAT(fecha, '%d-%m-%Y') <= '$fechaf' ";
            }
        }
    
        if($status_interfase)
            $SQLstatus_interfase = " AND IF(IFNULL(Respuesta, '') = '', 2, 1) = $status_interfase ";
        if($recepcion_interfase)
            $SQLrecepcion_interfase = " AND IF(Respuesta = '{\"error\":\"OK\"}', 1, 2) = $recepcion_interfase ";
        if($dispositivo_interfase)
            $SQLdispositivo_interfase = " AND IFNULL(Dispositivo, '') = '$dispositivo_interfase' ";
    }
    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);


    $sql = "SELECT Id, fecha, IFNULL(Proceso, Referencia) AS Tipo, Dispositivo, Mensaje AS cadena_enviada,
                   'Salida de AssistPro a ERP' AS In_Out, 
                   IF(IFNULL(Respuesta, '') = '', '<span style=\"color:red;font-weight: bolder;\">Error</span>', '<span style=\"color:green;font-weight: bolder;\">Envío OK</span>') AS Estatus, 
                   IF(Respuesta = '{\"error\":\"OK\"}', '<span style=\"color:green;font-weight: bolder;\" >Recepción OK</span>', '<span style=\"color:red;font-weight: bolder;\" title=\"Error\">Error</span>') AS Recepcion,
                   Respuesta,
                    IF(IFNULL(Respuesta, '') = '', 'Error', 'Envío OK') AS EstatusButton, 
                    IF(Respuesta = '{\"error\":\"OK\"}', 'Recepción OK', 'Error') AS RecepcionButton

            FROM t_log_ws 
            WHERE 1 {$sql_where} {$sqlFecha} {$SQLstatus_interfase} {$SQLrecepcion_interfase} {$SQLdispositivo_interfase} 
            ORDER BY id DESC ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $_page, $limit;";
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
/*
1 acciones 
2 Fecha
3 Tipo
4 Medio 》Web o APK
5 IN | OUT 》 in de ERP a AssistPro  》 Out de AssistPro a ERP
6 Status 》1  Envío OK  0 》 Error
7 Recepción 1 》 Recepción OK 0 》 Error
*/
    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;
        //$json = $row['Respuesta'];
        //$json = json_decode($json, true);
        //$respuesta = $json['error'];

        $responce->rows[$i]['Id']=$row['Id'];
        $responce->rows[$i]['cell']=array('', $row['Id'], $row['fecha'], ($row['Tipo']), ($row['cadena_enviada']), ($row['Dispositivo']), ($row['In_Out']), ($row['Estatus']), ($row['Recepcion']), ($row['EstatusButton']), ($row['RecepcionButton']), ($row['Respuesta']));
        $i++;
    }
    echo json_encode($responce);
}