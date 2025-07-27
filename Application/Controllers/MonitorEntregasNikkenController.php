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
  
    public function calcular()
    {
      echo "Calc";
    }

    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
        $status = Capsule::select
        (
          Capsule::raw
          ("
            SELECT 
            ESTADO AS status, 
            CONCAT(UCASE(LEFT(DESCRIPCION, 1)),
            LCASE(SUBSTRING(DESCRIPCION, 2))) AS descripcion 
            FROM cat_estados 
            WHERE ESTADO NOT IN ('*','E','T','F')
            ORDER BY ORDEN ASC
          ")
        );
        $db2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_select_db($db2, DB_NAME);
      
        $sql = "SELECT count(*) 
                FROM ( SELECT  	fec_pedido  AS `Fecha de Factura/Entrega`,
                fol_folio   AS `Documento`,
                postal      AS `C.P.`,
                direccion   AS `Calle/Num Destino`,
                colonia     AS `Colonia Destino`,
                ciudad      AS `Ciudad o Municipio de Envio`,
                estado      AS `Estado de Envio`,
                (CASE WHEN (`status` = 'K') THEN 'Cancelado' 
                ELSE IFNULL (CONVERT ((CASE WHEN (`serv_status` = 'EN_TRANSITO') 
                THEN 'En Transito' WHEN (`serv_status` = 'CONFIRMADO')
                THEN 'Entregado' 					        ELSE `serv_status` 
                END) USING utf8 ),
                (CASE WHEN (`status` IN ( 'T', 'E', 'F' )) THEN 'En Transito' ELSE `descripcion` END)) END) AS `Status`,
                u_asig AS `Asignado`,
                (CASE WHEN (`status` = 'K') THEN '1'  ELSE '0' END) AS `Cancelado`,
                u_asig AS `Surtido`,
                u_revi AS `Validado`,
                u_empa AS `Empacado`,
                `hora_inicio` AS `Fecha y hora de surtido`,
                `hir` AS `Fecha y hora de Validacion`,
                `fi_emp` AS `Fecha y hora de Empaque`,
                IFNULL ( guia, guia_caja ) AS `Guia`,
                (CASE WHEN ( `status` IN ( 'C', 'T', 'E', 'F' ) ) THEN '0' ELSE '1' END) AS `Almacen`,
                IFNULL(`fec_recoleccion`,IFNULL(`hie`,`fi_emp`)) AS `Transito`,
                (CASE WHEN ( IFNULL(`serv_status`, '') = 'CONFIRMADO' ) THEN '1' ELSE '0' END )	AS `Entregado`,
                IFNULL 	((TO_DAYS(IFNULL(`fec_entrega`,`get_date`()))	-TO_DAYS (IFNULL (`fec_recoleccion`,
                IFNULL (`hie`,`fi_emp`)))), 0) AS `Dias de Transito`,
                `fec_entrega`	AS `Fecha de Recepcion`,
                `recibe` AS `Persona de Recepcion`
                from __vt_monitoreo) x  where Entregado = 0 and `Dias de Transito` >= 4 and Status <> 'Cancelado'";
        $retrasados = mysqli_query($db2, $sql);
        return new View('dashboards.monitoreo_entrega_nikken', compact(['status', 'retrasados']));
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function paginateOLD()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $search  = $this->getInput('search');
        $status  = $this->getInput('status');
        $page    = $this->getInput('page', 1); // get the requested page
        $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
        $fecha_inicio  = $this->getInput('fecha_inicio');
        $fecha_fin  = $this->getInput('fecha_fin');
        $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord    = $this->getInput('sord'); // get the direction
        $status_entrega  = $this->getInput('status_entrega');
      
        $queryWhere="where 1";
        switch($status_entrega){
          case 1:
            $queryWhere .= "  and almacen = 1";
            switch($status){
              case 'I':
                $queryWhere .= " and Status = 'editando'";
              break;
              case 'A':
                $queryWhere .= " and Status = 'listo por asignar'";
              break;
              case 'S':
                $queryWhere .= " and Status = 'surtiendo'";
              break;
              case 'L':
                $queryWhere .= " and Status = 'pendiente de auditar'";
              break;
              case 'R':
                $queryWhere .= " and Status = 'auditando'";
              break;
              case 'P':
                $queryWhere .= " and Status = 'pendiente de empaque'";
              break;
              case 'M':
                $queryWhere .= " and Status = 'empacando'";
              break;
              case 'C':
                $queryWhere .= " and Status = 'pendiente de embarque'";
              break;
              case 'K':
                $queryWhere .= " and Status = 'Cancelado'";
              break;
            }
            break; 
            
           case 2:
            $queryWhere .= " and Entregado = 0 and Almacen = 0 and Status = 'En Transito'";
            break;
            
         /* case 2:
            $queryWhere .= " and Entregado = 0 and Almacen = 0 and `Transito` <> null";
            break;*/
            
          case 3:
            $queryWhere .= " and Entregado = 1";
            break;
          case 4:
            $queryWhere .= " and Status = 'En Transito' and Entregado = 0 and `Dias de Transito` >= 4 and Status <> 'Cancelado'";
            break;
        }
        
        if($search != "")
          $queryWhere .=" and Documento like '%".$search."%'";
      
        if($fecha_inicio != '')
        {
          $date_inicio=date("Y-m-d H:i:s",strtotime($fecha_inicio));
          $queryWhere .= " and `fec_pedido` >= DATE('".$date_inicio."') ";
        }
      
         if($fecha_fin != '')
        {
          $date_fin=date("Y-m-d H:i:s",strtotime($fecha_fin)); 
          $queryWhere .= " and `fec_pedido` <= DATE('".$date_fin."')"; 
        }
    
        //echo var_dump($fecha_inicio, $fecha_fin );
        //die();
       
      
        //die; 
        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        $count = 0;

        //$sqlWhere = '';
      
        $db2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_select_db($db2, DB_NAME);
      
        // TODO - EFICIENTAR
        $sql = "SELECT * FROM 
               (SELECT
               fec_pedido,
               DATE_FORMAT(fec_pedido,'%d-%m-%Y')  AS `Fecha de Factura/Entrega`,
               fol_folio   AS `Documento`, 	
               postal      AS `C.P.`, 	
               direccion   AS `Calle/Num Destino`, 	
               colonia     AS `Colonia Destino`, 	
               ciudad      AS `Ciudad o Municipio de Envio`, 	
               estado      AS `Estado de Envio`, 	
               (CASE WHEN ( `status` = 'K' ) THEN 'Cancelado' 
               ELSE IFNULL (CONVERT ( (CASE WHEN ( `serv_status` = 'EN_TRANSITO') THEN 'En Transito' 
               WHEN ( `serv_status` = 'CONFIRMADO') THEN 'Entregado' ELSE `serv_status` END) 
               USING utf8 ),
               (CASE WHEN ( `status` IN ( 'T', 'E', 'F' ) ) THEN 'En Transito' 
               ELSE `descripcion` END) ) END) AS `Status`,
               u_asig	AS `Asignado`,
               (CASE WHEN ( `status` = 'K' ) THEN '1'  ELSE '0' END) AS `Cancelado`,
               u_asig	AS `Surtido`,
               u_revi	AS `Validado`,
               u_empa AS `Empacado`,
               DATE_FORMAT(`hora_inicio`,'%d-%m-%Y %h:%i:%s') AS `Fecha y hora de surtido`,
               DATE_FORMAT(`hir`,'%d-%m-%Y %h:%i:%s') AS `Fecha y hora de Validacion`,
               DATE_FORMAT(`fi_emp`,'%d-%m-%Y %h:%i:%s')	AS `Fecha y hora de Empaque`,
               IFNULL ( guia, guia_caja )	AS `Guia`,
               (CASE WHEN ( `status` IN ( 'C', 'T', 'E', 'F' ) ) THEN '0' ELSE '1' END) AS `Almacen`,  
               IFNULL (DATE_FORMAT(`fec_recoleccion`,'%d-%m-%Y'),IFNULL(DATE_FORMAT(`hie`,'%d-%m-%Y'),DATE_FORMAT(`fi_emp`,'%d-%m-%Y')))	AS `Transito`,
               (CASE WHEN ( IFNULL(`serv_status`, '') = 'CONFIRMADO' ) THEN '1' ELSE '0' END )	AS `Entregado`,
               IFNULL 	( ( TO_DAYS(IFNULL(`fec_entrega`,`get_date`())) - TO_DAYS(IFNULL(
               `fec_recoleccion`,
               IFNULL(`hie`,`fi_emp`))) ) , 0 ) AS `Dias de Transito`,
               DATE_FORMAT(`fec_entrega`,'%d-%m-%Y') AS `Fecha de Recepcion`,
               `recibe` AS `Persona de Recepcion`
               from __vt_monitoreo) x ".$queryWhere."
               ORDER BY `fec_pedido` DESC
               limit ".$limit." OFFSET ".$start;
      
      
        $sql_count = "SELECT count(*) as x FROM 
               (SELECT
               fec_pedido,
               DATE_FORMAT(fec_pedido,'%d-%m-%Y')  AS `Fecha de Factura/Entrega`,
               fol_folio   AS `Documento`, 	
               postal      AS `C.P.`, 	
               direccion   AS `Calle/Num Destino`, 	
               colonia     AS `Colonia Destino`, 	
               ciudad      AS `Ciudad o Municipio de Envio`, 	
               estado      AS `Estado de Envio`, 	
               (CASE WHEN ( `status` = 'K' ) THEN 'Cancelado' 
               ELSE IFNULL (CONVERT ( (CASE WHEN ( `serv_status` = 'EN_TRANSITO') THEN 'En Transito' 
               WHEN ( `serv_status` = 'CONFIRMADO') THEN 'Entregado' ELSE `serv_status` END) 
               USING utf8 ),
               (CASE WHEN ( `status` IN ( 'T', 'E', 'F' ) ) THEN 'En Transito' 
               ELSE `descripcion` END) ) END) AS `Status`,
               u_asig	AS `Asignado`,
               (CASE WHEN ( `status` = 'K' ) THEN '1'  ELSE '0' END) AS `Cancelado`,
               u_asig	AS `Surtido`,
               u_revi	AS `Validado`,
               u_empa AS `Empacado`,
               DATE_FORMAT(`hora_inicio`,'%d-%m-%Y') AS `Fecha y hora de surtido`,
               DATE_FORMAT(`hir`,'%d-%m-%Y') AS `Fecha y hora de Validacion`,
               DATE_FORMAT(`fi_emp`,'%d-%m-%Y')	AS `Fecha y hora de Empaque`,
               IFNULL ( guia, guia_caja )	AS `Guia`,
               (CASE WHEN ( `status` IN ( 'C', 'T', 'E', 'F' ) ) THEN '0' ELSE '1' END) AS `Almacen`,  
               IFNULL (DATE_FORMAT(`fec_recoleccion`,'%d-%m-%Y'),IFNULL(DATE_FORMAT(`hie`,'%d-%m-%Y'),DATE_FORMAT(`fi_emp`,'%d-%m-%Y')))	AS `Transito`,
               (CASE WHEN ( IFNULL(`serv_status`, '') = 'CONFIRMADO' ) THEN '1' ELSE '0' END )	AS `Entregado`,
               IFNULL 	( ( TO_DAYS(IFNULL(`fec_entrega`,`get_date`())) - TO_DAYS(IFNULL(
               `fec_recoleccion`,
               IFNULL(`hie`,`fi_emp`))) ) , 0 ) AS `Dias de Transito`,
               DATE_FORMAT(`fec_entrega`,'%d-%m-%Y') AS `Fecha de Recepcion`,
               `recibe` AS `Persona de Recepcion`
               from __vt_monitoreo) x ".$queryWhere."
               ORDER BY `fec_pedido` DESC";
        
        //$sql = "Select * from th_pedido";
      
        //echo var_dump ($sql_count);
        //die();
      
        
      
        $res = mysqli_query($conn, $sql);
      
        $res_count = mysqli_query($conn, $sql_count);
      
        $counter = mysqli_fetch_array($res_count);

        $circle = '<i class="fa fa-circle"></i>';
        $response = new \stdClass;
        $response->data = [];
      
        $response->sql = $sql;

        $procesados = 0;
        //$count = $counter["x"];//count($data);
        $total_pages = ceil($counter["x"]/$limit);
        $cancelado ="";
        $almacen="";
        $entregado="";

        while ($row = mysqli_fetch_array($res)) {

          if($row["Entregado"] == 1)
            $dias_en_transito = "<span class='text-green'>{$circle}</span>";
          else
            if($row["Dias de Transito"] >= 4 )
              $dias_en_transito = "<span class='text-red'>{$circle}</span>";
            else
              $dias_en_transito = "<span class='text-yellow'>{$circle}</span>";
              
          
            if($row["Cancelado"] == 0)
              $cancelado = '<i class="fa fa-times"></i>';
            else
              $cancelado = '<i class="fa fa-check"></i>';
            if($row["Almacen"] == 0)
              $almacen = '<i class="fa fa-times"></i>';
            else
              $almacen = '<i class="fa fa-check"></i>';
            if($row["Entregado"] == 0)
              $entregado = '<i class="fa fa-times"></i>';
            else
              $entregado = '<span class="text-green"><i class="fa fa-check"></i></span>';
          
            $response->data[] = [
                'fecha_factura' =>       date_format(date_create($row['Fecha de Factura/Entrega']),"d-m-Y "),     //Fecha de Factura/Entrega
                'n_documento' =>        $row["Documento"],         //#Documento
                'codigo_postal' =>      $row["C.P."],     //Código Postal
                'calle' =>              utf8_encode($row["Calle/Num Destino"]),                     //Calle
                'colonia' =>            utf8_encode($row["Colonia Destino"]),                 //Colonia
                'ciudad' =>             utf8_encode( $row["Ciudad o Municipio de Envio"]),                   //Ciudad
                'estado' =>             utf8_encode($row["Estado de Envio"]),                   //Estado
                'estatus' =>            $row["Status"],                 //Estatus
                'asignado' =>           utf8_encode($row["Asignado"]),               //Asignado
                'cancelado' =>          $cancelado,                  //Cancelado
                'surtido' =>            utf8_encode($row["Surtido"]),                 //Surtido
                'reviso' =>             utf8_encode($row["Validado"]),                   //Validado
                'empacado' =>           utf8_encode($row["Empacado"]),               //Empacado
                'fecha_surtido' =>      ($row["Fecha y hora de surtido"] != null)?$row["Fecha y hora de surtido"]:"",         //Fecha Surtido
                'fecha_validacion' =>   ($row["Fecha y hora de Validacion"] != null)?$row["Fecha y hora de Validacion"]:"",   //Fecha Validación
                'fecha_empaque' =>      ($row["Fecha y hora de Empaque"] != null)?$row["Fecha y hora de Empaque"]:"",     //Fecha Empaque
                'guia' =>               ($row["Guia"] != null)?$row["Guia"]:"",                       //Guía
                'almacen' =>            $almacen,                      //Almacén
                'transito' =>           ($row["Transito"] != null)?$row["Transito"]:"",                    //Tránsito
                'entregado' =>          $entregado,                  //Entregado
                'dias_en_transito' =>   $row["Dias de Transito"]."-".$dias_en_transito,    //Días de Tránsito
                'fecha_recepcion' =>    ($row["Fecha de Recepcion"] != null)?$row["Fecha de Recepcion"]:"",    //Fecha de Recepción
                'persona_recepcion' =>  utf8_encode($row["Persona de Recepcion"]), //Persona de Recepción
            ];
            $procesados++;
            $count++;
        }

        $response->data = array_slice($response->data, $start, $limit);

        /*if ($count >0) {
            $total_pages = ceil($count/$limit);
        }
        else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page = $total_pages;
        }*/

        $response->from = ($start == 0 ? 1 : $start) ;
        $response->to = ($start + $limit);
        $response->page = $page;
        $response->total_pages = $total_pages;
        $response->total = $counter["x"];
        $response->status = 200;
        $response->query_completa = $sql;
        $response->counter = $counter["x"];

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response,JSON_PRETTY_PRINT);exit;		
    }
  
//*****************************************************************************************
//*****************************************************************************************
    public function paginate()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $search  = $this->getInput('search');
        $status  = $this->getInput('status');
        $page    = $this->getInput('page', 1); // get the requested page
        $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
        $fecha_inicio  = $this->getInput('fecha_inicio');
        $fecha_fin    = $this->getInput('fecha_fin');
        $cve_cliente  = $this->getInput('cve_cliente');
        $cve_proveedor  = $this->getInput('cve_proveedor');
        $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord    = $this->getInput('sord'); // get the direction
        $status_entrega  = $this->getInput('status_entrega');
        $mesas_revision  = $this->getInput('mesas_revision');
        $id_almacen  = $this->getInput('id_almacen');
      
        //$queryWhere = " WHERE p.Fol_folio NOT IN (SELECT Folio_Pro FROM t_ordenprod) ";
        $queryWhere = " WHERE p.TipoPedido != 'T' AND p.cve_almac = $id_almacen";
        if($search != "")
            $queryWhere .=" AND (p.Fol_folio LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%' OR tdor.ID_OEmbarque LIKE '%".$search."%') ";
          //$queryWhere .=" AND (p.destinatario LIKE '%".$search."%' OR d.razonsocial LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.Num_Guia LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.cve_usuario LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%') ";



        if($fecha_inicio != '')
        {
          $date_inicio=date("Y-m-d H:i:s",strtotime($fecha_inicio));
          $queryWhere .= " AND p.Fec_Entrada >= DATE('".$date_inicio."') ";
        }
      
         if($fecha_fin != '')
        {
          $date_fin=date("Y-m-d H:i:s",strtotime($fecha_fin)); 
          $queryWhere .= " AND p.Fec_Entrada <= DATE('".$date_fin."')"; 
        }

        if($status_entrega != '')
        {
            $queryWhere .= " AND p.status = '".$status_entrega."' ";
        }

        if($mesas_revision != '')
        {
            $sql_mesas_revision = " AND ub.cve_ubicacion = '".$mesas_revision."' ";
            if($mesas_revision == 'todas')
              $sql_mesas_revision = " AND IFNULL(ub.cve_ubicacion, '') != '' ";

            $queryWhere .= $sql_mesas_revision;
        }

        if($cve_cliente != '')
        {
            $queryWhere .= " AND p.Cve_clte = '".$cve_cliente."'";
        }

        if($cve_cliente != '')
        {
            $queryWhere .= " AND p.Cve_clte = '".$cve_cliente."'";
        }

        if($cve_proveedor != '')
        {
            $queryWhere .= " AND ct.ID_Proveedor = '".$cve_proveedor."'";
        }

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        $count = 0;

        //$sqlWhere = '';
      
        $db2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_select_db($db2, DB_NAME);
      
        $sql = "SELECT DISTINCT
                    p.Fol_folio AS PO,
                    IFNULL(p.Pick_Num, '') AS oc_cliente,
                    IFNULL(p.Cve_clte, '') AS Cliente,
                    IFNULL(d.id_destinatario, '') AS Clave_Destinatario,
                    IFNULL(d.razonsocial, '') AS Razon_Social_Destinatario,
                    IFNULL(DATE_FORMAT(p.Fec_Entrada, '%d-%m-%Y'), '') AS Fecha_Pedido,
                    IFNULL(DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y'), '') AS Fecha_Compromiso,
                    IFNULL(e.DESCRIPCION, '') AS Estado,
                    IF(transp.transporte_externo = 0, IFNULL(transp.Nombre, ''), '') AS Transporte,
                    #IF(transp.transporte_externo = 0, 
                        IFNULL(tdor.ID_OEmbarque, '')
                    #, '') 
                    AS Folio_Embarque,
                    #IF(transp.transporte_externo = 0, 
                        COUNT(c.Guia)
                        #, '') 
                    AS Guia_Embarque,
                    IF(transp.transporte_externo = 0, IFNULL(GROUP_CONCAT(c.Guia SEPARATOR ',  '), ''), '') AS Guias_Embarques,
                    IF(transp.transporte_externo = 0, IFNULL(IF(thor.status = 'T', DATE_FORMAT(thor.fecha, '%d-%m-%Y'), IF(thor.status = 'F', DATE_FORMAT(tdor.fecha_envio, '%d-%m-%Y'), '')), ''), '') AS Fecha_Envio,

                    #IF(transp.transporte_externo = 0, 
                    CASE 
                    WHEN thor.status = 'T'
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), thor.fecha) <= 0, 1, DATEDIFF(CURDATE(), thor.fecha)), '')
                    WHEN thor.status = 'F'
                    THEN IFNULL(IF(DATEDIFF(pen.Fecha, p.Fec_Entrega) <= 0, 1, DATEDIFF(pen.Fecha, p.Fec_Entrega)), '')
                    ELSE ''
                    END 
                    #, '') 
                    AS Dias_Transito,

                    #IF(transp.transporte_externo = 0, 
                    CASE 
                    WHEN ((thor.status = 'T') OR (p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F')
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), p.Fec_Entrega) <= 0, 1, DATEDIFF(CURDATE(), p.Fec_Entrega)), '') 
                    ELSE ''
                    END
                    #, '') 
                    AS Dias_Retraso,

                    IF(thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1), 'Si', 'No') AS Cumplimiento,
                    IFNULL(IF(((thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1) ) AND DATE_FORMAT(tdor.fecha_envio, '%Y-%m-%d') <= p.Fec_Pedido), 'Si', IF((p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F' OR p.status = 'T', 'No', '')), '') AS otif,
                    IFNULL(IF(thor.status = 'F' OR transp.transporte_externo = 1, IFNULL(DATE_FORMAT(tdor.fecha_entrega, '%d-%m-%Y'), DATE_FORMAT(thor.fecha, '%d-%m-%Y')), ''), '') AS Fecha_Entrega,
                    #IFNULL(IF(thor.status = 'F', IFNULL(pen.Recibio, ''), thor.cve_usuario), '') AS cve_usuario,
                    IFNULL(pen.Recibio, '') as cve_usuario,
                    CONCAT(IFNULL(d.postal, ''), IF(IFNULL(d.postal, '')='', '', ', '), IFNULL(d.direccion, ''), IF(IFNULL(d.direccion, '')='', '', ', '), IFNULL(d.colonia, ''), IF(IFNULL(d.colonia, '')='', '', ', '), IFNULL(d.ciudad, ''), IF(IFNULL(d.ciudad, '')='', '', ', '), IFNULL(d.estado, ''), IF(IFNULL(d.estado, '')='', '', ', '), IFNULL(d.telefono, '')) AS Destinatario,
                    IFNULL(GROUP_CONCAT(DISTINCT DATE_FORMAT(sp.Hora_Final, '%d-%m-%Y / %h:%i:%s')), '') AS fecha_hora_surtido,
                    GROUP_CONCAT(DISTINCT IFNULL(CONCAT('(', ub.cve_ubicacion,')', ' - ', ub.descripcion), '')) AS mesa_revision,
                    IFNULL(prv.Nombre, '') AS Proveedor
                FROM th_pedido p
                LEFT JOIN th_subpedido sp ON sp.Fol_folio = p.Fol_folio
                LEFT JOIN t_ubicaciones_revision ub ON ub.cve_ubicacion = sp.buffer
                LEFT JOIN c_destinatarios d ON p.Cve_Clte = d.Cve_Clte
                LEFT JOIN cat_estados e ON e.ESTADO = p.status
                LEFT JOIN td_ordenembarque tdor ON tdor.Fol_folio = p.Fol_folio
                LEFT JOIN th_ordenembarque thor ON thor.ID_OEmbarque = tdor.ID_OEmbarque
                LEFT JOIN t_transporte transp ON transp.id = thor.ID_Transporte 
                LEFT JOIN t_pedentregados pen ON pen.Fol_folio = p.Fol_folio
                LEFT JOIN th_cajamixta c ON c.fol_folio = p.Fol_folio
                LEFT JOIN c_cliente ct ON p.Cve_clte = ct.Cve_Clte
                LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = ct.ID_Proveedor
                {$queryWhere} 
                 #AND p.Activo = 1 
                GROUP BY PO
                #ORDER BY Fecha_Pedido DESC
                ORDER BY p.Fec_Entrada DESC
                ";

        $res = mysqli_query($conn, $sql);
      
        $counter = mysqli_num_rows($res);

        $sql .= " LIMIT ".$limit." OFFSET ".$start;
        $res = mysqli_query($conn, $sql);

        $circle = '<i class="fa fa-circle"></i>';
        $response = new \stdClass;
        $response->data = [];
      
        $response->sql = $sql;

        $procesados = 0;

        $total_pages = ceil($counter/$limit);
        $cancelado ="";
        $almacen="";
        $entregado="";

        while ($row = mysqli_fetch_array($res)) {
          

            $response->data[] = [
                'PO'                        =>  $row["PO"],
                'oc_cliente'                =>  $row["oc_cliente"],
                'Clave_Cliente'             =>  $row["Cliente"],
                'Clave_Destinatario'        =>  $row["Clave_Destinatario"],
                'Razon_Social_Destinatario' =>  $row["Razon_Social_Destinatario"],
                'Fecha_Pedido'              =>  $row["Fecha_Pedido"],
                'Fecha_Compromiso'          =>  $row["Fecha_Compromiso"],
                'Fecha_Entrega'             =>  $row["Fecha_Entrega"],
                'Estado'                    =>  $row["Estado"],
                'Cumplimiento'              =>  $row["Cumplimiento"],
                'OTIF'                      =>  $row["otif"],
                'Destinatario'              =>  $row["Destinatario"],
                'fecha_hora_surtido'        =>  $row["fecha_hora_surtido"],
                'mesa_revision'             =>  $row["mesa_revision"],
                'cve_proveedor'             =>  $row["Proveedor"],
                'Transporte'                =>  $row["Transporte"],
                'Folio_Embarque'            =>  $row["Folio_Embarque"],
                'Guia_Embarque'             =>  $row["Guia_Embarque"],
                'Guias_Embarques'           =>  $row["Guias_Embarques"],
                'Fecha_Envio'               =>  $row["Fecha_Envio"],
                'cve_usuario'               =>  $row["cve_usuario"],
                'Dias_Transito'             =>  $row["Dias_Transito"],
                'Dias_Retraso'              =>  $row["Dias_Retraso"]
            ];
            $procesados++;
            $count++;
        }

        $response->data = array_slice($response->data, $start, $limit);

        /*if ($count >0) {
            $total_pages = ceil($count/$limit);
        }
        else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page = $total_pages;
        }*/

        $response->from = ($start == 0 ? 1 : $start) ;
        $response->to = ($start + $limit);
        $response->page = $page;
        $response->total_pages = $total_pages;
        $response->total = $counter;
        $response->status = 200;
        $response->query_completa = $sql;
        $response->counter = $counter;

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response,JSON_PRETTY_PRINT);exit;   
    }
//*****************************************************************************************
//*****************************************************************************************
    public function exportar()
    {
        $columnas = [
            'Folio',
            'Cliente',
            'Clave Destinatario',
            'Razon Social Destinatario',
            'Fecha Pedido',
            'Fecha/Hora Surtido',
            utf8_decode('Mesa Revisión'),
            'Fecha Compromiso',
            'Estado',
            'Transporte',
            'Folio Embarque',
            'Guia Embarque',
            utf8_decode('Fecha Envío'),
            'Dias Transito',
            'Dias Retraso',
            'Cumplimiento',
            'OTIF',
            'Fecha Entrega',
            utf8_decode('Firma Recepción')
        ];
      
        $search          = $_GET["search"];
        $status          = $_GET["status"];
        $page            = $_GET["page"];
        $limit           = $_GET["rows"];
        $fecha_inicio    = $_GET["fecha_inicio"];
        $fecha_fin       = $_GET["fecha_fin"];
        $sidx            = $_GET["sidx"];
        $sord            = $_GET["sord"];
        $status_entrega  = $_GET["status_entrega"];
        $mesas_revision  = $_GET["mesas_revision"];
      
        $queryWhere = " WHERE p.Fol_folio NOT IN (SELECT Folio_Pro FROM t_ordenprod) ";
        if($search != "")
            $queryWhere .=" AND (p.Fol_folio LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%' OR tdor.ID_OEmbarque LIKE '%".$search."%') ";
          //$queryWhere .=" AND (p.destinatario LIKE '%".$search."%' OR d.razonsocial LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.Num_Guia LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.cve_usuario LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%') ";

        if($fecha_inicio != '')
        {
          $date_inicio=date("Y-m-d H:i:s",strtotime($fecha_inicio));
          $queryWhere .= " AND p.Fec_Entrada >= DATE('".$date_inicio."') ";
        }
      
         if($fecha_fin != '')
        {
          $date_fin=date("Y-m-d H:i:s",strtotime($fecha_fin)); 
          $queryWhere .= " AND p.Fec_Entrada <= DATE('".$date_fin."')"; 
        }

        if($status_entrega != '')
        {
            $queryWhere .= " AND p.status = '".$status_entrega."'";
        }

        if($mesas_revision != '')
        {
            $sql_mesas_revision = " AND ub.cve_ubicacion = '".$mesas_revision."' ";
            if($mesas_revision == 'todas')
              $sql_mesas_revision = " AND IFNULL(ub.cve_ubicacion, '') != '' ";

            $queryWhere .= $sql_mesas_revision;
        }

        $sql = "SELECT DISTINCT
                    p.Fol_folio AS PO,
                    IFNULL(p.Cve_clte, '') AS Cliente,
                    IFNULL(d.id_destinatario, '') AS Clave_Destinatario,
                    IFNULL(d.razonsocial, '') AS Razon_Social_Destinatario,
                    IFNULL(DATE_FORMAT(p.Fec_Entrada, '%d-%m-%Y'), '') AS Fecha_Pedido,
                    IFNULL(DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y'), '') AS Fecha_Compromiso,
                    IFNULL(e.DESCRIPCION, '') AS Estado,
                    IFNULL(transp.Nombre, '') AS Transporte,
                    IFNULL(tdor.ID_OEmbarque, '') AS Folio_Embarque,
                    COUNT(c.Guia) AS Guia_Embarque,
                    IFNULL(IF(thor.status = 'T', DATE_FORMAT(thor.fecha, '%d-%m-%Y'), IF(thor.status = 'F', DATE_FORMAT(pen.Fecha, '%d-%m-%Y'), '')), '') AS Fecha_Envio,

                    CASE 
                    WHEN thor.status = 'T'
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), thor.fecha) <= 0, 1, DATEDIFF(CURDATE(), thor.fecha)), '')
                    WHEN thor.status = 'F'
                    THEN IFNULL(IF(DATEDIFF(pen.Fecha, p.Fec_Entrega) <= 0, 1, DATEDIFF(pen.Fecha, p.Fec_Entrega)), '')
                    ELSE ''
                    END AS Dias_Transito,

                    CASE 
                    WHEN ((thor.status = 'T') OR (p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F')
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), p.Fec_Entrega) <= 0, 1, DATEDIFF(CURDATE(), p.Fec_Entrega)), '') 
                    ELSE ''
                    END AS Dias_Retraso,
                    IF(thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1), 'Si', 'No') AS Cumplimiento,
                    IFNULL(IF(((thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1) ) AND DATE_FORMAT(tdor.fecha_envio, '%Y-%m-%d') <= p.Fec_Pedido), 'Si', IF((p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F' OR p.status = 'T', 'No', '')), '') AS otif,

                    #IFNULL(IF(thor.status = 'F' AND pen.Fecha <= p.Fec_Entrega, 'Si', IF((p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F' OR p.status = 'T', 'No', '')), '') AS Cumplimiento,
                    IFNULL(IF(thor.status = 'F', IFNULL(DATE_FORMAT(pen.Fecha, '%d-%m-%Y'), ''), ''), '') AS Fecha_Entrega,
                    IFNULL(GROUP_CONCAT(DISTINCT DATE_FORMAT(sp.Hora_Final, '%d-%m-%Y / %h:%i:%s')), '') AS fecha_hora_surtido,
                    GROUP_CONCAT(DISTINCT IFNULL(CONCAT('(', ub.cve_ubicacion,')', ' - ', ub.descripcion), '')) AS mesa_revision,
                    IFNULL(IF(thor.status = 'F', IFNULL(pen.Recibio, ''), ''), '') AS cve_usuario
                FROM th_pedido p
                LEFT JOIN th_subpedido sp ON sp.Fol_folio = p.Fol_folio
                LEFT JOIN t_ubicaciones_revision ub ON ub.cve_ubicacion = sp.buffer
                LEFT JOIN c_destinatarios d ON p.Cve_Clte = d.Cve_Clte
                LEFT JOIN cat_estados e ON e.ESTADO = p.status
                LEFT JOIN td_ordenembarque tdor ON tdor.Fol_folio = p.Fol_folio
                LEFT JOIN th_ordenembarque thor ON thor.ID_OEmbarque = tdor.ID_OEmbarque
                LEFT JOIN t_transporte transp ON transp.id = thor.ID_Transporte
                LEFT JOIN t_pedentregados pen ON pen.Fol_folio = p.Fol_folio
                LEFT JOIN th_cajamixta c ON c.fol_folio = p.Fol_folio
                {$queryWhere}
                GROUP BY PO
                ORDER BY Fecha_Pedido";
        $query = mysqli_query(\db2(), $sql);
        
        //echo var_dump($sql);
        //die();
        

        $filename = "Monitoreo de Pedidos".date('Y/m/d') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");
      

        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
        {
            echo $this->clear_column($row["PO"])."\t";
            echo $this->clear_column($row["Cliente"])."\t";
            echo $this->clear_column($row["Clave_Destinatario"])."\t";
            echo $this->clear_column($row["Razon_Social_Destinatario"])."\t";
            echo $this->clear_column($row["Fecha_Pedido"])."\t";
            echo $this->clear_column($row["fecha_hora_surtido"])."\t";
            echo $this->clear_column($row["mesa_revision"])."\t";
            echo $this->clear_column($row["Fecha_Compromiso"])."\t";
            echo $this->clear_column($row["Estado"])."\t";
            echo $this->clear_column($row["Transporte"])."\t";
            echo $this->clear_column($row["Folio_Embarque"])."\t";
            echo $this->clear_column($row["Guia_Embarque"])."\t";
            echo $this->clear_column($row["Fecha_Envio"])."\t";
            echo $this->clear_column($row["Dias_Transito"])."\t";
            echo $this->clear_column($row["Dias_Retraso"])."\t";
            echo $this->clear_column($row["Cumplimiento"])."\t";
            echo $this->clear_column($row["otif"])."\t";
            echo $this->clear_column($row["Fecha_Entrega"])."\t";
            echo $this->clear_column($row["cve_usuario"])."\t";
            echo  "\r\n";
        }
        exit;
    }
  
    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }
}
