<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;

use Application\Models\Pedidos;
use Application\Models\PedidosItems;
use Application\Models\SubPedidos;
use Application\Models\SubPedidosItems;
use Application\Models\RecorridoSurtido;
use Application\Models\Ubicaciones;

use Illuminate\Support\Facades\DB;
use Application\Models\ConsolidadosOla;
use Application\Models\ConsolidadosOlaItems;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @version 1.0.0
 * @category Pedidos
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class PedidosController extends Controller
{
    const CLAVE = 0;
    const ALMACEN = 1;
    const CANTIDAD = 2;
    const CADUCIDAD = 3;
    const LOTE = 4;
    const SERIE = 5;
    const BL = 6;
  
    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
    ];



    /**
     * Devuelve un listado de los Pedidos
     *
     * @return void
     */
    public function paginate()
    {
      $page    = $this->getInput('page', 1); // get the requested page
      $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
      $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
      $sord    = $this->getInput('sord'); // get the direction
      $status  = $this->getInput('status');

      $search  = $this->getInput('search');
      $codigo  = $this->getInput('codigo');
      $criterio  = $this->getInput('criterio');
      $almacen  = $this->getInput('almacen');
      $filtro  = $this->getInput('filtro');

      $fecha_inicio  = $this->getInput('fecha_inicio');
      $fecha_fin  = $this->getInput('fecha_fin');


      $start = $limit * $page - $limit; // do not put $limit*($page - 1) 
      $count = 0;

      $sql = "
        SELECT
          o.id_pedido,
          o.Fol_folio AS orden,
          IFNULL(o.Pick_Num, '--') AS orden_cliente,
          IFNULL(p.Descripcion, '--') AS prioridad,
          CASE LEFT(o.Fol_folio,2)
          WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Consolidado de Ola')
          ELSE IFNULL(e.DESCRIPCION, '--')
          END AS status,
          IFNULL(c.RazonSocial, '--') AS cliente,
          IFNULL(c.CalleNumero, '--') AS direccion,
          IFNULL(c.CodigoPostal, '--') AS dane,
          IFNULL(c.Ciudad, '--') AS ciudad,
          IFNULL(c.Estado, '--') AS estado,
          IFNULL(COALESCE(SUM(od.Num_cantidad), 0), '--') AS cantidad,
          IFNULL(ROUND(SUM(od.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))), 4), '--') AS volumen,
          IFNULL(SUM(od.Num_cantidad * a.peso), '--') AS peso,
          IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_pedido,
          IFNULL(DATE_FORMAT(thsub.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_ini,
          IFNULL(DATE_FORMAT(thsub.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_fi,
          IFNULL(concat(datediff(DATE_FORMAT(Hora_Final, '%Y-%m-%d'), DATE_FORMAT(Hora_inicio, '%Y-%m-%d')),'  ',timediff(DATE_FORMAT(Hora_Final, '%H:%i:%s'), DATE_FORMAT(Hora_inicio, '%H:%i:%s'))), '--') as TiempoSurtido,
          IFNULL(concat(FLOOR((sum(s.Cantidad)*100)/ sum(od.Num_cantidad)),'%'), '0%') AS surtido,
          IFNULL(u.nombre_completo, '--') AS asignado
        FROM th_pedido o
          LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad
          LEFT JOIN cat_estados e ON e.ESTADO = o.status
          LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte   
          LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
          LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio
          LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario
          LEFT JOIN c_articulo a ON a.cve_articulo = od.Cve_articulo
          LEFT JOIN t_recorrido_surtido trs on trs.fol_folio = od.Fol_folio and trs.Cve_articulo = od.Cve_articulo
          LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac
        WHERE o.Activo = 1
      ";

      $sql2 = "
        SELECT o.fol_folio
        FROM th_pedido o
        LEFT JOIN cat_estados e ON e.ESTADO = o.status
        LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte   
        LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
        LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio
        LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = od.Cve_articulo
        LEFT JOIN t_recorrido_surtido trs on trs.fol_folio = od.Fol_folio and trs.Cve_articulo = od.Cve_articulo
        LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac
        WHERE o.Activo = 1
      ";

      if (!empty($almacen)) 
      {
        $sql .= " AND o.cve_almac = '{$almacen}' ";
        $sql2 .= " AND o.cve_almac = '{$almacen}' ";
      }
      if (!empty($status)) 
      {
        $sql .= " AND o.status = '{$status}' ";
        $sql2 .= " AND o.status = '{$status}' ";
      }

      if (!empty($criterio) ) //&& !empty($filtro)
      {
        $sql .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR p.Descripcion like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
        $sql2 .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
      }

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("Y-m-d", strtotime($fecha_inicio));
        $fecha_fin = date ("Y-m-d", strtotime($fecha_fin));
        $sql .= " AND o.Fec_Pedido BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $sql2 .= " AND o.Fec_Pedido BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("Y-m-d", strtotime($fecha_inicio));
        $sql .= " AND o.Fec_Pedido >= '{$fecha_inicio}' ";
        $sql2 .= " AND o.Fec_Pedido >= '{$fecha_inicio}' ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("Y-m-d", strtotime($fecha_fin));
        $sql .= " AND o.Fec_Pedido <= '{$fecha_fin}'";
        $sql2 .= " AND o.Fec_Pedido <= '{$fecha_fin}'";
      }
      //$sql .= " AND o.status <> 'O' ";
      $sql .= " GROUP BY o.Fol_folio ORDER BY o.Fec_Entrada DESC";
      $sql2 .= " GROUP BY o.Fol_folio ORDER BY o.Fec_Entrada DESC";

      $sql_count = "Select count(*) as x from (".$sql2.") y";

      $sql .= " limit ".$limit." OFFSET ".$start;
      
      

      $data = Capsule::select(Capsule::raw($sql));
      
//       echo var_dump($data);
//       die();

      $data_count = Capsule::select(Capsule::raw($sql_count));

      $response = new \stdClass;
      $response->data = [];

      //$response->query["sql"] = $sql;
      //$response->query["sql_count"] = $sql_count;

      //$response->data["sql_count"] = $sql_count;

      //$count = count($data);
      $count = $data_count[0]->x;

      foreach ($data as $row) 
      {
        //   $date1 = new DateTime("2015-02-14");
        //  $date2 = new DateTime("2015-02-16");
        // $diff = $date1->diff($date2);
        // will output 2 days
        //echo $diff->days . ' days ';
        //$date_tiempo = strtotime($row->fecha_fi)-strtotime($row->fecha_ini);
        /*$fecha1 = new DateTime($row->fecha_ini);//fecha inicial
        $fecha2 = new DateTime($row->fecha_fi);//fecha de cierre

        $intervalo = $fecha1->diff($fecha2);

        $diferencia = $intervalo->format('%Y %m %d %H %i %s');*/

        $response->data[] = [
          'id' => $row->id_pedido,
          'orden' => $row->orden,
          'orden_cliente' => $row->orden_cliente,
          'prioridad' => $row->prioridad,
          'status' => $row->status,
          'cliente' => $row->cliente,
          'direccion' => $row->direccion,
          'dane' => $row->dane,
          'ciudad' => $row->ciudad,
          'estado' => $row->estado,
          'cantidad' => $row->cantidad,
          'volumen' => $row->volumen,
          'peso' => round($row->peso,3),
          'fecha_pedido' => $row->fecha_pedido,
          'fecha_ini' => $row->fecha_ini,
          'fecha_fi' => $row->fecha_fi,
          'TiempoSurtido' => $row->TiempoSurtido,  
          'surtido' => $row->surtido,
          'asignado' => $row->asignado,
        ];
        if ($row->peso !='')
        {
          $response->pesototal+= $row->peso; 
        }
        if ($row->volumen !='')
        {
          $response->volumentotal+= $row->volumen; 
        }
        if ($row->orden !='')
        {
          $response->totalpedidos++; 
        }
      }
      //$response->data = array_slice($response->data, $start, $limit);

      if ($count >0) 
      {
        $total_pages = ceil($count/$limit);
      }
      else 
      {
        $total_pages = 0;
      }

      if ($page > $total_pages) 
      {
        $page = $total_pages;
      }

      $sql = str_replace(["\n", "\t", "  "], ['',' ', ''], $sql);
      $response->from = ($start == 0 ? 1 : $start) ;
      $response->to = ($start + $limit);
      $response->page = $page;
      $response->total_pages = $total_pages;
      $response->total = $count;
      $response->status = 200;
      //$response->sql = $sql;

      ob_clean();
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($response,JSON_PRETTY_PRINT);exit;	
    }


    /**
     * Crea un Consolidado de Ola
     *
     * @return void
     */
    function crearConsolidadoDeOla()
    {
        $folios = $_POST['folios'];
        $fecha_entrega = $_POST['fecha_entrega'];
        $total_cajas = 1;//$_POST['total_cajas'];
        $total_piezas = 1;//$_POST['toal_piezas'];
       
        $totalFolio = count($folios) - 1;
        foreach($folios as $key => $value){
            $foliosStr .= "'{$value}'";
            if($key !== $totalFolio){
                $foliosStr .= ',';
            }
        }
        $folio_consolidado = $this->loadNumeroOla();
        $pesoVolumen = $this->pesoVolumenDePedidos($folios);

        // Cabecera del consolidado
        $model_header = new ConsolidadosOla();
        $model_header->Fec_Entrega      = $this->pSQL($fecha_entrega);
        $model_header->Tot_Cajas        = $this->pSQL($total_cajas);
        $model_header->Tot_Pzs          = $this->pSQL($total_piezas);
        //$model->Placa_Trans  = $this->pSQL($placa_trans);
        //$model->Sellos       = $this->pSQL($sellos);
        $model_header->Fol_PedidoCon    = $this->pSQL($folio_consolidado);
        $model_header->No_OrdComp       = $this->pSQL($folio_consolidado);
        $model_header->Status           = $this->pSQL('P');
        $model_header->save();
        
        
        $folio_items = [];
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT
                *,
                IFNULL(SUM(item.Num_cantidad), 0) AS sum_pedidas
            FROM td_pedido item
                LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
            WHERE item.Fol_folio IN ({$foliosStr})
            GROUP BY art.Cve_Articulo
                ORDER BY item.Fol_folio, item.itemPos;
        ";
        if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $i=0;
        while ($row = mysqli_fetch_array($res)) {
          $row = array_map('utf8_encode', $row);
          $folio_items[$i]=$row;
          $i++;
        }
        
        foreach ($folio_items as $item)
        {
            $model_items = new ConsolidadosOlaItems();
            $model_items->Fol_PedidoCon = $folio_consolidado;
            $model_items->No_OrdComp    = $folio_consolidado;
            $model_items->Fec_OrdCom    = $fecha_entrega;
            $model_items->Cve_Articulo  = $item['Cve_articulo'];
            $model_items->Cant_Pedida   = $item['sum_pedidas'];
            $model_items->Unid_Empaque  = 'BO';
            $model_items->Tot_Cajas     = $item['sum_pedidas'];
            //$model_items->Fact_Madre    = '';
            $model_items->Cve_Clte      = '';
            $model_items->Cve_CteProv   = '';
            $model_items->Fol_Folio     = $item['Fol_folio'];
            $model_items->CodB_Cte      = '';
            $model_items->Cod_PV        = '';
            $model_items->Status        = 'A';

            $model_items->save();
        }
      
        foreach ($folios as $folio_nro)
        {
            //@todo Funciton para actualizar el estatus del pedido para ocultarlo
            Pedidos::where('Fol_folio', $folio_nro)->update(['status'=>'O']);
        }

        //Duplicar pedido pero com Orden de Consolidado de Ola
        $model_header = new Pedidos;
        $model_header->Fol_folio            = $folio_consolidado;
        $model_header->Fec_Pedido           = $folio->Fol_folio;
        $model_header->Cve_clte             = '';
        $model_header->status               = 'A';
        $model_header->Fec_Entrega          = $fecha_entrega;
        $model_header->cve_Vendedor         = $folio->cve_Vendedor;
        $model_header->Num_Meses            = $folio->Num_Meses;
        $model_header->Observaciones        = $folio->Observaciones;
        $model_header->statusaurora         = $folio->statusaurora;
        $model_header->ID_Tipoprioridad     = $folio->ID_Tipoprioridad;
        $model_header->Fec_Entrada          = $folio->Fec_Entrada;
        $model_header->transporte           = $folio->transporte;
        $model_header->ruta                 = $folio->ruta;
        $model_header->bloqueado            = $folio->bloqueado;
        $model_header->fechadet             = $folio->fechadet;
        $model_header->fechades             = $folio->fechades;
        $model_header->cve_almac            = $folio->cve_almac;
        $model_header->destinatario         = '';
        $model_header->subido               = $folio->subido;
        $model_header->cve_ubicacion        = $folio->cve_ubicacion;
        $model_header->Pick_Num             = $folio->Pick_Num;
        $model_header->Cve_Usuario          = $folio->Cve_Usuario;
        $model_header->Ship_Num             = $folio->Ship_Num;
        $model_header->BanEmpaque           = $folio->BanEmpaque;
        $model_header->Cve_CteProv          = $folio->Cve_CteProv;
        $model_header->Activo               = $folio->Activo;
        $model_header->save();


        foreach ($folio_items as $item)
        {
            $model_items = new PedidosItems();
            $model_items->fol_folio     = $folio_consolidado;
            $model_items->Cve_articulo  = $item['Cve_articulo'];
            $model_items->Num_cantidad  = $item['Num_cantidad'];
            $model_items->Num_Meses     = $item['Num_Meses'];
            $model_items->SurtidoXCajas = $item['SurtidoXCajas'];
            $model_items->SurtidoXPiezas = $item['SurtidoXPiezas'];
            $model_items->status        = $item['status'];
            $model_items->cve_cot       = $item['cve_cot'];
            $model_items->factor        = $item['factor'];
            $model_items->itemPos       = $item['itemPos'];
            $model_items->cve_lote      = $item['cve_lote'];
            $model_items->Activo        = $item['Activo'];

            $model_items->save();
        }

         
        $this->response(200, [
            'statusText' => 'Consolidado creado con exito',
            'data' => [
                'nro_ola' => $folio_consolidado,
                'peso' => $pesoVolumen['peso'],
                'volumen' =>$pesoVolumen['volumen'],
                'total_ordenes' => ($totalFolio + 1),
                'items' => ConsolidadosOlaItems::where('No_OrdComp', $folio_consolidado)->get(),
                'items2' => $items2,
            ]
        ]);

    }


    /**
     * borrar un consolidado de Ola
     *
     * @return void
     */
    function borrarConsolidado()
    {
        $consololidado = $this->getDelete('consololidado');
        $folios = ConsolidadosOlaItems::select('Fol_PedidoCon')->distinct()->where('No_OrdComp', $consololidado)->get(['Fol_PedidoCon']);

        foreach ($folios as $folio) {
            Pedidos::where('Fol_folio', $folio->Fol_PedidoCon)->update(['status'=>'A']);
        }

        ConsolidadosOla::where('No_OrdComp', $consololidado)->delete();
        ConsolidadosOlaItems::where('No_OrdComp', $consololidado)->delete();

    }


    /**
     * Devuelve los detalles d eun Consoliadado de Ola
     *
     * @return void
     */
    function detallesConsolidado()
    {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction
        $folio = $_POST['folio'];
        $totalFolio = count($folio) - 1;
        $folios = '';
        
        foreach($folio as $key => $value){
            $folios .= "'{$value}'";
            if($key !== $totalFolio){
                $folios .= ',';
            }
        }
        $start = $limit*$page - $limit;
    
        if(!$sidx) $sidx =1;
    
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn, 'utf8');
        $sqlCount = "SELECT COUNT(Cve_Articulo) AS cuenta FROM `td_consolidado` WHERE Fol_PedidoCon IN ({$folios})";
    
        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    
        $row = mysqli_fetch_array($res);
        $count = $row['cuenta'];
    
        $sql = "SELECT
                    item.No_OrdComp AS folio,
                    art.Cve_Articulo AS clave,
                    IFNULL(art.des_articulo, '--') AS articulo,
                    IFNULL(SUM(item.Num_cantidad), 0) AS pedidas,
                    0 AS surtidas
                FROM td_pedido item
                    LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
                WHERE item.No_OrdComp IN ({$folios})
                GROUP BY art.Cve_Articulo
                    ORDER BY item.No_OrdComp, item.itemPos;
        ";
    
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
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
            $row = array_map('utf8_encode', $row);
            $responce->rows[$i]['id']=$i;
            $responce->rows[$i]['cell']=array($row['clave'],$row['articulo'],$row['pedidas'],$row['surtidas']);
            $i++;
        }
    
        echo json_encode($responce);
    }


    /**
     * Devuelve los detalles de un surtido
     *
     * @return void
     */
    function detallesSutido()
    {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction
        $folio = $_POST['folio'];
        $totalFolio = count($folio) - 1;
        $folios = '';
        
        foreach($folio as $key => $value){
            $folios .= "'{$value}'";
            if($key !== $totalFolio){
                $folios .= ',';
            }
        }
        $start = $limit*$page - $limit;
    
        if(!$sidx) $sidx =1;
    
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn, 'utf8');
        $sqlCount = "SELECT COUNT(Cve_Articulo) AS cuenta FROM `td_consolidado` WHERE Fol_PedidoCon IN ({$folios})";
    
        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    
        $row = mysqli_fetch_array($res);
        $count = $row['cuenta'];
    
        $sql = "SELECT
                    item.No_OrdComp AS folio,
                    art.Cve_Articulo AS clave,
                    IFNULL(art.des_articulo, '--') AS articulo,
                    IFNULL(SUM(item.Num_cantidad), 0) AS pedidas,
                    0 AS surtidas
                FROM td_pedido item
                    LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
                WHERE item.No_OrdComp IN ({$folios})
                GROUP BY art.Cve_Articulo
                    ORDER BY item.No_OrdComp, item.itemPos;
        ";
    
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
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
            $row = array_map('utf8_encode', $row);
            $responce->rows[$i]['id']=$i;
            $responce->rows[$i]['cell']=array($row['clave'],$row['articulo'],$row['pedidas'],$row['surtidas']);
            $i++;
        }
    
        echo json_encode($responce);
    }


    /**
     * Devuelve elpeso y Volumen del Folio especificado
     *
     * @param string $folios Número de Folio
     * @return void
     */
    function pesoVolumenDePedidos($folios)
    {
        $pesototal = 0;
        $volumentotal = 0;
        $folio = '';
        $total = intval(count($folios) -1);

        foreach ($folios as $key => $value) {
            $folio .= "'{$value}'";
            if ($key !== $total) {
                $folio .= ",";
            }
        }
        
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn, 'utf8');
        $sql = "SELECT  
                    SUM(peso) as pesototal,
                    SUM((alto/1000) * (ancho/1000) * (fondo/1000)) as volumentotal
            FROM c_articulo
                WHERE cve_articulo IN 
                    (SELECT Cve_articulo FROM td_pedido WHERE Fol_folio IN({$folio}))
        ";
        $query = mysqli_query($conn, $sql);

        if ($query->num_rows >0) {
            $result = mysqli_fetch_assoc($query);
            $pesototal = $result['pesototal'];
            $volumentotal = $result['volumentotal'];
        }

        mysqli_close($conn);

        return [
            "peso"     => $pesototal,
            "volumen"  => $volumentotal
        ];
    }


    private function loadNumeroOla() {
        $sql="SELECT AUTO_INCREMENT as id
            FROM information_schema.tables
            WHERE table_name = 'th_consolidado'
            AND table_schema = DATABASE()";            
            $sth = \db()->prepare( $sql );
        
        $sth->execute();
        return 'WS0'.$sth->fetch()[0];
    }


    /**
     * Importa pedidos a la Tabla de pedidos
     *
     * @return void
     */
    public function importar()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $tipo = $_POST["tipo"];
        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );
      
        $columnas = array("Folio","Cliente","FechaPedido","FechaEntrega","Prioridad","ClaveAlmacen","Comentarios","NoOrdenCliente","Destinatario","Articulo","Piezas","Meses","Status","itemPos","Cliente Proveedor");
        if($tipo=="vp"){$columnas[] = "BL";}
        $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
      
        $num_pedidos = 0;
        $secuencia = 0;
        $Fol_folio = "";
        $linea = 1;
        $lineas = $xlsx->rows();
        $folio_cont = 1; //provisional
        foreach ($xlsx->rows() as $row)
        {
          if($tipo!="ph") //me evito la validación por los momentos
          {
                if($linea == 1) {
                    foreach($columnas as $k => $v)
                    {
                        if($this->pSQL($row[$k])!=$v)
                        {
                            $k_i=$k+1;
                            $this->response(400, ['statusText' =>  "ERROR Falta el titulo “".$v."” en la columna “".$cl_excel[$k]."” de su archivo excel",]); 
                        }
                    }
                    $linea++;
                    continue;
                }
                else
                {
                    foreach($columnas as $k => $v)
                    {
                        if($this->pSQL($row[$k])=="")
                        {
                            // Datos incorrecto en la fila “Numero de fila” de la columna “Letra de columna”
                            $k_i=$k+1;
                            $this->response(400, ['statusText' =>  "ERROR Dato incorrecto en la fila “".$linea."” columna “".$cl_excel[$k]."”. Este dato es necesario.",]); 
                        }
                    }
                }

            if($Fol_folio != $this->pSQL($row[0]))
            {
                $num_pedidos+=1;
                $secuencia = 0;
            }
            $Fol_folio = $this->pSQL($row[0]);
            $Cve_articulo = $this->pSQL($row[9]);
            $cve_almac = $this->pSQL($row[5]);
            $element = Pedidos::where('Fol_folio', $Fol_folio)->where('cve_almac', $cve_almac)->first();
            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Pedidos(); 
            }
            $model->Fol_folio        = $this->pSQL($row[0]);
            $model->Cve_clte         = $this->pSQL($row[1]);
            $model->Fec_Pedido       = $this->pSQL($row[2]);
            $model->Fec_Entrada      = $this->pSQL($row[2]);
            $model->Fec_Entrega      = $this->pSQL($row[3]);
            $model->ID_Tipoprioridad = $this->pSQL($row[4]);
            $model->cve_almac        = $this->pSQL($row[5]);
            $model->Observaciones    = $this->pSQL($row[6]);
            $model->Pick_Num         = $this->pSQL($row[7]);
            $model->destinatario     = $this->pSQL($row[8]);
            $model->status           = $this->pSQL($row[12]);
            $model->Cve_CteProv      = $this->pSQL($row[14]);
              
            $respuesta = $model->save();
          
            if($tipo=="vp")
            {
              $element = SubPedidos::where('fol_folio', $Fol_folio)->where('cve_almac', $cve_almac)->first();
              if($element != NULL){
                  $model = $element; 
              }
              else {
                  $model = new SubPedidos();
              }
              
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $this->pSQL($row[5]);
              $model->Sufijo           = 1;
              $model->Fec_Entrada      = $this->pSQL($row[2]);
              $model->status           = $this->pSQL($row[12]);

              $respuesta = $model->save();
            }
          
            $items = PedidosItems::where('Fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->first();

            if($items != NULL){
                $model = $items; 
            }
            else {
                $model = new PedidosItems(); 
            }
          
            $model->Fol_folio        = $this->pSQL($row[0]);
            $model->Cve_articulo     = $this->pSQL($row[9]);
            $model->Num_cantidad     = $this->pSQL($row[10]);
            $model->Num_Meses        = $this->pSQL($row[11]);
            $model->status           = $this->pSQL($row[12]);
            
            $respuesta = $model->save();
          
          
            if($tipo=="vp")
            {
              $element = SubPedidosItems::where('fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->delete();
              $model = new SubPedidosItems(); 
              
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $this->pSQL($row[5]);
              $model->Sufijo           = 1;
              $model->Cve_articulo     = $this->pSQL($row[9]);
              $model->Num_cantidad     = $this->pSQL($row[10]);
              $model->Nun_surtida      = 0;
              $model->status           = $this->pSQL($row[12]);
              
              $respuesta = $model->save();
              
              $ubicacion = Ubicaciones::where('CodigoCSD', $this->pSQL($row[15]))->first();
              
              $element = RecorridoSurtido::where('fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->delete();
              
              $secuencia++;
              
              $model = new RecorridoSurtido();
              
              $model->idy_ubica        = $ubicacion->idy_ubica;
              $model->orden_secuencia  = $secuencia;
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $this->pSQL($row[5]);
              $model->Sufijo           = 1;
              $model->Cve_articulo     = $this->pSQL($row[9]);
              $model->Cantidad         = $this->pSQL($row[10]);
              $model->Picking          = "S";
              $model->Activo           = 1;
              
              $respuesta = $model->save();
              
            }
          }
          else if($linea > 1)
          {
//                $element = Pedidos::where('Fol_folio', $Fol_folio)->where('cve_almac', $cve_almac)->first();
//                if($element != NULL){
//                    $model = $element; 
//                }
//                else {
                    $model = new Pedidos(); 
//                }

                $model->Fol_folio        = $folio_cont;
                $model->Cve_clte         = $this->pSQL($row[7]);
                $model->Fec_Pedido       = '2019-12-18';//$this->pSQL($row[1]);
                $model->Fec_Entrega      = '2019-12-18';//$this->pSQL($row[2]);
                $model->ID_Tipoprioridad = 1;
                $model->cve_almac        = 3;
                $model->Observaciones    = 0;
                $model->destinatario     = $this->pSQL($row[8]);
                $model->Pick_Num         = $this->pSQL($row[0]);
                $model->Cve_articulo     = $this->pSQL($row[11]);
                $model->Num_cantidad     = $this->pSQL($row[9]);
                $model->Num_Meses        = 0;
                $model->status           = 'A';
                $model->itemPos          = 1;
                $model->Cve_CteProv      = 100;
/*
                $this->response(400, ['statusText' =>  "
                  Fol_folio = ".$model->Fol_folio."<br>
                  Cve_clte = ".$model->Cve_clte."<br>
                  Fec_Pedido = ".$model->Fec_Pedido."<br>
                  Fec_Entrega = ".$model->Fec_Entrega."<br>
                  ID_Tipoprioridad = ".$model->ID_Tipoprioridad."<br>
                  cve_almac = ".$model->cve_almac."<br>
                  Observaciones = ".$model->Observaciones."<br>
                  destinatario = ".$model->destinatario."<br>
                  Pick_Num = ".$model->Pick_Num."<br>
                  Cve_articulo = ".$model->Cve_articulo."<br>
                  Num_cantidad = ".$model->Num_cantidad."<br>
                  Num_Meses = ".$model->Num_Meses."<br>
                  status = ".$model->status."<br>
                  itemPos = ".$model->itemPos."<br>
                  Cve_CteProv = ".$model->Cve_CteProv."<br>
                  ",]); 
*/
                $respuesta = $model->save();
          }

          $folio_cont++;
          $linea++;
        }
      
        $this->response(200, [
            'statusText' =>  "Pedidos importados con exito. Total de Pedidos: \"{$num_pedidos}\"",
            "lineas"=>$lineas,
        ]);
    }

  
  
  public function importar_pedidos()
     {
        var_dump("Esto ya llego ");
    
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero. Verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        //aqui va cabecera 
        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
            {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en elformato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportarPedidos($row);
            if( $eval === TRUE ){
 
            } else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }

        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $clave = $this->pSQL($row[self::CLAVE]);
            $element = Pedidos::where('cve_articulo', '=', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Pedidos(); 
            }
            
            $model->cve_articulo     = $clave;
            $model->des_articulo     = $this->pSQL($row[self::DESCRIPCION]);
            $model->cve_almac        = $this->pSQL($row[self::ALMACEN]);
            $model->cve_codprov      = $this->pSQL($row[self::PROVEEDOR]);
            $model->barras2          = $this->pSQL($row[self::CODIGO_BARRA]);
            $model->grupo            = $this->pSQL($row[self::GRUPO]);
            $model->alto             = $this->pSQL($row[self::ALTO]);
            $model->fondo            = $this->pSQL($row[self::FONDO]);
            $model->ancho            = $this->pSQL($row[self::ANCHO]);
            $model->clasificacion    = $this->pSQL($row[self::CLASIFICACION]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Artículos importados con exito. Total de artículos: \"{$linea}\"",
        ]);

    }
  
    public function validarRequeridosImportarPedidos($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }


    /**
     * Valida que los datos del los campos obligatorios no estén vacíos
     *
     * @param [type] $row
     * @return void
     */
    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }


    /**
     * Exportar la cabecera de los pedidos
     *
     * @return void
     */
    public function exportarCabecera()
    {
      //var_dump("Hola");
      //http://wms.assistpro-adl.com/api/v2/pedidos/exportar-cabecera
      
        $filename = $this->fileNameExport("-cabecera-");
        $columnas = $this->getColumnsTable('th_pedido');
        $data = Pedidos::get();

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach ($columnas as $value) {
            echo $value . "\t" ;
        }
        print("\r\n");

        foreach($data as $row)
        {            
            foreach ($columnas as $columna) {
                echo $this->clearColumnToExport($row->{$columna}) ;
            }
            echo  "\r\n";
        }
        exit;  
        
    }


    /**
     * Exportar Detalle de Pedidos
     * @param string $folio Folio especifico a exportar
     */
    public function exportarDetalles($folio = '')
    {
        $filename = $this->fileNameExport("-detalle-");
        $columnas = $this->getColumnsTable('td_pedido');
        $data = PedidosItems::get();

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach ($columnas as $value) {
            echo $value . "\t" ;
        }
        print("\r\n");

        foreach($data as $row)
        {            
            foreach ($columnas as $columna) {
                echo $this->clearColumnToExport($row->{$columna}) ;
            }
            echo  "\r\n";
        }
        exit;        
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function store()
    {
        $Fol_folio = $this->pSQL($this->getPost('Fol_folio'));
        $Fec_Pedido = $this->pSQL($this->getPost('Fec_Pedido'));
        $Fec_Entrega = $this->pSQL($this->getPost('Fec_Entrega'));
        $Pick_Num = $this->pSQL($this->getPost('Pick_Num'));
        $Fec_Entrada = $this->pSQL($this->getPost('Fec_Entrada'));
        $Cve_clte = $this->pSQL($this->getPost('Cve_clte'));
        $cve_Vendedor = $this->pSQL($this->getPost('cve_Vendedor'));
        $Cve_Usuario = $this->pSQL($this->getPost('Cve_Usuario'));
        $ID_Tipoprioridad = $this->pSQL($this->getPost('ID_Tipoprioridad'));
        $cve_almac = $this->pSQL($this->getPost('cve_almac'));
        $Cve_CteProv = $this->pSQL($this->getPost('Cve_CteProv'));
        $Observaciones = $this->pSQL($this->getPost('Observaciones'));
        $Activo = $this->pSQL($this->getPost('Activo'), 1);

        if( isNikken() ){
            $status = 'I';
        } else{
            $status = 'A';
        }

        $existe = Pedidos::where('Fol_folio', $Fol_folio)->first();

        if( $existe != null){
            $this->response(400, [
                'statusText' =>  "El folio del pedido que intente ingresar ya se encuentra registrado",
            ]);
        }
        
        $cve_almac = AlmacenP::where('clave', $cve_almac)->first();
        $cve_almac = $cve_almac->id;

        $model = new Pedidos();

        $model->Fol_folio =  $Fol_folio;
        $model->Fec_Pedido = $Fec_Pedido;
        $model->Fec_Entrega = $Fec_Entrega;
        $model->Fec_Entrada = $Fec_Entrada;
        $model->Cve_clte = $Cve_clte;
        $model->status = $status;
        $model->cve_Vendedor = $cve_Vendedor;
        $model->Pick_Num = $Pick_Num;
        $model->Cve_Usuario = $Cve_Usuario;
        $model->ID_Tipoprioridad = $ID_Tipoprioridad;
        $model->cve_almac = $cve_almac;
        $model->Cve_CteProv = $Cve_CteProv;
        $model->Observaciones = $Observaciones;
        $model->Activo = $Activo;     
        $model->save();


        ############################ Detalles #############################
        PedidosItems::where('Fol_folio', $Fol_folio)->delete();
        
        $model = new PedidosItems();
        $linea = 1;
        $items = $this->pSQL($this->getPost('arrDetalle'));

        if( count($items) ){
            foreach ($items as $value)
            {
                $Cve_articulo = $this->pSQL($value['Cve_articulo']);
                $Num_cantidad = $this->pSQL($value['Num_cantidad']);
                $Num_Meses = $this->pSQL($value['Num_Meses']);

                $model->Fol_folio = $Fol_folio;
                $model->Cve_articulo = $Cve_articulo;
                $model->Num_Meses = $Num_Meses;
                $model->Num_cantidad = $Num_cantidad;
                $model->itemPos = $linea;
                $model->status = $status;
                $model->Activo = $Activo;
                $model->save();
                $linea++;
            }
        }

        $this->response(200, [
            'statusText' =>  "El pedido ha sido registrado correctamente",
        ]);

    }


    public function asignarUsuarioPedido()
    {

    }
  

}