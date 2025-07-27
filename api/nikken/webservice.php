<?php 
include '../../config.php';
error_reporting(0);
if(isset($_POST) && !empty($_POST)){
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn,'utf8');

    switch ($_POST['action']) {

        case 'getDeliverys':
            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid
            $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
            $sord = $_POST['sord']; // get the direction
            $start = $limit * $page - $limit; // do not put $limit*($page - 1)
            $count = 0;
            $status = $_POST['status'];
            $search = $_POST['search'];

            $sqlWhere = '';

            if(!empty($status)){
                $sqlWhere .= " AND tp.status = '{$status}' ";
            }
            if(!empty($search)){
                $sqlWhere .= " AND tp.Fol_folio like '$search'";
            }


            $responce ;

            $i = 0;
            
                $sql = "SELECT  DATE_FORMAT(tp.Fec_Pedido, '%d-%m-%Y') AS fecha_factura,
                                tp.Fol_folio AS n_documento,
                                destino.postal AS codigo_postal,
                                destino.direccion AS calle,
                                destino.colonia AS colonia,
                                destino.ciudad AS ciudad,
                                destino.estado AS estado,
                                (SELECT CONCAT(UCASE(LEFT(DESCRIPCION, 1)), LCASE(SUBSTRING(DESCRIPCION, 2))) FROM cat_estados WHERE ESTADO = tp.status) AS estatus,
                                IF(tp.status = 'K', 'S', 'N') AS cancelado,
                                (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)) AS asignado,
                                (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)) AS surtido,
                                (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT Reviso FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)) AS reviso,
                                (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT empaco FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)) AS empacado,
                                (SELECT DATE_FORMAT(Hora_inicio, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1) AS fecha_surtido,
                                (SELECT DATE_FORMAT(HIR, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1) AS fecha_validacion,
                                (SELECT DATE_FORMAT(FI_Emp, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1) AS fecha_empaque,
                                cjamix.Guia AS guia,
                                rastreo.Serv_Status AS status_server,
                                (CASE WHEN tp.status = 'C' THEN
                                    IFNULL(
                                        DATEDIFF(
                                            IF(rastreo.Fec_Entrega IS NULL, NOW(), rastreo.Fec_Entrega), 
                                            (SELECT HIE FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)
                                    ), 0) 
                                ELSE
                                    '--'
                                END)
                                AS dias_transito,
                                rastreo.Fec_Entrega AS fecha_recepcion,
                                rastreo.Recibe AS persona_recepcion,
                                (SELECT 
                                    HIE 
                                FROM th_subpedido 
                                WHERE 
                                    fol_folio = tp.Fol_folio AND 
                                    cve_almac = tp.cve_almac AND 
                                    Sufijo = cjamix.Sufijo 
                                LIMIT 1
                                ) transito,
                                tp.status status
                        FROM th_pedido tp
                            LEFT JOIN th_cajamixta cjamix ON cjamix.fol_folio = tp.Fol_folio
                            LEFT JOIN Rel_PedidoDest pedi_dest ON pedi_dest.Fol_Folio = tp.Fol_folio
                            LEFT JOIN c_destinatarios destino ON destino.id_destinatario = pedi_dest.Id_Destinatario
                            LEFT JOIN c_almacenp almac ON almac.id = tp.cve_almac
                            LEFT JOIN T_RastreoGuias rastreo ON rastreo.Fol_Folio = tp.Fol_folio AND rastreo.Guia = cjamix.Guia
                        WHERE tp.Activo = '1' {$sqlWhere}  AND tp.status <> 'O' 
                            GROUP BY tp.Fec_Pedido ,tp.Fol_folio ";
				
               
                if (!($res = mysqli_query($conn, $sql))) {
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}
                $i = 0;
				
                while ($row = mysqli_fetch_array($res)) {
                    $row=array_map('utf8_encode', $row);
					
                    $circle = '<i class="fa fa-circle"></i>';
                    $dias_en_transito = '';


                    if($row['dias_transito'] < 5){
                        $dias_en_transito = "<span class='text-yellow'>{$circle}</span>";
                    }


                    if($row['dias_transito'] > 5){
                        $dias_en_transito = "<span class='text-red'>{$circle}</span>";
                    }


                    if($row['cancelado'] === 'S'){
                        $dias_en_transito = "<span class='text-blue'>{$circle}</span>";
                    }


                    if($row['status_server'] === 'CONFIRMADO'){
                        $dias_en_transito = "<span class='text-green'>{$circle}</span>";
                    }

                    
                    if( $row['server_status'] == 'CONFIRMADO') {
                        $entregado = '<span class="text-green"><i class="fa fa-check"></i></span>';
                    }
                    elseif( $row['server_status'] == 'CANCELADO') {
                        $entregado = '<span class="text-red"><i class="fa fa-ban"></i></span>';
                    }
                    else {
                        $entregado = '--';
                    }


                    if( $row['cancelado'] == 'S') {
                        $cancelado = '<i class="fa fa-check"></i>';
                    }
                    else {
                        $cancelado = '<i class="fa fa-times"></i>';
                    }


                    if(empty($row['server_status']) || $row['server_status'] === 'PENDIENTE') {
                        $almacen = '<i class="fa fa-check"></i>';
                    }
                    else {
                        $almacen = '--';
                    }


                    if($row['status'] == 'C') {
                        $transito = $row['transito'];
                        $dias_en_transito = $row['dias_transito']."    ".$dias_en_transito;
                    }
                    else {
                        $transito = '--';
                        $dias_en_transito = "--";
                    }
					$responce->rows[$i]['id']=$row['n_documento'];
                    $responce->rows[$i]['cell']= [
                        $row['fecha_factura'],      //Fecha de Factura/Entrega
                        $row['n_documento'],        //#Documento
                        $row['codigo_postal'],      //Código Postal
                        $row['calle'],              //Calle
                        $row['colonia'],            //Colonia
                        $row['ciudad'],             //Ciudad
                        $row['estado'],             //Estado
                        $row['estatus'],            //Estatus
                        $row['asignado'],           //Asignado
                        $cancelado,                 //Cancelado
                        $row['surtido'],            //Surtido
                        $row['reviso'],             //Validado
                        $row['empacado'],           //Empacado
                        $row['fecha_surtido'],      //Fecha Surtido
                        $row['fecha_validacion'],   //Fecha Validación
                        $row['fecha_empaque'],      //Fecha Empaque
                        $row['guia'],               //Guía
                        $almacen,                   //Almacén
                        $transito,                  //Tránsito
                        $entregado,                 //Entregado
                        $dias_en_transito,          //Días de Tránsito
                        $row['fecha_recepcion'],    //Fecha de Recepción
                        $row['persona_recepcion']   //Persona de Recepción
                    ];
                    
                    $i++;
                }
            
				
				$count = $i;
				if ($count >0) {
					$total_pages = ceil($count/$limit);
				}
				else {
					$total_pages = 0;
				}
				
				if ($page > $total_pages) {
					$page=$total_pages;
				}

				$responce->page = $page;
				$responce->total = $total_pages;
				$responce->records = $count;
				
				echo json_encode($responce);
				
            break;
    }
    mysqli_close($conn);
}
?>
