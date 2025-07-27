<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class MonitorEntregasNikkenController extends Controller
{

    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
        $status = Capsule::select( 
                        Capsule::raw("SELECT 
                                ESTADO AS status, 
                                CONCAT(UCASE(LEFT(DESCRIPCION, 1)),
                                LCASE(SUBSTRING(DESCRIPCION, 2))) AS descripcion 
                            FROM cat_estados 
                            WHERE ESTADO <> '*'
                            ORDER BY descripcion ASC
                       ")
                    );

        if($_SESSION['perfil_usuario'] == 1){
            $retrasados = Capsule::select( 
                Capsule::raw("SELECT 
                                rastreo.Serv_Status status,
                                subp.HIE embarco, 
                                DATEDIFF(
                                    NOW(), 
                                subp.HIE
                            ) AS dias_transito
                            FROM th_subpedido subp
                                LEFT JOIN th_cajamixta cjamix ON cjamix.fol_folio = subp.Fol_folio
                                LEFT JOIN T_RastreoGuias rastreo ON rastreo.Fol_Folio = subp.Fol_folio AND rastreo.Guia = cjamix.Guia
                            WHERE subp.`status` = 'C' AND 
                                DATEDIFF(
                                    IF(rastreo.Serv_Status != 'ENTREGADO', NOW(), rastreo.Fec_Entrega) ,subp.HIE
                                ) >= 4
            ")
            );
            //$retrasados = [1,2];
        }
        else {
            $retrasados = [];
        }
        
        
        return new View('dashboards.monitoreo_entrega_nikken', compact([
            'status', 'retrasados'
        ]));
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function paginate()
    {
        
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $page    = $this->getInput('page', 1); // get the requested page
        $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
        $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord    = $this->getInput('sord'); // get the direction
        $status  = $this->getInput('status');
        $search  = $this->getInput('search');

        $avanzado  = (int) $this->getInput('avanzado', 0);

        $fecha_inicio  = $this->getInput('fecha_inicio');
        $fecha_fin  = $this->getInput('fecha_fin');
        $status_entrega  = $this->getInput('status_entrega');

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        $count = 0;

        $sqlWhere = '';

        if($avanzado == 0 AND !empty($status)){
            $sqlWhere .= " AND tp.status = '{$status}' ";
        }

        if($avanzado == 0 AND !empty($search) AND strlen($search) > 0){
            $sqlWhere .= " AND tp.Fol_folio LIKE '{$search}'";
        }

        
        if($avanzado == 1 AND !empty($fecha_inicio) AND !empty($fecha_fin)){
            $sqlWhere .= " AND tp.Fec_Pedido BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        }

        if($avanzado == 1 AND !empty($status_entrega)){
            switch ($status_entrega) {
                // Pedido en almacén
                case 1: $sqlWhere .= " AND (tp.status <> 'K' AND 
                                                tp.status <> 'E' AND 
                                                tp.status <> 'F' AND 
                                                tp.status <> 'T'
                                            ) AND (tp.Fol_Folio NOT IN (SELECT Fol_Folio FROM T_RastreoGuias)) "; break;
                
                // Pedido en tránsito
                case 2: $sqlWhere .= " AND (tp.status <> 'K') AND 
                                        rastreo.Serv_Status = 'EN_TRANSITO' AND 
                                        tp.Fol_Folio IN (SELECT Fol_Folio FROM T_RastreoGuias) AND
                                        DATEDIFF(
                                                NOW(), 
                                                (SELECT
                                                        HIE 
                                                    FROM th_subpedido 
                                                    WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo
                                                )
                                            ) <= 5
                "; break; 

                // Pedido entregado
                case 3: $sqlWhere .= " AND rastreo.Serv_Status = 'CONFIRMADO' AND IFNULL(rastreo.Fec_Entrega,'')<>'' And IfNull(rastreo.Recibe,'')<>''"; break; 
                
                
                // Pedido atrasado
                case 4: $sqlWhere .= " AND tp.status = 'C' AND 

                                            rastreo.Serv_Status = 'EN_TRANSITO' AND

                                            DATEDIFF(
                                                IF(rastreo.Serv_Status <> 'ENTREGADO', NOW(), rastreo.Fec_Entrega), 
                                                (SELECT HIE FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo)
                                            ) >= 5
                                            ";
                break; 
            }
        }
        
        
        $data = Capsule::select( 
                Capsule::raw("SELECT  DATE_FORMAT(tp.Fec_Pedido, '%d-%m-%Y') AS fecha_factura,
                    tp.Fol_folio AS n_documento,
                    IFNULL(destino.postal, '--') AS codigo_postal,
                    IFNULL(destino.direccion, '--') AS calle,
                    IFNULL(destino.colonia, '--') AS colonia,
                    IFNULL(destino.ciudad, '--') AS ciudad,
                    IFNULL(destino.estado, '--') AS estado,
                    IFNULL((SELECT CONCAT(UCASE(LEFT(DESCRIPCION, 1)), LCASE(SUBSTRING(DESCRIPCION, 2))) FROM cat_estados WHERE ESTADO = tp.status), '--') AS estatus,
                    IF(tp.status = 'K', 'S', 'N') AS cancelado,
                    IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)), '--') AS asignado,
                    IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)), '--') AS surtido,
                    IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT Reviso FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)), '--') AS reviso,
                    IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT empaco FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)), '--') AS empacado,
                    IFNULL((SELECT DATE_FORMAT(Hora_inicio, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1), '--') AS fecha_surtido,
                    IFNULL((SELECT DATE_FORMAT(HIR, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1), '--') AS fecha_validacion,
                    IFNULL((SELECT DATE_FORMAT(FI_Emp, '%d-%m-%Y %H:%i:%s') FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1), '--') AS fecha_empaque,
                    IFNULL(cjamix.Guia, '--') AS guia,
                    IFNULL(rastreo.Serv_Status, '--') AS status_server,
                    (CASE WHEN rastreo.Serv_Status = 'EN_TRANSITO' THEN
                        IFNULL(
                            DATEDIFF(
                                IF(rastreo.Fec_Recoleccion  IS NULL, NOW(), rastreo.Fec_Recoleccion ), 
                                (SELECT HIE FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1)
                        ), 0) 
                    ELSE
                        '--'
                    END)
                    AS dias_transito,
                    IFNULL(rastreo.Fec_Entrega, '--') AS fecha_recepcion,
                    IFNULL(rastreo.Recibe, '--') AS persona_recepcion,
                    IFNULL((SELECT 
                            HIE 
                        FROM th_subpedido 
                        WHERE 
                            fol_folio = tp.Fol_folio AND 
                            cve_almac = tp.cve_almac AND 
                            Sufijo = cjamix.Sufijo 
                        LIMIT 1
                        ), '--') transito,
                    IFNULL(tp.status, '--') status
                FROM th_pedido tp
                    LEFT JOIN th_cajamixta cjamix ON cjamix.fol_folio = tp.Fol_folio
                    LEFT JOIN Rel_PedidoDest pedi_dest ON pedi_dest.Fol_Folio = tp.Fol_folio
                    LEFT JOIN c_destinatarios destino ON destino.id_destinatario = pedi_dest.Id_Destinatario
                    LEFT JOIN c_almacenp almac ON almac.id = tp.cve_almac
                    LEFT JOIN T_RastreoGuias rastreo ON rastreo.Fol_Folio = tp.Fol_folio AND rastreo.Guia = cjamix.Guia
                WHERE tp.Activo = '1' {$sqlWhere}  AND tp.status <> 'O' 
                    GROUP BY tp.Fec_Pedido ,tp.Fol_folio"
            )
        );

        $circle = '<i class="fa fa-circle"></i>';
        $response = new \stdClass;
        $response->data = [];

        $procesados = 0;
        $count = count($data);

        foreach ($data as $row) {
            

            //$row = array_map('utf8_encode', $row);            
            
            $dias_en_transito = '';

            if($row->dias_transito <=3){
                $dias_en_transito = "<span class='text-yellow'>{$circle}</span>";
            }

            if($row->dias_transito >= 4){
                $dias_en_transito = "<span class='text-red'>{$circle}</span>";
            }

            if($row->cancelado === 'S'){
                $dias_en_transito = "<span class='text-blue'>{$circle}</span>";
            }

            if($row->status_server === 'CONFIRMADO'){
                $dias_en_transito = "<span class='text-green'>{$circle}</span>";
            }
            
            if( $row->server_status == 'CONFIRMADO' OR ($row->fecha_recepcion !='' OR $row->persona_recepcion !='')) {
                $entregado = '<span class="text-green"><i class="fa fa-check"></i></span>';
            }
            elseif( $row->server_status == 'CANCELADO') {
                $entregado = '<span class="text-red"><i class="fa fa-ban"></i></span>';
            }
            else {
                $entregado = '--';
            }


            if( $row->cancelado == 'S') {
                $cancelado = '<i class="fa fa-check"></i>';
            }
            else {
                $cancelado = '<i class="fa fa-times"></i>';
            }


            if(empty($row->server_status) || $row->server_status === 'PENDIENTE') {
                $almacen = '<i class="fa fa-check"></i>';
            }
            else {
                $almacen = '--';
            }


            if($row->status == 'C') {
                $transito = $row->transito;
                $dias_en_transito = $row->dias_transito."    ".$dias_en_transito;
            }
            else {
                $transito = '--';
                $dias_en_transito = "--";
            }

            if($row->server_status == 'EN_TRANSITO') {
                $transito = '<span class="text-green"><i class="fa fa-check"></i></span>';
            }

            /*if( !empty($status_entrega) AND $status_entrega = 1 ){
                $transito = "<span class='text-red'>{$transito}</span>";
            }
            if( !empty($status_entrega) AND $status_entrega = 2 ){
                $transito = "<span class='text-red'>{$transito}</span>";
            }
            if( !empty($status_entrega) AND $status_entrega = 3 ){ //Pedido entregado.
                $dias_en_transito = "<span class='text-red'>{$transito}</span>";
            }
            if( !empty($status_entrega) AND $status_entrega = 4 ){
                $transito = "<span class='text-red'>{$transito}</span>";
            }*/

            //$response->rows[$count]['id'] = $row->n_documento;
            $response->data[] = [
                'fecha_factura' => $row->fecha_factura,     //Fecha de Factura/Entrega
                'n_documento' => $row->n_documento,         //#Documento
                'codigo_postal' => $row->codigo_postal,     //Código Postal
                'calle' => $row->calle,                     //Calle
                'colonia' => $row->colonia,                 //Colonia
                'ciudad' => $row->ciudad,                   //Ciudad
                'estado' => $row->estado,                   //Estado
                'estatus' => $row->estatus,                 //Estatus
                'asignado' => $row->asignado,               //Asignado
                'cancelado' => $cancelado,                  //Cancelado
                'surtido' => $row->surtido,                 //Surtido
                'reviso' => $row->reviso,                   //Validado
                'empacado' => $row->empacado,               //Empacado
                'fecha_surtido' => $row->fecha_surtido,         //Fecha Surtido
                'fecha_validacion' => $row->fecha_validacion,   //Fecha Validación
                'fecha_empaque' => $row->fecha_empaque,     //Fecha Empaque
                'guia' => $row->guia,                       //Guía
                'almacen' => $almacen,                      //Almacén
                'transito' => $transito,                    //Tránsito
                'entregado' => $entregado,                  //Entregado
                'dias_en_transito' => $dias_en_transito,    //Días de Tránsito
                'fecha_recepcion' => $row->fecha_recepcion,    //Fecha de Recepción
                'persona_recepcion' => $row->persona_recepcion //Persona de Recepción
            ];

            $procesados++;

        }
            

        $response->data = array_slice($response->data, $start, $limit);

        if ($count >0) {
            $total_pages = ceil($count/$limit);
        }
        else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $response->from = ($start == 0 ? 1 : $start) ;
        $response->to = ($start + $limit);
        $response->page = $page;
        $response->total_pages = $total_pages;
        $response->total = $count;
        $response->status = 200;

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response,JSON_PRETTY_PRINT);exit;		
            
    }





    /**
     * Undocumented function
     *
     * @return void
     */
    public function retrasados()
    {
        
        $page    = $this->getInput('page', 1); // get the requested page
        $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
        $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord    = $this->getInput('sord'); // get the direction
        $status  = $this->getInput('status');
        $search  = $this->getInput('search');

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        $count = 0;

        $sqlWhere = '';

        if(!empty($status)){
            $sqlWhere .= " AND tp.status = '{$status}' ";
        }
        if(!empty($search)){
            $sqlWhere .= " AND tp.Fol_folio LIKE '$search'";
        }
        $mes_pasado = date('Y-m-d',strtotime("-1 month")) ;
        $data = Capsule::select( 
                Capsule::raw("SELECT  DATE_FORMAT(tp.Fec_Pedido, '%d-%m-%Y') AS fecha_factura,
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
                WHERE 
                    tp.Activo = '1' AND
                    tp.`status` = 'C' AND 
                    rastreo.Serv_Status = 'EN_TRANSITO' AND

                    DATEDIFF(
                        IF(rastreo.Serv_Status <> 'ENTREGADO', NOW(), rastreo.Fec_Entrega), 
                        (SELECT HIE FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo)
                    ) >= 5 AND

                    (SELECT HIE FROM th_subpedido WHERE fol_folio = tp.Fol_folio AND cve_almac = tp.cve_almac AND Sufijo = cjamix.Sufijo LIMIT 1) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
                GROUP BY tp.Fec_Pedido ,tp.Fol_folio"
            )
        );


        $count = 0;
        $circle = '<i class="fa fa-circle"></i>';
		$response = new \stdClass;
        foreach ($data as $row) {
            //$row = array_map('utf8_encode', $row);            
            
            $dias_en_transito = '';

            if($row->dias_transito < 5){
                $dias_en_transito = "<span class='text-yellow'>{$circle}</span>";
            }

            if($row->dias_transito > 5){
                $dias_en_transito = "<span class='text-red'>{$circle}</span>";
            }

            if($row->cancelado === 'S'){
                $dias_en_transito = "<span class='text-blue'>{$circle}</span>";
            }

            if($row->status_server === 'CONFIRMADO'){
                $dias_en_transito = "<span class='text-green'>{$circle}</span>";
            }
            
            if( $row->server_status == 'CONFIRMADO') {
                $entregado = '<span class="text-green"><i class="fa fa-check"></i></span>';
            }
            elseif( $row->server_status == 'CANCELADO') {
                $entregado = '<span class="text-red"><i class="fa fa-ban"></i></span>';
            }
            else {
                $entregado = '--';
            }


            if( $row->cancelado == 'S') {
                $cancelado = '<i class="fa fa-check"></i>';
            }
            else {
                $cancelado = '<i class="fa fa-times"></i>';
            }


            if(empty($row->server_status) || $row->server_status === 'PENDIENTE') {
                $almacen = '<i class="fa fa-check"></i>';
            }
            else {
                $almacen = '--';
            }


            if($row->status == 'C') {
                $transito = $row->transito;
                $dias_en_transito = $row->dias_transito."    ".$dias_en_transito;
            }
            else {
                $transito = '--';
                $dias_en_transito = "--";
            }

            $response->rows[$count]['id'] = $row->n_documento;
            $response->rows[$count]['cell'] = [
                $row->fecha_factura,      //Fecha de Factura/Entrega
                $row->n_documento,        //#Documento
                $row->codigo_postal,      //Código Postal
                $row->calle,              //Calle
                $row->colonia,            //Colonia
                $row->ciudad,             //Ciudad
                $row->estado,             //Estado
                $row->estatus,            //Estatus
                $row->asignado,           //Asignado
                $cancelado,                 //Cancelado
                $row->surtido,            //Surtido
                $row->reviso,             //Validado
                $row->empacado,           //Empacado
                $row->fecha_surtido,      //Fecha Surtido
                $row->fecha_validacion,   //Fecha Validación
                $row->fecha_empaque,      //Fecha Empaque
                $row->guia,               //Guía
                $almacen,                   //Almacén
                $transito,                  //Tránsito
                $entregado,                 //Entregado
                $dias_en_transito,          //Días de Tránsito
                $row->fecha_recepcion,    //Fecha de Recepción
                $row->persona_recepcion   //Persona de Recepción
            ];                    
            $count++;
        }
            

        if ($count >0) {
            $total_pages = ceil($count/$limit);
        }
        else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page=$total_pages;
        }

        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response,JSON_PRETTY_PRINT);exit;		
            
    }


}
