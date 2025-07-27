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

    const CLAVE = 0; const ALMACEN = 1; const CANTIDAD = 2; const CADUCIDAD = 3; const LOTE = 4; const SERIE = 5; const BL = 6;

//****************************************************************************************************************************
//*****************************************       PEDIDOS TH      ************************************************************
/****************************************************************************************************************************
    const FOLIO                      = 0;    const BLOQUEADO                  = 11; const COLONIA_DESTINATARIO       = 22;
    const FECHA_PEDIDO               = 1;    const NUM_ORDEN_CLIENTE          = 12; const CODIGO_POSTAL_DESTINATARIO = 23;
    const CLAVE_CLIENTE              = 2;    const PICK_NUMBER                = 13; const CIUDAD_DESTINATARIO        = 24;
    const CLAVE_CLIENTE_PROV         = 3;    const CVE_ARTICULO               = 14; const ESTADO_DESTINATARIO        = 25;
    const DIR_ENTREGA_PRINCIPAL      = 4;    const LOTE_SERIE                 = 15; const CONTACTO                   = 26;
    const FECHA_ENTREGA              = 5;    const NUM_CANTIDAD               = 16; const TELEF_DESTINATARIO         = 27;
    const CLAVE_VENDEDOR             = 6;    const MESES_2                    = 17; const EMAIL_DESTINATARIO         = 28;
    const MESES                      = 7;    const ITEM_NUM                   = 18; const LATITUD                    = 29;
    const OBSERVACIONES              = 8;    const CVE_DESTINATARIO           = 19; const LONGITUD                   = 30;
    const FECHA_ENTRADA              = 9;    const RAZON_SOCIAL               = 20;
    const PRIORIDAD                  = 10;   const DIRECCION_DESTINATARIO     = 21;
//****************************************************************************************************************************
//****************************************************************************************************************************/
const CVE_ARTICULO = 0;
const LOTE_SERIE = 1; 
const NUM_CANTIDAD = 2;

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
      $criterio  = $search;//$this->getInput('criterio');
      $almacen  = $this->getInput('almacen');
      //$filtro  = $this->getInput('filtro');

      $fecha_inicio  = $this->getInput('fecha_inicio');
      $fecha_fin  = $this->getInput('fecha_fin');


      $start = $limit * $page - $limit; // do not put $limit*($page - 1) 
      $count = 0;


      $subpedidos_con_bl = ""; $ver_subpedido_A = "";

      $sql2 = "
        SELECT o.fol_folio
        FROM th_pedido o
        LEFT JOIN cat_estados e ON e.ESTADO = o.status 
        LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte   
        LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
        LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio 
        LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario 
        LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = od.Cve_articulo 
        LEFT JOIN t_recorrido_surtido trs on trs.fol_folio = od.Fol_folio and trs.Cve_articulo = od.Cve_articulo 
        LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac 
        WHERE o.Activo = 1
      ";


      if($search != "") $status = ""; //cuando se realice una busqueda con texto escrito, se debe poder buscar por cualquier status

      $status_A = "";
      if(!empty($status) && $status != 'A')
        $status_A = " AND o.status = '{$status}' AND o.Fol_folio NOT IN (SELECT Fol_folio FROM th_subpedido)";

      if($status == 'S') 
      {
          $subpedidos_con_bl = "
       AND o.Fol_folio IN (SELECT fol_folio FROM t_recorrido_surtido WHERE fol_folio = o.Fol_folio) 
       AND (SELECT COUNT(*) FROM th_pedido WHERE fol_folio IN (SELECT fol_folio FROM th_subpedido) AND STATUS = 'S') = 0 ";
       //#SOLO CON STATUS = 'S'
       //SOLO CON STATUS = 'S', este = 'S' tambien es fijo no variable
          //$ver_subpedido_A = " AND (SELECT claverp FROM t_recorrido_surtido WHERE fol_folio = thsp.Fol_folio AND Cve_articulo = tdsp.Cve_articulo LIMIT 1) != ''";
      }

      $sql_search = "";$sql_union_search = "";
      if (!empty($search) ) //&& !empty($filtro)
      {
            $sql_search = " AND (o.Fol_folio like '%$search%' OR o.Pick_Num like '%$search%' OR p.Descripcion like '%$search%' OR c.RazonSocial like '%$search%' OR u.nombre_completo like '%$search%' OR od.Cve_articulo like '%$search%') ";

            $sql_union_search = " AND (thsp.Fol_folio LIKE '%$criterio%' OR th.Pick_Num LIKE '%$criterio%' OR p.Descripcion LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%' OR c_usuario.nombre_completo LIKE '%$criterio%' OR tdsp.Cve_articulo like '%$criterio%') ";

            /*$sql_union_backorder .= "WHERE (b.Fol_Folio LIKE '%$criterio%' OR b.Pick_Num LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%')";*/

            $sql2 .= "AND o.Fol_folio like '%$search%' OR o.Pick_Num like '%$search%' OR c.RazonSocial like '%$search%' OR u.nombre_completo like '%$search%'";
      }

      $sql_disponible = " '1' AS disponible, ";
      $sql_disponible2 = " '1' AS disponible, ";
      
      //&& (strpos($_SERVER['HTTP_HOST'], 'wms.ql.') === false)
if((strpos($_SERVER['HTTP_HOST'], 'avavex') === false) && (strpos($_SERVER['HTTP_HOST'], 'rie') === false) && (strpos($_SERVER['HTTP_HOST'], 'dicoisa') === false))
{
    $sql_disponible = "    
    IF(IFNULL(o.Ship_Num, '') = '',
    
    (SELECT SUM(IF(COALESCE(
    IF(IFNULL(tsbx.cve_lote, '') = '', 
(SELECT SUM(Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))
), 
(SELECT SUM(V_ExistenciaG.Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaG 
LEFT JOIN V_ExistenciaGral veg ON veg.cve_articulo = V_ExistenciaG.cve_articulo and IFNULL(veg.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') 
WHERE V_ExistenciaG.cve_articulo = tsbx.Cve_Articulo AND IFNULL(tsbx.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND IFNULL(veg.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND V_ExistenciaG.cve_almac = '{$almacen}' AND veg.cve_articulo = tsbx.Cve_Articulo
AND ((IFNULL(V_ExistenciaG.cve_lote, '') IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaG.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaG.cve_articulo = ax.cve_articulo AND IFNULL(tsbx.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND IFNULL(ax.Caduca, 'N') = 'N'))
))
, 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))
) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  #LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = o.Fol_folio
AND th_pedido.cve_almac = '{$almacen}')

, 

    (SELECT SUM(IF(COALESCE(
IF(IFNULL(tsbx.cve_lote, '') = '',
(SELECT SUM(Existencia) AS suma 
FROM V_ExistenciaGralProduccion V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))
)
,
(SELECT SUM(Existencia) AS suma 
FROM V_ExistenciaGralProduccion V_ExistenciaG 
WHERE V_ExistenciaG.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaG.cve_lote = tsbx.cve_lote AND V_ExistenciaG.cve_almac = '{$almacen}' 
AND ((V_ExistenciaG.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaG.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaG.cve_articulo = ax.cve_articulo AND tsbx.cve_lote = V_ExistenciaG.cve_lote AND IFNULL(ax.Caduca, 'N') = 'N'))
)
)
, 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGralProduccion V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  #LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = o.Fol_folio
AND th_pedido.cve_almac = '{$almacen}')

) AS disponible,

";
/*
$sql_disponible2 = "(SELECT SUM(IF(COALESCE((SELECT SUM(Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = tdsp.fol_folio
AND th_pedido.cve_almac = '{$almacen}') AS disponible,";
*/
}

      $sql = "
      SELECT * FROM (
        SELECT 
          o.id_pedido, 
          '0' AS es_ot,
          o.Fol_folio AS orden, 
          0 AS sufijo, 
          IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), '') AS Folio_BackO,
          IFNULL(o.Pick_Num, '--') AS orden_cliente, 
          #IFNULL(o.destinatario, '--') AS destinatario,
          IFNULL(IF((o.destinatario = NULL OR o.destinatario = '') AND o.cve_ubicacion = '', '--', IF(o.cve_ubicacion != '', (SELECT descripcion FROM t_ruta WHERE cve_ruta = o.cve_ubicacion), '--')), '') AS destinatario,
          IFNULL(p.Descripcion, '--') AS prioridad, 
          CASE  
          WHEN LEFT(o.Fol_folio,2) = 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
          WHEN LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO' THEN (SELECT DESCRIPCION FROM cat_estados WHERE ESTADO = 'A')
          ELSE IFNULL(e.DESCRIPCION, '--') 
          END AS status, 
          o.status AS status_pedido,
          IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = o.Fol_folio), 1) AS TieneOla,
          IF(op.Folio_Pro = o.Fol_folio, CONCAT('(',op.Cve_Articulo,') ', (SELECT des_articulo FROM c_articulo WHERE cve_articulo = op.Cve_Articulo), ' - Cantidad: ', op.Cantidad), '--') AS articulo_ot,
          IF(LEFT(o.Fol_folio,2) = 'WS', (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = o.Fol_folio LIMIT 1), '') AS folio_ws,
          IFNULL(c.RazonSocial, '--') AS cliente, 
          IFNULL(c.CalleNumero, '--') AS direccion, 
          IFNULL(c.CodigoPostal, '--') AS dane, 
          IFNULL(c.Ciudad, '--') AS ciudad, 
          IFNULL(c.Estado, '--') AS estado, 
          IF(LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO', (SELECT COUNT(DISTINCT Cve_Articulo) FROM td_backorder WHERE Folio_BackO = (SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio) LIMIT 1), COUNT(DISTINCT od.cve_articulo)) AS num_articulos,
          '--' AS cantidad_surtida,
          IF(LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO', (SELECT SUM(Cantidad_Pedido) FROM td_backorder WHERE Folio_BackO = (SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio) LIMIT 1), IFNULL(COALESCE((SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = o.Fol_folio), 0), '--')) AS cantidad, 

          {$sql_disponible}

          #'1' AS disponible,
          #IFNULL(ROUND(SUM(od.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))), 4), '0') AS volumen, 
          '0' AS volumen, 
          #IFNULL(SUM(od.Num_cantidad * a.peso), '0') AS peso, 

          #IF(op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0, IFNULL(SUM(od.Num_cantidad * a.peso), '0'), 
          #IF(LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO', (SELECT SUM(Cantidad_Pedido) FROM td_backorder WHERE Folio_BackO = (SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio) LIMIT 1), IFNULL(COALESCE((SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = o.Fol_folio), 0), '--'))
          #) AS peso,
          '0' AS peso,

          IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido, 
          IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS Fec_Entrega, 
          o.Fec_Entrada AS Fec_Entrega_ord,
          IFNULL(DATE_FORMAT(o.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso, 
          IFNULL(DATE_FORMAT(thsub.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_ini, 
          IFNULL(DATE_FORMAT(thsub.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_fi, 
          IFNULL(concat(datediff(DATE_FORMAT(Hora_Final, '%Y-%m-%d'), DATE_FORMAT(Hora_inicio, '%Y-%m-%d')),'  ',timediff(DATE_FORMAT(Hora_Final, '%H:%i:%s'),  DATE_FORMAT(Hora_inicio, '%H:%i:%s'))), '--') as TiempoSurtido, 
          IFNULL(concat(FLOOR((sum(s.Cantidad)*100)/ sum(od.Num_cantidad)),'%'), '0%') AS surtido, 
          IFNULL(o.BanEmpaque, 0) as bloqueado,
          IFNULL(u.nombre_completo, '--') AS asignado 
        FROM th_pedido o 
          LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad 
          LEFT JOIN cat_estados e ON e.ESTADO = o.status 
          LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte   
          LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio 
          LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio 
          LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario 
          LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = od.Cve_articulo 
          LEFT JOIN t_recorrido_surtido trs on trs.fol_folio = od.Fol_folio and trs.Cve_articulo = od.Cve_articulo 
          LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac 
          LEFT JOIN t_ordenprod op ON op.Folio_Pro = o.Fol_folio
        WHERE o.Activo = 1 and o.id_pedido != '' {$sql_search} 
        $status_A 
        $subpedidos_con_bl 
      ";


      $status_subpedidos = "";
      if (!empty($status)) 
      {
          //$status_subpedidos = " AND th_pedido.status = '{$status}'";
          $status_subpedidos = " AND thsp.status = '{$status}' ";
      }
/*
      $sql_union = "
      SELECT DISTINCT
              th_pedido.id_pedido,
                    td_pedido.fol_folio AS orden,
                    t_recorrido_surtido.Sufijo AS sufijo,
                    '' AS Folio_BackO,
                    IFNULL(th_pedido.Pick_Num, '--') AS orden_cliente, 
                    th_pedido.destinatario AS destinatario,
                    IFNULL(p.Descripcion, '--') AS prioridad, 
                    CASE LEFT(th_pedido.Fol_folio,2) 
                    WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
                    ELSE IFNULL(e.DESCRIPCION, '--') 
                    END AS STATUS, 
                    th_pedido.status AS status_pedido,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = th_pedido.Fol_folio), 1) AS TieneOla,
                    IFNULL(c.RazonSocial, '--') AS cliente, 
                    IFNULL(c.CalleNumero, '--') AS direccion, 
                    IFNULL(c.CodigoPostal, '--') AS dane, 
                    IFNULL(c.Ciudad, '--') AS ciudad, 
                    IFNULL(c.Estado, '--') AS estado, 
                    '--' AS cantidad,
                    '0' AS volumen,
                    '0' AS peso,
                    IFNULL(DATE_FORMAT(th_pedido.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    th_pedido.Fec_Entrada AS Fec_Entrega,
                    IFNULL(DATE_FORMAT(th_pedido.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    '--' AS fecha_ini, 
                    '--' AS fecha_fi, 
                    '--' AS TiempoSurtido,
                    '0%' AS surtido,
                    '' as bloqueado,
                    IFNULL(c_usuario.nombre_completo, '--') AS asignado 
                FROM td_pedido
                    INNER JOIN t_recorrido_surtido ON t_recorrido_surtido.fol_folio = td_pedido.Fol_folio AND t_recorrido_surtido.Cve_articulo = td_pedido.Cve_articulo
                    LEFT JOIN c_lotes ON c_lotes.id = t_recorrido_surtido.cve_lote
                    LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_recorrido_surtido.cve_usuario
                    LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
                    LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio $status_subpedidos
                    LEFT JOIN cat_estados e ON e.ESTADO = th_pedido.status 
                    LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th_pedido.ID_Tipoprioridad 
                    LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                    LEFT JOIN (
                        SELECT 
                            V_ExistenciaGral.cve_articulo,
                            c_ubicacion.CodigoCSD,
                            MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                        FROM V_ExistenciaGral 
                            LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                        GROUP BY cve_articulo
                    ) Z ON Z.cve_articulo = td_pedido.Cve_articulo
                  WHERE th_pedido.id_pedido != ''
      ";
*/
      $sql_union = "
      SELECT DISTINCT 
        thsp.Fol_folio AS id_pedido,
                    IF(opx.Folio_Pro = th.Fol_folio, 1, 0) AS es_ot,
                    thsp.fol_folio AS orden,
                    thsp.Sufijo AS sufijo,
                    '' AS Folio_BackO,
                    IFNULL(th.Pick_Num, '--') AS orden_cliente, 
                    #IFNULL(th.destinatario, '--') AS destinatario,
                    IFNULL(IF((th.destinatario = NULL OR th.destinatario = '') AND th.cve_ubicacion = '', '--', IF(th.cve_ubicacion != '', (SELECT descripcion FROM t_ruta WHERE cve_ruta = th.cve_ubicacion), '--')), '') AS destinatario,
                    IFNULL(p.Descripcion, '--') AS prioridad, 
                    CASE LEFT(thsp.Fol_folio,2) 
                    WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
                    ELSE IFNULL(e.DESCRIPCION, '--') 
                    END AS STATUS, 
                    thsp.status AS status_pedido,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = thsp.Fol_folio), 1) AS TieneOla,
                    IF(opx.Folio_Pro = th.Fol_folio, CONCAT('(',opx.Cve_Articulo,') ', (SELECT des_articulo FROM c_articulo WHERE cve_articulo = opx.Cve_Articulo), ' - Cantidad: ', opx.Cantidad), '--') AS articulo_ot, 
                    IF(LEFT(th.Fol_folio,2) = 'WS', (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = th.Fol_folio LIMIT 1), '') AS folio_ws,
                    IFNULL(IF(LEFT(thsp.Fol_folio, 2) = 'TR', CONCAT(pr.Nombre, '<br>', cal.nombre), c.RazonSocial), '--') AS cliente, 
                    IFNULL(c.CalleNumero, '--') AS direccion, 
                    IFNULL(c.CodigoPostal, '--') AS dane, 
                    IFNULL(c.Ciudad, '--') AS ciudad, 
                    IFNULL(c.Estado, '--') AS estado, 
                    IFNULL((SELECT COUNT(DISTINCT tdsub.Cve_articulo) FROM td_subpedido tdsub WHERE tdsub.fol_folio = thsp.Fol_folio AND tdsub.Sufijo = thsp.Sufijo), '--') AS num_articulos,
                    IF(thsp.status != 'S', IFNULL((SELECT SUM(Cantidad) FROM td_surtidopiezas WHERE fol_folio = thsp.fol_folio AND Sufijo = thsp.Sufijo), '--'), '--') AS cantidad_surtida, 
                    IFNULL((SELECT SUM(tdsub.Num_Cantidad) FROM td_subpedido tdsub WHERE tdsub.fol_folio = thsp.Fol_folio AND tdsub.Sufijo = thsp.Sufijo), '--') AS cantidad, 
                    #'1' AS disponible,

                    {$sql_disponible2}

                    TRUNCATE(((SELECT SUM(IFNULL(ancho/1000, 0)*IFNULL(alto/1000, 0)*IFNULL(fondo/1000, 0)) FROM c_articulo WHERE cve_articulo IN (SELECT Cve_Articulo FROM td_subpedido WHERE Fol_Folio = thsp.Fol_Folio AND Sufijo = thsp.Sufijo))*IFNULL((SELECT SUM(tdsub.Num_Cantidad) FROM td_subpedido tdsub WHERE tdsub.fol_folio = thsp.Fol_folio AND tdsub.Sufijo = thsp.Sufijo), 0)), 3) AS volumen,
                    ((SELECT SUM(peso) FROM c_articulo WHERE cve_articulo IN (SELECT Cve_Articulo FROM td_subpedido WHERE Fol_Folio = thsp.Fol_Folio AND Sufijo = thsp.Sufijo))*IFNULL((SELECT SUM(tdsub.Num_Cantidad) FROM td_subpedido tdsub WHERE tdsub.fol_folio = thsp.Fol_folio AND tdsub.Sufijo = thsp.Sufijo), 0)) AS peso,
                    IFNULL(DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    thsp.Fec_Entrada AS Fec_Entrega,
                    thsp.Fec_Entrada AS Fec_Entrega_ord,
                    IFNULL(DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    IF(thsp.status != 'S', IFNULL(DATE_FORMAT(thsp.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_ini, 
                    IF(thsp.status != 'S', IFNULL(DATE_FORMAT(thsp.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_fi, 
                    IF(thsp.status != 'S', IFNULL(CONCAT(
                                          FLOOR(HOUR(TIMEDIFF(thsp.Hora_Final, thsp.Hora_inicio)) / 24), ' d, ',
                                          MOD(HOUR(TIMEDIFF(thsp.Hora_Final,  thsp.Hora_inicio )), 24), ' h, ',
                                          MINUTE(TIMEDIFF(thsp.Hora_Final, thsp.Hora_inicio )), ' min'), '--'), '--') AS TiempoSurtido, 
                    #IF(IFNULL(COALESCE(SUM(td.Num_cantidad), 0), 0) != 0 AND thsp.status != 'S', CONCAT(TRUNCATE(IFNULL(COALESCE(SUM(tdsp.Num_Cantidad), 0), 0)/COALESCE(SUM(td.Num_cantidad), 0)*100, 2), '%'), '0%') AS surtido,
                    IF((SELECT SUM(Num_cantidad) FROM td_pedido WHERE fol_folio = thsp.fol_folio) != 0, 
                    CONCAT(TRUNCATE((SELECT SUM(Num_Cantidad) FROM td_subpedido WHERE fol_folio = thsp.fol_folio AND Sufijo = thsp.Sufijo)/(SELECT SUM(Num_cantidad) FROM td_pedido WHERE fol_folio = thsp.fol_folio), 3)*100, '%'), '0%') AS surtido,
                    '0' as bloqueado,
                    IFNULL(c_usuario.nombre_completo, '--') AS asignado 
                FROM td_subpedido tdsp 
                    LEFT JOIN th_subpedido thsp ON thsp.Fol_folio = thsp.Fol_folio AND thsp.Sufijo = thsp.Sufijo $ver_subpedido_A 
                    LEFT JOIN th_pedido th ON th.Fol_folio = thsp.fol_folio 
                    #LEFT JOIN td_pedido td ON thsp.Fol_folio = td.fol_folio 
                    LEFT JOIN t_ordenprod opx ON th.Fol_folio = opx.Folio_Pro
                    #LEFT JOIN c_lotes ON c_lotes.id = td.cve_lote 
                    LEFT JOIN c_usuario ON c_usuario.cve_usuario = thsp.cve_usuario #(SELECT cve_usuario FROM t_recorrido_surtido WHERE fol_folio = thsp.Fol_folio AND Cve_articulo = tdsp.Cve_articulo AND Sufijo = thsp.Sufijo LIMIT 1) 
                    #OR c_usuario.cve_usuario = thsp.cve_usuario)
                    LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = tdsp.Cve_articulo 
                    LEFT JOIN c_almacenp cal ON cal.id = th.statusaurora
                    LEFT JOIN c_proveedores pr ON pr.cve_proveedor = th.Cve_clte
                    #LEFT JOIN c_usuario ON c_usuario.cve_usuario = thsp.cve_usuario
                    LEFT JOIN cat_estados e ON e.ESTADO = thsp.status 
                    LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th.ID_Tipoprioridad  
                    LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte 

                  WHERE thsp.Fol_folio != '' {$sql_union_search}  {$status_subpedidos} AND thsp.cve_almac = '{$almacen}'
                  GROUP BY thsp.Fol_folio,thsp.Sufijo 
                  ";
/*

                    LEFT JOIN ( 
                        SELECT 
                            V_ExistenciaGral.cve_articulo, 
                            c_ubicacion.CodigoCSD,
                            MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                        FROM V_ExistenciaGral 
                            LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                        GROUP BY cve_articulo 
                    ) Z ON Z.cve_articulo = tdsp.Cve_articulo 
*/
      $status_backorder = "";
      if (!empty($status)) 
      {
          $status_backorder = " AND b.Status = '{$status}' ";
      }

      $sql_union_backorder = "";
/*
      $sql_union_backorder = "
                SELECT DISTINCT
                    b.Folio_BackO AS id_pedido, 
                    b.Fol_Folio AS orden, 
                    0 AS sufijo, 
                    b.Folio_BackO AS Folio_BackO,
                    b.Pick_num AS orden_cliente, 
                    '' AS destinatario,
                    '--' AS prioridad, 
                    (SELECT DESCRIPCION FROM cat_estados WHERE ESTADO = b.Status) AS STATUS,
                    b.Status AS status_pedido,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = b.Fol_Folio), 1) AS TieneOla,
                    IFNULL(c.RazonSocial, '--') AS cliente, 
                    IFNULL(c.CalleNumero, '--') AS direccion, 
                    IFNULL(c.CodigoPostal, '--') AS dane, 
                    IFNULL(c.Ciudad, '--') AS ciudad, 
                    IFNULL(c.Estado, '--') AS estado, 
                    '--' AS cantidad_surtida,
                    '--' AS cantidad, 
                    '0' AS volumen,
                    '0' AS peso,
                    IFNULL(DATE_FORMAT(b.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    b.Fec_Entrega,
                    IFNULL(DATE_FORMAT(b.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    '--' AS fecha_ini, 
                    '--' AS fecha_fi, 
                    '--' AS TiempoSurtido,
                    '0%' AS surtido,
                    '--' AS asignado 
                FROM td_backorder bo
                    LEFT JOIN th_backorder b ON b.Folio_BackO = bo.Folio_BackO $status_backorder
                    LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = bo.Cve_Articulo
                    LEFT JOIN c_cliente c ON c.Cve_Clte = b.Cve_Clte
                ";
*/
      if (!empty($almacen)) 
      {
        $sql .= " AND o.cve_almac = '{$almacen}' ";
        $sql2 .= " AND o.cve_almac = '{$almacen}' ";
      }
      if (!empty($status)) 
      {
        //$sql .= " AND o.status = '{$status}' ";
       // $sql_union .= " AND thsp.status = '{$status}' ";
        //$sql_union_backorder .= " AND b.status = '{$status}' ";
        //$sql .= " AND (o.status = '{$status}' AND o.Fol_folio NOT LIKE 'WS%') OR (o.status = 'O' AND IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = o.Fol_folio), 0) <> '0') ";
        $sql2 .= " AND o.status = '{$status}' ";
      }
/*
      if (!empty($criterio) ) //&& !empty($filtro)
      {
            $sql .= " AND (o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR p.Descripcion like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%') ";

            //$sql_union .= " AND (tdsp.Fol_folio LIKE '%$criterio%' OR th.Pick_Num LIKE '%$criterio%' OR p.Descripcion LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%' OR c_usuario.nombre_completo LIKE '%$criterio%') ";

            //$sql_union_backorder .= "WHERE (b.Fol_Folio LIKE '%$criterio%' OR b.Pick_Num LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%')";

            $sql2 .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
      }
*/
      //$sql_union_backorder .= " ORDER BY Fec_Entrega DESC";
      //if($search != "")
      //  $sql_union_backorder .= " ORDER BY id_pedido DESC ";//$sql_union_backorder .= " ORDER BY id_pedido DESC ";
      //else 
        $sql_union_backorder .= " ORDER BY Fec_Entrega_ord DESC ";
      

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
      //$sql .= " GROUP BY o.Fol_folio ORDER BY o.id_pedido DESC";
      $sql .= " GROUP BY o.Fol_folio";
    //{$status}
      $sql .= " ) AS res1 WHERE res1.status_pedido = 'A' 
      AND (SELECT COUNT(*) FROM td_pedido WHERE Fol_folio = res1.orden) != (SELECT COUNT(*) num FROM th_subpedido th, td_subpedido td WHERE th.fol_folio = res1.orden AND th.fol_folio = td.fol_folio AND th.status = 'S' 
      AND th.Sufijo = td.Sufijo) OR (res1.status_pedido = 'S' AND res1.sufijo = 0 AND res1.orden IN (SELECT Fol_folio FROM th_backorder)) 

      UNION ".$sql_union."  ".$sql_union_backorder;
      //#este = 'S' es fijo no variable
//      #OR (res1.status_pedido = '{$status}' AND res1.sufijo = 0 AND res1.orden IN (SELECT Fol_folio FROM th_backorder)) 
//     #AND (res1.orden NOT IN (SELECT fol_folio FROM th_subpedido)) 


      //$sql2 .= " GROUP BY o.Fol_folio  ORDER BY o.id_pedido DESC";
      $sql2 .= " GROUP BY Fol_folio  ORDER BY Fec_Entrega DESC";

      //$sql_count = "Select count(*) as x from (".$sql2.") y";

      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      if (!($res_count = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }

      $count = mysqli_num_rows($res_count);

      $sql .= " limit ".$start.", ".$limit;
      
      

      $data = Capsule::select(Capsule::raw($sql));
      
//       echo var_dump($data);
//       die();

      //$data_count = Capsule::select(Capsule::raw($sql_count));


//*********************************************************************************
        $sql3 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = '{$status}'";
        if (!($res3 = mysqli_query($conn, $sql3))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res3);
        $count_pedidos = $row['num_pedidos'];

        if($status == 'A')
        {
          $sql3 = "SELECT COUNT(o.fol_folio) num_pedidos_bo
                  FROM th_backorder o   
                  WHERE o.Status = 'A'";
          if (!($res3 = mysqli_query($conn, $sql3))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res3);
          $count_pedidos += $row['num_pedidos_bo'];
        }



        $sql4 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = 'S'";
        if (!($res4 = mysqli_query($conn, $sql4))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res4);
        $count_asignados = $row['num_pedidos'];


        if($status)
        $sql4 = "SELECT COUNT(o.fol_folio) num_subpedidos
                FROM th_subpedido o   
                WHERE o.status = '{$status}'";
        else
        $sql4 = "SELECT COUNT(o.fol_folio) num_subpedidos
                FROM th_subpedido o ";
        if (!($res4 = mysqli_query($conn, $sql4))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res4);
        $count_subpedidos = $row['num_subpedidos'];


        $sql5 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = 'C'";
        if (!($res5 = mysqli_query($conn, $sql5))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res5);
        $count_empaque = $row['num_pedidos'];
//*********************************************************************************

      $response = new \stdClass;
      $response->data = [];

      //$response->query["sql"] = $sql;
      //$response->query["sql_count"] = $sql_count;

      //$response->data["sql_count"] = $sql_count;

      //$count = count($data);
      //$count = $data_count[0]->x;

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
          'TieneOla' => $row->TieneOla,
          'destinatario' => $row->destinatario,
          'orden' => $row->orden,
          'sufijo' => $row->sufijo,
          'Folio_BackO' => $row->Folio_BackO,
          'orden_cliente' => $row->orden_cliente,
          'prioridad' => $row->prioridad,
          'status' => $row->status,
          'entregado' => '',
          'status_pedido' => $row->status_pedido,
          'num_articulos' => $row->num_articulos,
          'cantidad_surtida' => $row->cantidad_surtida,
          'cantidad' => $row->cantidad,
          'articulo_ot' => $row->articulo_ot,
          'es_ot' => $row->es_ot,
          'folio_ws' => $row->folio_ws,
          'disponible' => $row->disponible,
          'volumen' => $row->volumen,
          'peso' => round($row->peso,3),
          'fecha_pedido' => $row->fecha_pedido,
          'fecha_compromiso' => $row->fecha_compromiso,
          'fecha_ini' => $row->fecha_ini,
          'fecha_fi' => $row->fecha_fi,
          'TiempoSurtido' => $row->TiempoSurtido,  
          'surtido' => $row->surtido,
          'cliente' => $row->cliente,
          'direccion' => $row->direccion,
          'dane' => $row->dane,
          'ciudad' => $row->ciudad,
          'estado' => $row->estado,
          'asignado' => $row->asignado
          //'sql' => $sql
        ];
/*
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
*/
      }
      //$response->data = array_slice($response->data, $start, $limit);
        $response->pesototal = $count_asignados; // Se cambió a Pedidos Asignados pero se usa la variable peso total ya que estaba ahí
        $response->totalpedidos = $count_pedidos;
        $response->totalsubpedidos = $count_subpedidos;
        $response->volumentotal = $count_empaque;

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
      $response->sql = $sql;
      $response->sql_search = $sql_search;

      ob_clean();
      header('Content-Type: application/json; charset=utf-8');
      //echo $sql;
      //echo " ------------- ";
      //echo $sql2;
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
        $almacen = $_POST['almacen'];
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
        $fecha_actual = $this->getFechaActual();

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
/*
        $sql = "SELECT
                *,
                IFNULL(SUM(item.Num_cantidad), 0) AS sum_pedidas
            FROM td_pedido item
                LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
            WHERE item.Fol_folio IN ({$foliosStr})
            GROUP BY art.Cve_Articulo
                ORDER BY item.Fol_folio, item.itemPos;
        ";
*/
        $sql = "SELECT
                  GROUP_CONCAT(item.Fol_folio SEPARATOR ',') AS Fol_folio,
                  item.Cve_articulo,
                  art.control_peso,
                  IFNULL(IF(art.control_peso = 'S', ROUND(SUM(item.Num_cantidad), 4), SUM(item.Num_cantidad)), 0) AS sum_pedidas
                FROM td_pedido item
                LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
                WHERE item.Fol_folio IN ({$foliosStr})
                GROUP BY item.Cve_Articulo, item.cve_lote
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
            $sum_pedidas = $item['sum_pedidas'];
            //if($item['control_peso'] == 'S')
            //{
            //    if(is_float($sum_pedidas))
            //      $sum_pedidas = number_format($sum_pedidas, 4);
            //}

            $model_items = new ConsolidadosOlaItems();
            $model_items->Fol_PedidoCon = $folio_consolidado;
            $model_items->No_OrdComp    = $folio_consolidado;
            $model_items->Fec_OrdCom    = $fecha_entrega;
            $model_items->Cve_Articulo  = $item['Cve_articulo'];
            $model_items->Cant_Pedida   = $sum_pedidas;
            $model_items->Unid_Empaque  = 'BO';
            $model_items->Tot_Cajas     = $sum_pedidas;
            //$model_items->Fact_Madre    = '';
            $model_items->Cve_Clte      = '';
            $model_items->Cve_CteProv   = '';
            $model_items->Fol_Folio     = $item['Fol_folio'];
            $model_items->CodB_Cte      = '';
            $model_items->Cod_PV        = '';
            $model_items->Status        = 'A';

            $model_items->save();


            $model_items_pedido = new PedidosItems();
            $model_items_pedido->fol_folio     = $folio_consolidado;
            $model_items_pedido->Cve_articulo  = $item['Cve_articulo'];
            $model_items_pedido->Num_cantidad  = $sum_pedidas;
            $model_items_pedido->Num_Meses     = '';
            $model_items_pedido->SurtidoXCajas = '';
            $model_items_pedido->SurtidoXPiezas = '';
            $model_items_pedido->status        = 'A';
            $model_items_pedido->cve_cot       = '';
            $model_items_pedido->factor        = '';
            $model_items_pedido->itemPos       = '';
            $model_items_pedido->cve_lote      = $item['cve_lote'];
            $model_items_pedido->Activo        = 1;

            $model_items_pedido->save();

        }
      
        foreach ($folios as $folio_nro)
        {
            //@todo Funciton para actualizar el estatus del pedido para ocultarlo
            Pedidos::where('Fol_folio', $folio_nro)->update(['status'=>'O']);
        }

        //Duplicar pedido pero com Orden de Consolidado de Ola
        $model_header = new Pedidos;
        $model_header->Fol_folio            = $folio_consolidado;
        $model_header->Fec_Pedido           = $fecha_actual;
        $model_header->Cve_clte             = '';
        $model_header->status               = 'A';
        $model_header->Fec_Entrega          = $fecha_entrega;
        $model_header->cve_Vendedor         = '';
        $model_header->Num_Meses            = '';
        $model_header->Observaciones        = '';
        $model_header->statusaurora         = '';
        $model_header->ID_Tipoprioridad     = 0;
        $model_header->Fec_Entrada          = $fecha_actual;
        $model_header->transporte           = '';
        $model_header->ruta                 = '';
        $model_header->bloqueado            = '';
        $model_header->fechadet             = $fecha_actual;
        $model_header->fechades             = $fecha_actual;
        $model_header->cve_almac            = $almacen;
        $model_header->destinatario         = '';
        $model_header->subido               = '';
        $model_header->cve_ubicacion        = '';
        $model_header->Pick_Num             = '';
        $model_header->Cve_Usuario          = $_SESSION['cve_usuario'];
        $model_header->Ship_Num             = '';
        $model_header->BanEmpaque           = '';
        $model_header->Cve_CteProv          = '';
        $model_header->Activo               = 1;
        $model_header->save();

/*
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
*/
         
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


    public function getYearActual()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT YEAR(CURRENT_DATE()) AS YEAR_ACTUAL FROM DUAL";
        $rs = mysqli_query($conn, $sql);
        $year_actual = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $year_actual = $year_actual['YEAR_ACTUAL'];

        return $year_actual;
    }

    public function getMesActual()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 9,CONCAT(0,MONTH(CURRENT_DATE())),MONTH(CURRENT_DATE())) AS MES_ACTUAL FROM DUAL";
        $rs = mysqli_query($conn, $sql);
        $mes_actual = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $mes_actual = $mes_actual['MES_ACTUAL'];

        return $mes_actual;
    }

    public function getFechaActual()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT CURDATE() as Fecha_Actual FROM DUAL";
        $rs = mysqli_query($conn, $sql);
        $Fecha_Actual = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $Fecha_Actual = $Fecha_Actual['Fecha_Actual'];

        return $Fecha_Actual;
    }


    //private $consecutivo_folio = 1;
    public function getConsecutivoFolio($c)
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        //IF(COUNT(*)+1 < 9,CONCAT(0,COUNT(*)+1),COUNT(*)+1)
        $sql = "SELECT COUNT(*)+1 AS Consecutivo
                FROM th_pedido 
                WHERE Fol_folio LIKE CONCAT('S', YEAR(CURRENT_DATE()), IF(MONTH(CURRENT_DATE()) < 9, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())), '%')";
                //MONTH(Fec_Pedido) = MONTH(CURRENT_DATE()) AND YEAR(Fec_Pedido) = YEAR(CURRENT_DATE())
        $rs = mysqli_query($conn, $sql);
        $folio_cont = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $folio_cont = $folio_cont['Consecutivo'] + $c;

        if($folio_cont < 10) 
        {
            $folio_cont = "0".$folio_cont; 
        }


        $folio_cont_prueba = $folio_cont;
        $folio_cont_prueba = "S".$this->getYearActual().$this->getMesActual().$folio_cont_prueba;

        while(true)
        {
            $sql = "SELECT COUNT(*) AS Num_Consecutivo FROM th_pedido WHERE Fol_Folio = '$folio_cont_prueba'";
            $rs = mysqli_query($conn, $sql);
            $folio_cont_prueba = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $folio_cont_prueba = $folio_cont_prueba['Num_Consecutivo'];

            if($folio_cont_prueba > 0)
            {
                $folio_cont+=0; //lo paso de string a int
                $folio_cont++;
                if($folio_cont < 10) 
                {
                    $folio_cont = "0".$folio_cont; 
                }
                $folio_cont_prueba = "S".$this->getYearActual().$this->getMesActual().$folio_cont;
            }
            else 
                break;
        }

        return $folio_cont;
    }

function unix_timestamp_to_human ($timestamp = "", $format = 'Y-m-d H:i:s')
{
   if (empty($timestamp) || ! is_numeric($timestamp)) $timestamp = time();
   return ($timestamp) ? date($format, $timestamp) : date($format, $timestamp);
}


    public function ImportarTH()
    {

        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $tipo = $_POST["tipo"];
        $almacen_val = $_POST["almacenes"];
        $date=date_create($_POST["fecha_pedido"]);
        $fecha_pedido = date_format($date,"Y-m-d");
        $date=date_create($_POST["fecha_entrega"]);
        $fecha_entrega = date_format($date,"Y-m-d");
        $folio_pedido = $_POST["folio_pedido"];
        $prioridad = $_POST["prioridad"];
        $num_orden = $_POST["num_orden"];
        $desc_cliente = $_POST["desc_cliente"];
        $destinatario = $_POST["destinatario"];
        $observacion = $_POST["observacion"];

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT clave FROM c_almacenp WHERE id = $almacen_val";
        $rs = mysqli_query($conn, $sql);
        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $almacen_val_clave = $resul['clave'];

        $num_pedidos = 0;
        $secuencia = 0;
        $Fol_folio = "";
        $linea = 1;
        $lineas = $xlsx->rows();
        $articulos_no_existentes = "";
        $clientes_no_existentes = "";
        $registro_anterior = "";
        $registro_anterior_cliente = "";
        $orden_de_compra = 0;
        $consecutivo_folio = 0;
        //$destinatario = "";
        $folio_cont = "";
        $folio_anterior = "";
        $clave_cliente = "";
        $registrar = 1;
        $registrar_pedido_th = false;
        $registrar_por_cliente = true;
        $folios_registrados = array();
        $tiendas_registradas = array();
        $clientes_registrados = array();
        $mensaje_dev = "";
        $tracking_codigo = "";
        $cve_articulo_registrado = "";
        $ya_existe_folio = "";
        $mensaje_folio_exist = "";
        $folios_exist = array();
        $ok_pedido = true;
        $sql_exist = "";
        $row_hack = "";
        $folios_repetidos = "";
        $folios_no_validos = "";
        $lotes_series_no_existentes = "";
        foreach ($xlsx->rows() as $row)
        {
              if($linea == 1 && $this->pSQL($row[3]) != "") $ok_pedido = false; 
              // SI HAY UNA COLUMNA ACTIVA EN LA COLUMNA[3] SIGNIFICA QUE ESTÁN USANDO OTRO LAYOUT

              if($linea > 1 && $ok_pedido)
              {//s60
                  $cve_almac = $almacen_val;
                  $cve_clte = "";
                  $cve_dest = "";
                  $cve_articulo = "";
                  $Sku = "";

                  $registrar = 0;
                  if($folio_anterior != $folio_pedido)
                       $registrar = 1;

                  if($registrar == 1)
                  {//s38
                        $model = new Pedidos(); 

                        $Fol_folio = $folio_pedido;
                        $Cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                        $cve_almac = $almacen_val;
                        $cve_lote      = $this->pSQL($row[self::LOTE_SERIE]);
                        //$registrar = 0;

                        $clave_cliente = $desc_cliente;
                        $sql = "SELECT  Cve_Clte as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $cliente = $resul['cliente'];

                        $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                        $model->Fol_folio        = $folio_pedido;
                        $model->Cve_clte         = $cliente;

                        $model->Fec_Pedido       = $fecha_pedido;
                        $model->Fec_Entrada      = $fecha_pedido;
                        $model->Fec_Entrega      = $fecha_entrega;
                        $model->ID_Tipoprioridad = $prioridad;
                        $model->cve_almac        = $almacen_val;
                        $model->Observaciones    = $observacion;
                        $model->Pick_Num         = $num_orden;
                        $model->destinatario     = $destinatario;
                        $model->status           = 'A';
                        $model->Cve_CteProv      = $clave_cliente;
                        //$registrar = 0;

                        $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $num_articulo = $resul['num_articulo'];

                        $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $cliente = $resul['cliente'];

                        $tracking_codigo .= "[$linea - 2->num_articulo = $num_articulo, cliente = $cliente]";

                        if($num_articulo && $cliente)
                        {//s27
                            $mensaje_dev .= "Folio = ".$folio_pedido."\n\n";
                            $folio_dest = $folio_pedido;
                            //$destinatario = $this->pSQL($row[self::CVE_DESTINATARIO]);

                            //if($cve_articulo_registrado != $cve_articulo)
                            $sql = "SELECT control_lotes, control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $control_lotes         = $resul['control_lotes'];
                            $control_numero_series = $resul['control_numero_series'];


                            $existe_lote = 0; $existe_serie = 0; $registrar_lote_serie = true; $lote =""; $serie = "";
                            if($this->pSQL($row[self::LOTE_SERIE]) != '')
                            {
                                if($control_lotes == 'S')
                                {
                                    $lote = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '$cve_articulo' AND Lote = '$lote'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_lote = $resul['existe'];
                                }

                                if($control_numero_series == 'S')
                                {
                                    $serie = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '$cve_articulo' AND numero_serie = '$serie'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_serie = $resul['existe'];
                                }

                                if((($existe_lote == 0 || ($lote == '' && $serie == '')) && $control_lotes == 'S') || (($existe_serie == 0 || ($lote == '' && $serie == '')) && $control_numero_series == 'S'))
                                {
                                    $registrar_lote_serie = false;
                                }
                            }

                          if((($registrar_lote_serie == true && ($control_lotes == 'S' || $control_numero_series == 'S')) || ($lote == '' && $serie == '')) || $cve_lote == '')
                            {
                                $respuesta = $model->save();
                                $cve_articulo_registrado = $cve_articulo;

                                $sql = "INSERT INTO Rel_PedidoDest(Fol_Folio, Cve_Almac, Id_Destinatario) VALUES (
                                             '".$folio_dest."', 
                                             '".$almacen_val_clave."', 
                                             ".$destinatario."
                                        );";
                                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
                                $tracking_codigo .= "[$linea -3]";

                                //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
                                //$consecutivo_folio++;
                                if($orden_de_compra != $folio_pedido)
                                {//s24
                                    //$num_pedidos++;
                                    $orden_de_compra = $folio_pedido;
                                    $secuencia = 0;
                                }//s24
                            }
                            //else
                            //  $lotes_series_no_existentes .= $lote.$serie."\n";
                            //else
                            //    $num_pedidos++;
                        }//s27
                        else
                        {//s28
                            if(!$num_articulo && $cve_articulo)
                            {//s29
                                $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                                if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                                $registro_anterior = $registro_actual;
                            }//s29
                            $tracking_codigo .= "[$linea -3X]";

                            if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                            {//s30
                                $registro_actual_cliente = $cve_clte."\n";
                                if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                                $registro_anterior_cliente = $registro_actual_cliente;
                                array_push($clientes_registrados, $cve_clte);
                            }//s30
                        }//s28

                        $registrar = 0;
                        $folio_anterior = $folio_pedido;
                  }//s38

                  //$items = PedidosItems::where('Fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->first();

                  //if($items != NULL){//s39
                  //    $model = $items; 
                  //}//s39
                  //else {//s40
                      $model = new PedidosItems(); 
                  //}//s40

                    $cve_articulo  = $this->pSQL($row[self::CVE_ARTICULO]);
                    $cve_lote      = $this->pSQL($row[self::LOTE_SERIE]);
                    $clave_cliente = $desc_cliente;

                    $model->Fol_folio        = $folio_pedido;
                    $model->Cve_articulo     = $this->pSQL($row[self::CVE_ARTICULO]);
                    $model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
                    $model->Num_Meses        = '0';
                    $model->status           = 'A';

                    $Folio = $folio_pedido;

                    $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $num_articulo = $resul['num_articulo'];

                    $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['cliente'];

                    $sql = "SELECT  COUNT(*) AS pedido FROM td_pedido WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $pedido_articulo = $resul['pedido'];

                    $tracking_codigo .= "[$linea -4 --> num_articulo = $num_articulo, cliente = $cliente, Cve_articulo = $cve_articulo, pedido_articulo = $pedido_articulo]";

                    if($num_articulo && $cliente)
                    {//s44
                        if($pedido_articulo)
                        {
                            $cantidad_agr = $this->pSQL($row[self::NUM_CANTIDAD]);
                            if($cantidad_agr)
                            {
                              $sql = "UPDATE td_pedido SET Num_cantidad = (Num_cantidad + $cantidad_agr) WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote'";
                              // AND '$clave_cliente' IN (SELECT Cve_clte FROM th_pedido WHERE Fol_folio='$Folio')
                              $rs = mysqli_query($conn, $sql);
                            }
                            $tracking_codigo .= "[$linea -5X, Cve_articulo = $cve_articulo]";
                        }
                        else //if($cve_articulo_registrado != $cve_articulo)
                        {
                            //$model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);

                            $sql = "SELECT control_lotes, control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $control_lotes         = $resul['control_lotes'];
                            $control_numero_series = $resul['control_numero_series'];

                            $existe_lote = 0; $existe_serie = 0; $registrar_lote_serie = true; $lote = ""; $serie = "";
                            if($this->pSQL($row[self::LOTE_SERIE]) != '')
                            {
                                if($control_lotes == 'S')
                                {
                                    $lote = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '$cve_articulo' AND Lote = '$lote'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_lote = $resul['existe'];
                                    $model->cve_lote         = $cve_lote;
                                }

                                if($control_numero_series == 'S')
                                {
                                    $serie = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '$cve_articulo' AND numero_serie = '$serie'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_serie = $resul['existe'];
                                    $model->cve_lote         = $cve_lote;
                                }

                                if(((($existe_lote == 0 || ($lote == '' && $serie == '')) && $control_lotes == 'S') || (($existe_serie == 0 || ($lote == '' && $serie == '')) && $control_numero_series == 'S')) || $cve_lote == '')
                                {
                                    $registrar_lote_serie = false;
                                }
                            }

                            if(($registrar_lote_serie == true && ($existe_lote > 0 && $control_lotes == 'S') || ($existe_serie > 0 && $control_numero_series == 'S')) || ($lote == '' && $serie == ''))
                            {
                              $respuesta = $model->save();
                            }
                            else if($cve_lote)
                              $lotes_series_no_existentes .= $cve_lote."\n";

                            $tracking_codigo .= "[$linea -5Y, Cve_articulo = $cve_articulo]";
                        }

                        $cve_articulo_registrado = $cve_articulo;

                        $tracking_codigo .= "[$linea -5]";

                        if($orden_de_compra != $folio_pedido)
                        {//s45
                            $num_pedidos++;
                            $orden_de_compra = $folio_pedido;
                            $secuencia = 0;
                        }//s45
                        else
                            $num_pedidos++;
                    }//s44
                    else
                    {//s46
                        if(!$num_articulo && $cve_articulo)
                        {//s47
                            $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                            if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                            $registro_anterior = $registro_actual;
                        }//s47

                        if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                        {//s48
                            $registro_actual_cliente = $cve_clte."\n";
                            if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                            $registro_anterior_cliente = $registro_actual_cliente;
                            array_push($clientes_registrados, $cve_clte);
                        }//s48
                        $tracking_codigo .= "[$linea -5X]";
                    }//s46


                    $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $num_articulo = $resul['num_articulo'];

                    $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['cliente'];

                    $tracking_codigo .= "[$linea -6 --> num_articulo = $num_articulo, cliente = $cliente, Cve_articulo = $cve_articulo]";

                    if($num_articulo && $cliente)
                    {//s50
                        $Sku_R = "200".$Sku;
                        //***************************************************************
                        //       Función para cálculo de dígito verificador UPCA
                        //***************************************************************
                            $impares = 0;
                            $pares = 0;
                            for($i = 0; $i < strlen($Sku_R); $i++)
                              if($i%2==0)
                                $impares += $Sku_R[$i];
                              else
                                $pares += $Sku_R[$i];

                            $imparesx3 = $impares*3;
                            $imparesx3maspares = $imparesx3+$pares;
                            $imparesx3maspares_save = $imparesx3maspares;
                            while($imparesx3maspares%10!=0)
                              $imparesx3maspares++;
                            $codigo = $imparesx3maspares - $imparesx3maspares_save;

                            $Sku_R .= $codigo;
                        //***************************************************************
                        //                        By RM/AO
                        //***************************************************************

                        $sql = "INSERT INTO c_articulo_codigo (Cve_Almacen, Cve_Articulo, Cve_Clte, Codigo, Sku_R, Descripcion) Values ";
                        $sql .= "('".$cve_almac."', '".$cve_articulo."', '".$cve_clte."', '".$Sku."', '".$Sku_R."', 'A')";
                        $sql .= " ON DUPLICATE KEY UPDATE Codigo='$Sku', Sku_R='$Sku_R', Descripcion='A'";
                        $rs = mysqli_query($conn, $sql);

                        $tracking_codigo .= "[$linea -7]";

                    }//s50
              }//s60
              $linea++;
        }

      $statusText = "El Formato del Layout está Incorrecto.";
      if($ok_pedido)
      {
        $mensaje_no_existentes = ""; $mensaje_clientes_no_existentes = "";
        if($articulos_no_existentes && $num_pedidos)
           $mensaje_no_existentes = "\n\n\nSin embargo, Los siguientes productos no se encuentran en el Catálogo: \n\n\n ". $articulos_no_existentes;

        $mensaje_lotes_series_no_existentes = "";
        if($lotes_series_no_existentes != "")
           $mensaje_lotes_series_no_existentes = "\n\n\nLos siguientes Lotes no existen en el sistema: \n\n\n ".$lotes_series_no_existentes;

         if($clientes_no_existentes != "")
            $mensaje_clientes_no_existentes = "\n\n\nLos siguientes clientes no existen en el sistema: \n\n $clientes_no_existentes";

         $statusText = "Pedidos importados con exito. Total de Pedidos: \"{$num_pedidos}\" $mensaje_no_existentes".$mensaje_clientes_no_existentes.$mensaje_lotes_series_no_existentes;

         if($folios_repetidos) $mensaje_folio_exist = "\n\nFolios Repetidos: \n\n".$folios_repetidos;

         if($num_pedidos == 0 && $mensaje_clientes_no_existentes == "" && $mensaje_folio_exist == "")
            $statusText = "No se ha ingresado ningún pedido".$folios_no_validos;
          else if($mensaje_folio_exist != "" || $folios_no_validos != "")
            $statusText .= $mensaje_folio_exist.$folios_no_validos;
      }

      
        $this->response(200, [

            'statusText' =>  $statusText,
            'pedidos' => $num_pedidos,
            'mensaje_dev' => $mensaje_dev,
            'tracking_codigo' => $tracking_codigo,
            "lineas"=>$lineas
        ]);

    }

/*
    public function ImportarTH()
    {

        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $tipo = $_POST["tipo"];
        $almacen_val = $_POST["almacenes"];

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT clave FROM c_almacenp WHERE id = $almacen_val";
        $rs = mysqli_query($conn, $sql);
        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $almacen_val_clave = $resul['clave'];

        $num_pedidos = 0;
        $secuencia = 0;
        $Fol_folio = "";
        $linea = 1;
        $lineas = $xlsx->rows();
        $articulos_no_existentes = "";
        $clientes_no_existentes = "";
        $registro_anterior = "";
        $registro_anterior_cliente = "";
        $orden_de_compra = 0;
        $consecutivo_folio = 0;
        $destinatario = "";
        $folio_cont = "";
        $folio_anterior = "";
        $clave_cliente = "";
        $registrar = 1;
        $registrar_pedido_th = false;
        $registrar_por_cliente = true;
        $folios_registrados = array();
        $tiendas_registradas = array();
        $clientes_registrados = array();
        $mensaje_dev = "";
        $tracking_codigo = "";
        $cve_articulo_registrado = "";
        $ya_existe_folio = "";
        $mensaje_folio_exist = "";
        $folios_exist = array();
        $ok_pedido = true;
        $sql_exist = "";
        $row_hack = "";
        $folios_repetidos = "";
        $folios_no_validos = "";
        $lotes_series_no_existentes = "";
        foreach ($xlsx->rows() as $row)
        {
              $folio_rev = $this->pSQL($row[self::FOLIO]);

              $folio_rev = str_replace(' ', '', $folio_rev);
              $folio_rev = str_replace('+', '', $folio_rev);
              $folio_rev = str_replace("'", '', $folio_rev);

              $no_valid = array("(","á","|","é","í","ó","ú","Ä","Ë","Ï","Ö","Ü","ä","ë","ï","ö","ü","¨","´","{","}","Á","É","Í","Ó","Ú","ñ","Ñ","`","!","@","=","*","^","[","]","ç","Ç",":",".",";",",",")");

              $folio_rev = str_replace($no_valid, '', $folio_rev);
/*
              if (preg_match('(á|é|í|ó|ú|Ä|Ë|Ï|Ö|Ü|ä|ë|ï|ö|ü|¨|´|{|}|Á|É|Í|Ó|Ú|ñ|Ñ|"|`|#|!|@|=|#|\*|^|[|]|ç|Ç|:|.|;|,)', $folio_rev))
              {
                $folios_no_validos = "\n\nHay Folios con caracteres no válidos";
                continue;
              }
*/
              //if($folio_rev == "") continue;

              //$sql = "SELECT COUNT(*) as folio FROM th_pedido WHERE Fol_folio = '$folio_rev'";
              //$rs = mysqli_query($conn, $sql);
              //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              //$folio_res = $resul['folio'];
              //$tracking_codigo .= "[$linea - 1]";
              //if($folio_res > 0) 
              //{
              //    if(!in_array($folio_rev, $folios_exist)) 
              //    {
              //        array_push($folios_exist, $folio_rev);
              //    }
                  //else
                    //$folios_repetidos .= $folio_rev."\n";
                /*
                  else
                  {
                    $ya_existe_folio = "El Folio (".$folio_rev.") ya existe\n\n"; 
                    $mensaje_folio_exist .= $ya_existe_folio;
                    $ok_pedido = false;
                  }
                **************
                //continue;
              //}

              if($linea == 1 && $this->pSQL($row[3]) == "") $ok_pedido = false; 
              // SI HAY UNA COLUMNA ACTIVA EN LA COLUMNA[3] SIGNIFICA QUE ESTÁN USANDO OTRO LAYOUT


              if($linea > 1 && $ok_pedido)
              {//s60
                  $cve_almac = $almacen_val;
                  $cve_clte = "";
                  $cve_dest = "";
                  $cve_articulo = "";
                  $Sku = "";

                  $registrar = 0;
                  if($folio_anterior != $this->pSQL($row[self::FOLIO]))
                       $registrar = 1;

                  if($registrar == 1)
                  {//s38
                      $model = new Pedidos(); 

                      $Fol_folio = $this->pSQL($row[self::FOLIO]);
                      $Cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                      $cve_almac = $almacen_val;
                      //$registrar = 0;

                      $clave_cliente = $this->pSQL($row[self::CLAVE_CLIENTE]);
                      $sql = "SELECT  Cve_Clte as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                      $rs = mysqli_query($conn, $sql);
                      $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      $cliente = $resul['cliente'];

                      $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                      $model->Fol_folio        = $this->pSQL($row[self::FOLIO]);
                      $model->Cve_clte         = $cliente;

                      $excel_date = $this->pSQL($row[self::FECHA_PEDIDO]);
                      $unix_date = ($excel_date - 25569) * 86400;
                      $excel_date = 25569 + ($unix_date / 86400);
                      $unix_date = ($excel_date - 25569) * 86400;
                      $fecha = gmdate("Y-m-d", $unix_date);
                      //$fecha = gmdate("d-m-Y", $unix_date);
                      //$fecha = gmdate("d/m/Y", $unix_date);
                      //$fecha = gmdate("Y/m/d", $unix_date);
                      //$model->Fec_Pedido       = $fecha;
                      $model->Fec_Pedido       = $this->pSQL($row[self::FECHA_PEDIDO]);
                      $excel_date = $this->pSQL($row[self::FECHA_ENTRADA]);
                      $unix_date = ($excel_date - 25569) * 86400;
                      $excel_date = 25569 + ($unix_date / 86400);
                      $unix_date = ($excel_date - 25569) * 86400;
                      $fecha = gmdate("Y-m-d", $unix_date);
                      //$fecha = gmdate("d-m-Y", $unix_date);
                      //$fecha = gmdate("d/m/Y", $unix_date);
                      //$fecha = gmdate("Y/m/d", $unix_date);
                      //$model->Fec_Entrada      = $fecha;
                      $model->Fec_Entrada      = $this->pSQL($row[self::FECHA_ENTRADA]);

                        $excel_date = $this->pSQL($row[self::FECHA_ENTREGA]);
                        $unix_date = ($excel_date - 25569) * 86400;
                        $excel_date = 25569 + ($unix_date / 86400);
                        $unix_date = ($excel_date - 25569) * 86400;
                        $fecha = gmdate("Y-m-d", $unix_date);
                        //$fecha = gmdate("d-m-Y", $unix_date);
                        //$fecha = gmdate("d/m/Y", $unix_date);
                        //$fecha = gmdate("Y/m/d", $unix_date);
                        //$model->Fec_Entrega      = $fecha;
                        $model->Fec_Entrega      = $this->pSQL($row[self::FECHA_ENTREGA]);

                        $model->ID_Tipoprioridad = $this->pSQL($row[self::PRIORIDAD]);
                        $model->cve_almac        = $almacen_val;
                        $model->Observaciones    = $this->pSQL($row[self::OBSERVACIONES]);
                        $model->Pick_Num         = $this->pSQL($row[self::NUM_ORDEN_CLIENTE]);//$this->pSQL($row[self::PICK_NUMBER]);
                        $model->destinatario     = $this->pSQL($row[self::CVE_DESTINATARIO]);
                        $model->status           = 'A';//$this->pSQL($row[12]);
                        $model->Cve_CteProv      = $this->pSQL($row[self::CLAVE_CLIENTE_PROV]);
                        //$registrar = 0;

                        $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $num_articulo = $resul['num_articulo'];

                        $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $cliente = $resul['cliente'];

                        $tracking_codigo .= "[$linea - 2->num_articulo = $num_articulo, cliente = $cliente]";

                        if($num_articulo && $cliente)
                        {//s27
                            $mensaje_dev .= "Folio = ".$this->pSQL($row[self::FOLIO])."\n\n";
                            $folio_dest = $this->pSQL($row[self::FOLIO]);
                            $destinatario = $this->pSQL($row[self::CVE_DESTINATARIO]);

                            //if($cve_articulo_registrado != $cve_articulo)
                            $sql = "SELECT control_lotes, control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $control_lotes         = $resul['control_lotes'];
                            $control_numero_series = $resul['control_numero_series'];

                            $existe_lote = 0; $existe_serie = 0; $registrar_lote_serie = true; $lote =""; $serie = "";
                            if($this->pSQL($row[self::LOTE_SERIE]) != '')
                            {
                                if($control_lotes == 'S')
                                {
                                    $lote = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '$cve_articulo' AND Lote = '$lote'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_lote = $resul['existe'];
                                }

                                if($control_numero_series == 'S')
                                {
                                    $serie = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '$cve_articulo' AND numero_serie = '$serie'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_serie = $resul['existe'];
                                }

                                if(($existe_lote == 0 && $control_lotes == 'S') || ($existe_serie == 0 && $control_numero_series == 'S'))
                                {
                                    $registrar_lote_serie = false;
                                }
                            }

                            if($registrar_lote_serie == true && ($control_lotes == 'S' || $control_numero_series == 'S'))
                            {
                                $respuesta = $model->save();
                                $cve_articulo_registrado = $cve_articulo;

                                $sql = "INSERT INTO Rel_PedidoDest(Fol_Folio, Cve_Almac, Id_Destinatario) VALUES (
                                             '".$folio_dest."', 
                                             '".$almacen_val_clave."', 
                                             ".$destinatario."
                                        );";
                                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
                                $tracking_codigo .= "[$linea -3]";

                                //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
                                //$consecutivo_folio++;
                                if($orden_de_compra != $this->pSQL($row[self::FOLIO]))
                                {//s24
                                    //$num_pedidos++;
                                    $orden_de_compra = $this->pSQL($row[self::FOLIO]);
                                    $secuencia = 0;
                                }//s24
                            }
                            //else
                            //  $lotes_series_no_existentes .= $lote.$serie."\n";
                            //else
                            //    $num_pedidos++;
                        }//s27
                        else
                        {//s28
                            if(!$num_articulo && $cve_articulo)
                            {//s29
                                $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                                if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                                $registro_anterior = $registro_actual;
                            }//s29
                            $tracking_codigo .= "[$linea -3X]";

                            if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                            {//s30
                                $registro_actual_cliente = $cve_clte."\n";
                                if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                                $registro_anterior_cliente = $registro_actual_cliente;
                                array_push($clientes_registrados, $cve_clte);
                            }//s30
                        }//s28

                        $registrar = 0;
                        $folio_anterior = $this->pSQL($row[self::FOLIO]);
                  }//s38

                  //$items = PedidosItems::where('Fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->first();

                  //if($items != NULL){//s39
                  //    $model = $items; 
                  //}//s39
                  //else {//s40
                      $model = new PedidosItems(); 
                  //}//s40

                    $cve_articulo  = $this->pSQL($row[self::CVE_ARTICULO]);
                    $cve_lote      = $this->pSQL($row[self::LOTE_SERIE]);
                    $clave_cliente = $this->pSQL($row[self::CLAVE_CLIENTE]);

                    $model->Fol_folio        = $this->pSQL($row[self::FOLIO]);
                    $model->Cve_articulo     = $this->pSQL($row[self::CVE_ARTICULO]);
                    $model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
                    $model->Num_Meses        = $this->pSQL($row[self::MESES]);
                    $model->status           = 'A';//$this->pSQL($row[12]);

                    $Folio = $this->pSQL($row[self::FOLIO]);

                    $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $num_articulo = $resul['num_articulo'];

                    $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['cliente'];

                    $sql = "SELECT  COUNT(*) AS pedido FROM td_pedido WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $pedido_articulo = $resul['pedido'];

                    $tracking_codigo .= "[$linea -4 --> num_articulo = $num_articulo, cliente = $cliente, Cve_articulo = $cve_articulo, pedido_articulo = $pedido_articulo]";

                    if($num_articulo && $cliente)
                    {//s44
                        if($pedido_articulo)
                        {
                            $cantidad_agr = $this->pSQL($row[self::NUM_CANTIDAD]);
                            if($cantidad_agr)
                            {
                              $sql = "UPDATE td_pedido SET Num_cantidad = (Num_cantidad + $cantidad_agr) WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote'";
                              // AND '$clave_cliente' IN (SELECT Cve_clte FROM th_pedido WHERE Fol_folio='$Folio')
                              $rs = mysqli_query($conn, $sql);
                            }
                            $tracking_codigo .= "[$linea -5X, Cve_articulo = $cve_articulo]";
                        }
                        else //if($cve_articulo_registrado != $cve_articulo)
                        {
                            //$model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);

                            $sql = "SELECT control_lotes, control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $control_lotes         = $resul['control_lotes'];
                            $control_numero_series = $resul['control_numero_series'];

                            $existe_lote = 0; $existe_serie = 0; $registrar_lote_serie = true;
                            if($this->pSQL($row[self::LOTE_SERIE]) != '')
                            {
                                if($control_lotes == 'S')
                                {
                                    $lote = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '$cve_articulo' AND Lote = '$lote'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_lote = $resul['existe'];
                                    $model->cve_lote         = $cve_lote;
                                }

                                if($control_numero_series == 'S')
                                {
                                    $serie = $this->pSQL($row[self::LOTE_SERIE]);
                                    $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '$cve_articulo' AND numero_serie = '$serie'";
                                    $rs = mysqli_query($conn, $sql);
                                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                                    $existe_serie = $resul['existe'];
                                    $model->cve_lote         = $cve_lote;
                                }

                                if(($existe_lote == 0 && $control_lotes == 'S') || ($existe_serie == 0 && $control_numero_series == 'S'))
                                {
                                    $registrar_lote_serie = false;
                                }
                            }

                            if($registrar_lote_serie == true && ($existe_lote > 0 && $control_lotes == 'S') || ($existe_serie > 0 && $control_numero_series == 'S'))
                            {
                              $respuesta = $model->save();
                            }
                            else
                              $lotes_series_no_existentes .= $cve_lote."\n";

                            $tracking_codigo .= "[$linea -5Y, Cve_articulo = $cve_articulo]";
                        }

                        $cve_articulo_registrado = $cve_articulo;

                        $tracking_codigo .= "[$linea -5]";

                        if($orden_de_compra != $this->pSQL($row[self::FOLIO]))
                        {//s45
                            $num_pedidos++;
                            $orden_de_compra = $this->pSQL($row[self::FOLIO]);
                            $secuencia = 0;
                        }//s45
                        else
                            $num_pedidos++;
                    }//s44
                    else
                    {//s46
                        if(!$num_articulo && $cve_articulo)
                        {//s47
                            $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                            if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                            $registro_anterior = $registro_actual;
                        }//s47

                        if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                        {//s48
                            $registro_actual_cliente = $cve_clte."\n";
                            if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                            $registro_anterior_cliente = $registro_actual_cliente;
                            array_push($clientes_registrados, $cve_clte);
                        }//s48
                        $tracking_codigo .= "[$linea -5X]";
                    }//s46


                    $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $num_articulo = $resul['num_articulo'];

                    $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['cliente'];

                    $tracking_codigo .= "[$linea -6 --> num_articulo = $num_articulo, cliente = $cliente, Cve_articulo = $cve_articulo]";

                    if($num_articulo && $cliente)
                    {//s50
                        $Sku_R = "200".$Sku;
                        //***************************************************************
                        //       Función para cálculo de dígito verificador UPCA
                        //***************************************************************
                            $impares = 0;
                            $pares = 0;
                            for($i = 0; $i < strlen($Sku_R); $i++)
                              if($i%2==0)
                                $impares += $Sku_R[$i];
                              else
                                $pares += $Sku_R[$i];

                            $imparesx3 = $impares*3;
                            $imparesx3maspares = $imparesx3+$pares;
                            $imparesx3maspares_save = $imparesx3maspares;
                            while($imparesx3maspares%10!=0)
                              $imparesx3maspares++;
                            $codigo = $imparesx3maspares - $imparesx3maspares_save;

                            $Sku_R .= $codigo;
                        //***************************************************************
                        //                        By RM/AO
                        //***************************************************************

                        $sql = "INSERT INTO c_articulo_codigo (Cve_Almacen, Cve_Articulo, Cve_Clte, Codigo, Sku_R, Descripcion) Values ";
                        $sql .= "('".$cve_almac."', '".$cve_articulo."', '".$cve_clte."', '".$Sku."', '".$Sku_R."', 'A')";
                        $sql .= " ON DUPLICATE KEY UPDATE Codigo='$Sku', Sku_R='$Sku_R', Descripcion='A'";
                        $rs = mysqli_query($conn, $sql);

                        $tracking_codigo .= "[$linea -7]";

                    }//s50
              }//s60
              $linea++;
        }

      $statusText = "El Formato del Layout está Incorrecto.";
      if($ok_pedido)
      {
        $mensaje_no_existentes = ""; $mensaje_clientes_no_existentes = "";
        if($articulos_no_existentes && $num_pedidos)
           $mensaje_no_existentes = "\n\n\nSin embargo, Los siguientes productos no se encuentran en el Catálogo: \n\n\n ". $articulos_no_existentes;

        $mensaje_lotes_series_no_existentes = "";
        if($lotes_series_no_existentes != "")
           $mensaje_lotes_series_no_existentes = "\n\n\nLos siguientes Lotes no existen en el sistema: \n\n\n ".$lotes_series_no_existentes;

         if($clientes_no_existentes != "")
            $mensaje_clientes_no_existentes = "\n\n\nLos siguientes clientes no existen en el sistema: \n\n $clientes_no_existentes";

         $statusText = "Pedidos importados con exito. Total de Pedidos: \"{$num_pedidos}\" $mensaje_no_existentes".$mensaje_clientes_no_existentes.$mensaje_lotes_series_no_existentes;

         if($folios_repetidos) $mensaje_folio_exist = "\n\nFolios Repetidos: \n\n".$folios_repetidos;

         if($num_pedidos == 0 && $mensaje_clientes_no_existentes == "" && $mensaje_folio_exist == "")
            $statusText = "No se ha ingresado ningún pedido".$folios_no_validos;
          else if($mensaje_folio_exist != "" || $folios_no_validos != "")
            $statusText .= $mensaje_folio_exist.$folios_no_validos;
      }

      
        $this->response(200, [

            'statusText' =>  $statusText,
            'pedidos' => $num_pedidos,
            'mensaje_dev' => $mensaje_dev,
            'tracking_codigo' => $tracking_codigo,
            "lineas"=>$lineas
        ]);

    }
*/

    public function importar()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $tipo = $_POST["tipo"];
        $almacen_val = $_POST["almacenes"];

        if($tipo == "th")
        {
            ImportarTH();
            return;
        }

        

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $columnas = array("Folio","Cliente","FechaPedido","FechaEntrega","Prioridad","ClaveAlmacen","Comentarios","NoOrdenCliente","Destinatario","Articulo","Piezas","Meses","Status","itemPos","Cliente Proveedor");
        if($tipo=="vp"){$columnas[] = "BL";}
        $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
      

        $sql = "SELECT clave FROM c_almacenp WHERE id = $almacen_val";
        $rs = mysqli_query($conn, $sql);
        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $almacen_val_clave = $resul['clave'];

        $num_pedidos = 0;
        $secuencia = 0;
        $Fol_folio = "";
        $linea = 1;
        $lineas = $xlsx->rows();
        $articulos_no_existentes = "";
        $clientes_no_existentes = "";
        $registro_anterior = "";
        $registro_anterior_cliente = "";
        $orden_de_compra = 0;
        $consecutivo_folio = 0;
        $destinatario = "";
        $folio_cont = "";
        $folio_anterior = "";
        $clave_cliente = "";
        $folio_consolidado = $this->loadNumeroOla();
        $folio_consolidado_registro = true;
        $registrar = 1;
        $registrar_pedido_th = false;
        $registrar_por_cliente = true;
        $folios_registrados = array();
        $tiendas_registradas = array();
        $clientes_registrados = array();
        $mensaje_dev = "";
        $tracking_codigo = "";
        $cve_articulo_registrado = "";
        $ya_existe_folio = "";
        $mensaje_folio_exist = "";
        $folios_exist = array();
        $ok_pedido = true;
        $sql_exist = "";
        $row_hack = "";
        foreach ($xlsx->rows() as $row)
        {//s53
          $folio_rev = $this->pSQL($row[self::FOLIO]);

          $sql = "SELECT COUNT(*) as folio FROM th_pedido WHERE Fol_folio = '$folio_rev'";
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $folio_res = $resul['folio'];

          $ya_existe_folio = ""; //$ok_pedido = true;

          if($folio_res > 0) 
          {
              if(!in_array($folio_rev, $folios_exist)) 
              {
                array_push($folios_exist, $folio_rev);
              }
/*
              else
              {
                $ya_existe_folio = "El Folio (".$folio_rev.") ya existe\n\n"; 
                $mensaje_folio_exist .= $ya_existe_folio;
                $ok_pedido = false;
              }
*/
          }

          if($linea == 2 && $tipo == "th") $row_hack = $row;
          if(!in_array($this->pSQL($row[8]), $tiendas_registradas) && ($tipo == "ph" /*|| $tipo == "lv" || $tipo == "hs"*/))
          {//s1
             $folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
             $registrar = 1;
          }//s1
          else if($tipo != "th")
          {//s2
              $posFolio  = array_search($this->pSQL($row[8]), $tiendas_registradas);
              $folio_cont = $folios_registrados[$posFolio];
              $registrar = 0;
          }//s2
         //$consecutivo_folio++;
          if($tipo!="ph" && $tipo!="lv" && $tipo!="hs" && $tipo!="th") //me evito la validación por los momentos
          {//s3
                if($linea == 1) {//s4
                    foreach($columnas as $k => $v)
                    {//s7
                        if($this->pSQL($row[$k])!=$v)
                        {//s5
                            $k_i=$k+1;
                            $this->response(400, ['statusText' =>  "ERROR Falta el titulo “".$v."” en la columna “".$cl_excel[$k]."” de su archivo excel",]); 
                        }//s5
                    }//s7
                    $linea++;
                    continue;
                }//s4
                else
                {//s10
                    foreach($columnas as $k => $v)
                    {//s8
                        if($this->pSQL($row[$k])=="")
                        {//s9
                            // Datos incorrecto en la fila “Numero de fila” de la columna “Letra de columna”
                            $k_i=$k+1;
                            $this->response(400, ['statusText' =>  "ERROR Dato incorrecto en la fila “".$linea."” columna “".$cl_excel[$k]."”. Este dato es necesario.",]); 
                        }//s9
                    }//s8
                }//s10
          }//s3
          else if($linea > 1 && $ok_pedido)
          {//s52
            //$cve_almac = "";
            $cve_almac = $almacen_val;
            $cve_clte = "";
            $cve_dest = "";
            $cve_articulo = "";
            $Sku = "";

            if($tipo!="ph" && $tipo!="lv" && $tipo!="hs" && $tipo!="th")
            {//s11
                //if($Fol_folio != $this->pSQL($row[0]))
                if($orden_de_compra != $this->pSQL($row[0]))
                {//s12
                    $num_pedidos++;
                    $orden_de_compra = $this->pSQL($row[0]);
                    $secuencia = 0;
                }//s12
            }//s11

            
            if($tipo=="th") 
            {
              $registrar = 0;
              if($folio_anterior != $this->pSQL($row[self::FOLIO]))
                   $registrar = 1;
            }

          if($registrar == 1)
          {//s38
            //$element = Pedidos::where('Fol_folio', $Fol_folio)->where('cve_almac', $cve_almac)->first();
            //if($element != NULL){//s13
            //    $model = $element; 
            //}//s13
            //else {//s14
                $model = new Pedidos(); 
            //}//s14

            if($tipo=="ph") 
            {//s15
                $Fol_folio = $folio_cont;
                //$consecutivo_folio++;
                $model->cve_almac = $almacen_val;
                //$model->Cve_articulo = $this->pSQL($row[15]);
            }//s15
            else if($tipo=="lv") 
            {//s16
                $Fol_folio = $folio_cont;
                //$consecutivo_folio++;
                $model->cve_almac = $almacen_val;
                //$model->Cve_articulo = $this->pSQL($row[15]);
            }//s16
            else 
            {//s17
              $Fol_folio = $this->pSQL($row[self::FOLIO]);
              $Cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
              $cve_almac = $almacen_val;
              //$registrar = 0;
            }//s17

            if($tipo=="ph") 
            {//s18
                $model->Fol_folio        = $folio_cont;
                $clave_cliente = $this->pSQL($row[7]);
                $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $cliente = $resul['cliente'];

                if($cliente)
                {//s19
                    $sql = "SELECT Cve_Clte FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['Cve_Clte'];
                    //$consecutivo_folio++;
                    $model->Cve_clte         = $cliente;//$row[7]

                    $excel_date = $row[2];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fec_Pedido = gmdate("Y-m-d", $unix_date);
                    $model->Fec_Pedido       = $Fec_Pedido;//$this->pSQL($row[2]);


                    $excel_date = $row[1];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fec_Entrada = gmdate("Y-m-d", $unix_date);
                    $model->Fec_Entrada      = $Fec_Entrada;//$this->pSQL($row[1]);


                    $model->ID_Tipoprioridad = 1;
                    $model->cve_almac        = $almacen_val;
                    $model->Observaciones    = 0;
                    $model->destinatario     = $this->pSQL($row[8]);
                    $model->Pick_Num         = $this->pSQL($row[0]);
                    $model->status           = 'A';
                    $model->Cve_CteProv      = $this->pSQL($row[7]);
                    //$model->Fec_Pedido       = $this->pSQL($row[2]);
                    //$model->Fec_Entrega      = $this->pSQL($row[1]);

                    $cve_almac = $almacen_val;
                    $cve_articulo = $this->pSQL($row[15]);
                    $cve_clte = $this->pSQL($row[7]);//$row[7]
                    //$cve_dest = $this->pSQL($row[7]);//$row[8]
                    $Sku = $this->pSQL($row[11]);

                    array_push($tiendas_registradas, $this->pSQL($row[8]));
                    array_push($folios_registrados, $folio_cont);
                }//s19
            }//s18
            else if($tipo=="lv") 
            {//s20
                $model->Fol_folio        = $folio_cont;

                $clave_cliente = $this->pSQL($row[7]);
                $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $cliente = $resul['cliente'];

                if($cliente)
                {//s21
                    $sql = "SELECT Cve_Clte FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cliente = $resul['Cve_Clte'];
                    //$consecutivo_folio++;
                    $model->Cve_clte         = $cliente;//$row[7]

                    $excel_date = $row[2];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fec_Pedido = gmdate("Y-m-d", $unix_date);
                    $model->Fec_Pedido       = $Fec_Pedido;//$this->pSQL($row[2]);


                    $excel_date = $row[1];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fec_Entrada = gmdate("Y-m-d", $unix_date);
                    $model->Fec_Entrada      = $Fec_Entrada;//$this->pSQL($row[1]);


                    $model->ID_Tipoprioridad = 1;
                    $model->cve_almac        = $almacen_val;
                    $model->Observaciones    = 0;
                    $model->destinatario     = $this->pSQL($row[8]);
                    $model->Pick_Num         = $this->pSQL($row[0]);
                    $model->status           = 'A';
                    $model->Cve_CteProv      = $this->pSQL($row[7]);
                    //$model->Fec_Pedido       = $this->pSQL($row[2]);
                    //$model->Fec_Entrega      = $this->pSQL($row[1]);

                    $cve_almac = $almacen_val;
                    $cve_articulo = $this->pSQL($row[15]);

                    $cve_clte = $this->pSQL($row[7]);//$row[7]
                    //$cve_dest = $this->pSQL($row[7]);//$row[8]
                    $Sku = $this->pSQL($row[11]);

                    array_push($tiendas_registradas, $this->pSQL($row[8]));
                    array_push($folios_registrados, $folio_cont);
                }//s21
            }//s20
            else 
            {//s22
                $clave_cliente = $this->pSQL($row[self::CLAVE_CLIENTE]);
                $sql = "SELECT  Cve_Clte as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $cliente = $resul['cliente'];

                $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                $model->Fol_folio        = $this->pSQL($row[self::FOLIO]);
                $model->Cve_clte         = $cliente;

                //if($this->pSQL($row[self::FECHA_PEDIDO]))
                //{
                    $excel_date = $this->pSQL($row[self::FECHA_PEDIDO]);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $fecha = gmdate("Y-m-d", $unix_date);
                    //$fecha = gmdate("d-m-Y", $unix_date);
                    //$fecha = gmdate("d/m/Y", $unix_date);
                    //$fecha = gmdate("Y/m/d", $unix_date);
                    //$model->Fec_Pedido       = $fecha;
                    $model->Fec_Pedido       = $this->pSQL($row[self::FECHA_PEDIDO]);
                //}

                //if($this->pSQL($row[self::FECHA_ENTRADA]))
                //{
                    $excel_date = $this->pSQL($row[self::FECHA_ENTRADA]);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $fecha = gmdate("Y-m-d", $unix_date);
                    //$fecha = gmdate("d-m-Y", $unix_date);
                    //$fecha = gmdate("d/m/Y", $unix_date);
                    //$fecha = gmdate("Y/m/d", $unix_date);
                    //$model->Fec_Entrada      = $fecha;
                    $model->Fec_Entrada      = $this->pSQL($row[self::FECHA_ENTRADA]);
                //}

                //if($this->pSQL($row[self::FECHA_ENTREGA]))
                //{
                    $excel_date = $this->pSQL($row[self::FECHA_ENTREGA]);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $fecha = gmdate("Y-m-d", $unix_date);
                    //$fecha = gmdate("d-m-Y", $unix_date);
                    //$fecha = gmdate("d/m/Y", $unix_date);
                    //$fecha = gmdate("Y/m/d", $unix_date);
                    //$model->Fec_Entrega      = $fecha;
                    $model->Fec_Entrega      = $this->pSQL($row[self::FECHA_ENTREGA]);
                //}

                $model->ID_Tipoprioridad = $this->pSQL($row[self::PRIORIDAD]);
                $model->cve_almac        = $almacen_val;
                $model->Observaciones    = $this->pSQL($row[self::OBSERVACIONES]);
                $model->Pick_Num         = $this->pSQL($row[self::NUM_ORDEN_CLIENTE]);//$this->pSQL($row[self::PICK_NUMBER]);
                $model->destinatario     = $this->pSQL($row[self::CVE_DESTINATARIO]);
                $model->status           = 'A';//$this->pSQL($row[12]);
                $model->Cve_CteProv      = $this->pSQL($row[self::CLAVE_CLIENTE_PROV]);
                //$registrar = 0;
            }//s22

            $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $num_articulo = $resul['num_articulo'];

            $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $cliente = $resul['cliente'];


            if($num_articulo && $cliente)
            {//s27
                $mensaje_dev .= "Folio = ".$this->pSQL($row[self::FOLIO])."\n\n";
                $folio_dest = $this->pSQL($row[self::FOLIO]);
                $destinatario = $this->pSQL($row[self::CVE_DESTINATARIO]);

                //if($cve_articulo_registrado != $cve_articulo)
                $respuesta = $model->save();
                $cve_articulo_registrado = $cve_articulo;

                $sql = "INSERT INTO Rel_PedidoDest(Fol_Folio, Cve_Almac, Id_Destinatario) VALUES (
                             '".$folio_dest."', 
                             '".$almacen_val_clave."', 
                             ".$destinatario."
                        );";
                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

                if($tipo == "ph")
                {//s23
                    $sql = "UPDATE th_pedido SET status = 'O' WHERE Fol_folio = '$folio_cont'";
                    $rs = mysqli_query($conn, $sql);
                }//s23

                //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
                //$consecutivo_folio++;
                if($orden_de_compra != $this->pSQL($row[self::FOLIO]))
                {//s24
                    //$num_pedidos++;
                    $orden_de_compra = $this->pSQL($row[self::FOLIO]);
                    $secuencia = 0;
                }//s24
                //else
                //    $num_pedidos++;

                if($tipo == "ph" && $folio_consolidado_registro)
                {//s25
                    $fecha_entrega = $this->pSQL($row[2]);
                    $total_cajas   = 1;
                    $total_piezas  = 1;

                    //$folio_consolidado = $this->loadNumeroOla();
                    //***************************************************************************************
                    //Aqui no importa que sean iguales porque son consolidados y van al cve_clte en realidad
                    $cve_clte = $this->pSQL($row[7]);
                    $cve_dest = $this->pSQL($row[7]);
                    //***************************************************************************************

                    $sql = "SELECT RazonSocial FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $nombre_cliente = $resul['RazonSocial'];

                    $model_header = new ConsolidadosOla();

                    $model_header->Fec_Entrega      = $this->pSQL($row[1]);

                    $model_header->Tot_Cajas        = 1;
                    $model_header->Tot_Pzs          = 1;
                    $model_header->Cve_CteCon       = $cve_clte;
                    $model_header->Nom_CteCon       = $nombre_cliente;
                    //$model->Placa_Trans  = $this->pSQL($placa_trans);
                    //$model->Sellos       = $this->pSQL($sellos);
                    $model_header->Fol_PedidoCon    = $this->pSQL($folio_consolidado);
                    $model_header->No_OrdComp       = $this->pSQL($row[0]);
                    $model_header->Status           = $this->pSQL('P');
                    $model_header->save();

                    $folio_consolidado_registro = false;

                    $model_consolidado = new Pedidos();

                    $model_consolidado->Fol_folio        = $folio_consolidado;


                    $excel_date = $this->pSQL($row[1]);//$row[1];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fecha_convert = gmdate("Y-m-d", $unix_date);
                    $model_consolidado->Fec_Pedido       = $Fecha_convert; //$this->pSQL($row[1]);

                    $model_consolidado->Cve_Clte         = $cve_clte;
                    $model_consolidado->status           = 'A';
                    $model_consolidado->ID_Tipoprioridad = 1;


                    $excel_date = $this->pSQL($row[2]);//$row[2];
                    $unix_date = ($excel_date - 25569) * 86400;
                    $excel_date = 25569 + ($unix_date / 86400);
                    $unix_date = ($excel_date - 25569) * 86400;
                    $Fecha_convert = gmdate("Y-m-d", $unix_date);
                    $model_consolidado->Fec_Entrada      = $Fecha_convert; //$this->pSQL($row[2]);

                    $model_consolidado->cve_almac        = $almacen_val;
                    $model_consolidado->destinatario     = $cve_dest;
                    $model_consolidado->Pick_Num         = $this->pSQL($row[0]);
                    $model_consolidado->Cve_CteProv      = $this->pSQL($row[7]);
                    $model_consolidado->Activo           = 1;

                    $model_consolidado->save();

                }//s25
                if($tipo != "th")
                {//s26
                    $sql = "UPDATE th_pedido SET status = 'O' WHERE Fol_folio = '$folio_cont'";
                    $rs = mysqli_query($conn, $sql);
                }//s26
            }//s27
            else
            {//s28
                if(!$num_articulo && $cve_articulo)
                {//s29
                    $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                    if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                    $registro_anterior = $registro_actual;
                }//s29

                if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                {//s30
                    $registro_actual_cliente = $cve_clte."\n";
                    if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                    $registro_anterior_cliente = $registro_actual_cliente;
                    array_push($clientes_registrados, $cve_clte);
                }//s30
            }//s28
            
            //$folio_cont++;
            //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio();
          
            if($tipo=="vp")
            {//s31
              $element = SubPedidos::where('fol_folio', $Fol_folio)->where('cve_almac', $cve_almac)->first();
              if($element != NULL){//s36
                  $model = $element; 
              }//s36
              else {//s35
                  $model = new SubPedidos();
              }//s35
              
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $almacen_val;
              $model->Sufijo           = 1;
              $model->Fec_Entrada      = $this->pSQL($row[2]);
              $model->status           = $this->pSQL($row[12]);

              $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $num_articulo = $resul['num_articulo'];

              if($num_articulo)
              {//s33
                  $respuesta = $model->save();
                  if($orden_de_compra != $this->pSQL($row[0]))
                  {//s34
                      $num_pedidos++;
                      $orden_de_compra = $this->pSQL($row[0]);
                      $secuencia = 0;
                  }//s34
                  else
                      $num_pedidos++;
              }//s33
              else if($cve_articulo)
              {//s32
                  $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                  if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                  $registro_anterior = $registro_actual;
              }//s32
                //$folio_cont++;
                //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio();
            }//s31
            $registrar = 0;
            if($tipo=="th") 
            $folio_anterior = $this->pSQL($row[self::FOLIO]);
          }//s38
            $items = PedidosItems::where('Fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->first();

            if($items != NULL){//s39
                $model = $items; 
            }//s39
            else {//s40
                $model = new PedidosItems(); 
            }//s40

/*
                $model->Fol_folio        = $folio_cont;
                $model->Cve_clte         = $this->pSQL($row[7]);
                $model->Fec_Pedido       = $this->pSQL($row[1]);
                $model->Fec_Entrega      = $this->pSQL($row[2]);
                $model->ID_Tipoprioridad = 1;
                $model->cve_almac        = $almacen_val;
                $model->Observaciones    = 0;
                $model->destinatario     = $this->pSQL($row[8]);
                $model->Pick_Num         = $this->pSQL($row[0]);
                $model->Cve_articulo     = $this->pSQL($row[11]);
                $model->Num_cantidad     = $this->pSQL($row[9]);
                $model->Num_Meses        = 0;
                $model->status           = 'A';
                $model->itemPos          = 1;
                $model->Cve_CteProv      = 100;
*/
            if($tipo=="ph") 
            {//s41
                $model->Fol_folio        = $folio_cont;
                //$consecutivo_folio++;

                $model->Cve_articulo     = $this->pSQL($row[15]);
                $model->Num_cantidad     = $this->pSQL($row[9]);
                $model->Num_Meses        = 0;
                $model->status           = 'A';
                $model->itemPos          = 1;
                //$model->Fec_Pedido       = $this->pSQL($row[2]);
                //$model->Fec_Entrada      = $this->pSQL($row[1]);

                $cve_almac = $almacen_val;
                $cve_articulo = $this->pSQL($row[15]);
                $cve_clte = $this->pSQL($row[7]);//$row[7]
                //$cve_dest = $this->pSQL($row[7]);//$row[8]
                $Sku = $this->pSQL($row[11]);
            }//s41
            else if($tipo=="lv") 
            {//s42
                $model->Fol_folio        = $folio_cont;
                //$consecutivo_folio++;

                $model->Cve_articulo     = $this->pSQL($row[15]);
                $model->Num_cantidad     = $this->pSQL($row[9]);
                $model->Num_Meses        = 0;
                $model->status           = 'A';
                $model->itemPos          = 1;
                //$model->Fec_Pedido       = $this->pSQL($row[2]);
                //$model->Fec_Entrada      = $this->pSQL($row[1]);

                $cve_almac = $almacen_val;
                $cve_articulo = $this->pSQL($row[15]);
                $cve_clte = $this->pSQL($row[7]);//$row[7]
                //$cve_dest = $this->pSQL($row[7]);//$row[8]
                $Sku = $this->pSQL($row[11]);
            }//s42
            else 
            {//s43
                $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
                $clave_cliente = $this->pSQL($row[self::CLAVE_CLIENTE]);

                $model->Fol_folio        = $this->pSQL($row[self::FOLIO]);
                $model->Cve_articulo     = $this->pSQL($row[self::CVE_ARTICULO]);
                $model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
                $model->Num_Meses        = $this->pSQL($row[self::MESES]);
                $model->status           = 'A';//$this->pSQL($row[12]);
            }//s43

            $Folio = $this->pSQL($row[self::FOLIO]);

            $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $num_articulo = $resul['num_articulo'];

            $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $cliente = $resul['cliente'];

            $sql = "SELECT  COUNT(*) AS pedido FROM td_pedido WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $pedido_articulo = $resul['pedido'];

            if($num_articulo && $cliente)
            {//s44

                if($pedido_articulo)
                {
                    $cantidad_agr = $this->pSQL($row[self::NUM_CANTIDAD]);
                    if($cantidad_agr)
                    {
                      $sql = "UPDATE td_pedido SET Num_cantidad = (Num_cantidad + $cantidad_agr) WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo'";
                      // AND '$clave_cliente' IN (SELECT Cve_clte FROM th_pedido WHERE Fol_folio='$Folio')
                      $rs = mysqli_query($conn, $sql);
                    }
                }
                else //if($cve_articulo_registrado != $cve_articulo)
                {
                    //$model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
                    $respuesta = $model->save();
                }

                $cve_articulo_registrado = $cve_articulo;

        //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
        //$consecutivo_folio++;
                if($orden_de_compra != $this->pSQL($row[self::FOLIO]))
                {//s45
                    $num_pedidos++;
                    $orden_de_compra = $this->pSQL($row[self::FOLIO]);
                    $secuencia = 0;
                }//s45
                else
                    $num_pedidos++;
            }//s44
            else
            {//s46
                if(!$num_articulo && $cve_articulo)
                {//s47
                    $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                    if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                    $registro_anterior = $registro_actual;
                }//s47

                if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                {//s48
                    $registro_actual_cliente = $cve_clte."\n";
                    if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                    $registro_anterior_cliente = $registro_actual_cliente;
                    array_push($clientes_registrados, $cve_clte);
                }//s48
            }//s46
            //$folio_cont++;
            //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio();
          
          
            if($tipo=="vp")
            {//s49
              $element = SubPedidosItems::where('fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->delete();
              $model = new SubPedidosItems(); 
              
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $almacen_val;
              $model->Sufijo           = 1;
              $model->Cve_articulo     = $this->pSQL($row[9]);
              $model->Num_cantidad     = $this->pSQL($row[10]);
              $model->Nun_surtida      = 0;
              $model->status           = $this->pSQL($row[12]);
              
              $respuesta = $model->save();
              //$folio_cont++;
              
              $ubicacion = Ubicaciones::where('CodigoCSD', $this->pSQL($row[15]))->first();
              
              $element = RecorridoSurtido::where('fol_folio', $Fol_folio)->where('Cve_articulo', $Cve_articulo)->delete();
              
              $secuencia++;
              
              $model = new RecorridoSurtido();
              
              $model->idy_ubica        = $ubicacion->idy_ubica;
              $model->orden_secuencia  = $secuencia;
              $model->fol_folio        = $this->pSQL($row[0]);
              $model->cve_almac        = $almacen_val;
              $model->Sufijo           = 1;
              $model->Cve_articulo     = $this->pSQL($row[9]);
              $model->Cantidad         = $this->pSQL($row[10]);
              $model->Picking          = "S";
              $model->Activo           = 1;
              
              $respuesta = $model->save();
              //$folio_cont++;
              //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio();
            }//s49

            $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $num_articulo = $resul['num_articulo'];

            $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $cliente = $resul['cliente'];

            if($num_articulo && $cliente)
            {//s50
                $Sku_R = "200".$Sku;
                //***************************************************************
                //       Función para cálculo de dígito verificador UPCA
                //***************************************************************
                    $impares = 0;
                    $pares = 0;
                    for($i = 0; $i < strlen($Sku_R); $i++)
                      if($i%2==0)
                        $impares += $Sku_R[$i];
                      else
                        $pares += $Sku_R[$i];

                    $imparesx3 = $impares*3;
                    $imparesx3maspares = $imparesx3+$pares;
                    $imparesx3maspares_save = $imparesx3maspares;
                    while($imparesx3maspares%10!=0)
                      $imparesx3maspares++;
                    $codigo = $imparesx3maspares - $imparesx3maspares_save;

                    $Sku_R .= $codigo;
                //***************************************************************
                //                        By RM/AO
                //***************************************************************

                $sql = "INSERT INTO c_articulo_codigo (Cve_Almacen, Cve_Articulo, Cve_Clte, Codigo, Sku_R, Descripcion) Values ";
                $sql .= "('".$cve_almac."', '".$cve_articulo."', '".$cve_clte."', '".$Sku."', '".$Sku_R."', 'A')";
                $sql .= " ON DUPLICATE KEY UPDATE Codigo='$Sku', Sku_R='$Sku_R', Descripcion='A'";
                $rs = mysqli_query($conn, $sql);


                if($tipo=="ph")
                {//s51
                    $model_items = new ConsolidadosOlaItems();
                    $model_items->Fol_PedidoCon = $folio_consolidado;
                    $model_items->No_OrdComp    = $this->pSQL($row[0]);
                    $model_items->Fec_OrdCom    = $this->pSQL($row[1]);
                    $model_items->Cve_Articulo  = $this->pSQL($row[15]);
                    $model_items->Cant_Pedida   = $this->pSQL($row[9]);
                    $model_items->Unid_Empaque  = 'BO';
                    $model_items->Tot_Cajas     = 1;
                    //$model_items->Fact_Madre    = '';
                    $model_items->Cve_Clte      = '';
                    $model_items->Cve_CteProv   = '';
                    $model_items->Fol_Folio     = $folio_cont;
                    $model_items->CodB_Cte      = '';
                    $model_items->Cod_PV        = '';
                    $model_items->Status        = 'A';

                    $model_items->save();

/*
                    $sql = "SELECT SUM(Num_cantidad) cantidad_consolidada FROM td_pedido WHERE Cve_articulo = '$cve_articulo'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cantidad_consolidada = $resul['cantidad_consolidada'];
*/
                    $model_consolidado = new PedidosItems();

                    $model_consolidado->Fol_folio        = $folio_consolidado;
                    $model_consolidado->Cve_articulo     = $cve_articulo;
                    $model_consolidado->Num_cantidad     = $this->pSQL($row[9]);//$cantidad_consolidada;
                    $model_consolidado->Num_Meses        = 0;
                    $model_consolidado->status           = 'A';
                    $model_consolidado->itemPos          = 1;
                    $model_consolidado->Activo           = 1;

                    $model_consolidado->save();

                }//s51
            }//s50
        }//s52

          $linea++;
        }//s53


        if($tipo == "th" && $mensaje_folio_exist == "" ) //Por alguna razón no registra la 2da fila del td_pedido por lo que mientras lo registro a parte.&& $num_pedidos > 1
        {//s54
            $row = $row_hack;
            $model = new PedidosItems(); 

            $cve_articulo  = $this->pSQL($row[self::CVE_ARTICULO]);
            $clave_cliente = $this->pSQL($row[self::CLAVE_CLIENTE]);
            $Folio         = $this->pSQL($row[self::FOLIO]);

            $model->Fol_folio        = $this->pSQL($row[self::FOLIO]);
            $model->Cve_articulo     = $this->pSQL($row[self::CVE_ARTICULO]);
            $model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
            $model->Num_Meses        = $this->pSQL($row[self::MESES]);
            $model->status           = 'A';//$this->pSQL($row[12]);

            $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $num_articulo = $resul['num_articulo'];

            $sql = "SELECT  COUNT(*) as cliente FROM c_cliente WHERE Cve_CteProv = '$clave_cliente'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $cliente = $resul['cliente'];

            $sql = "SELECT  COUNT(*) AS pedido FROM td_pedido WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $pedido_articulo = $resul['pedido'];

            if($num_articulo && $cliente)
            {//s44
                if($pedido_articulo)
                {
                    $cantidad_agr = $this->pSQL($row[self::NUM_CANTIDAD]);
                    if($cantidad_agr)
                    {
                      $sql = "UPDATE td_pedido SET Num_cantidad = (Num_cantidad + $cantidad_agr) WHERE Fol_folio = '$Folio' AND Cve_articulo = '$cve_articulo'";
                      $rs = mysqli_query($conn, $sql);
                    }
                }
                else
                {
                   //$model->Num_cantidad     = $this->pSQL($row[self::NUM_CANTIDAD]);
                   $respuesta = $model->save();
                }



        //$folio_cont = "S".$this->getYearActual().$this->getMesActual().$this->getConsecutivoFolio($consecutivo_folio);
        //$consecutivo_folio++;
                if($orden_de_compra != $this->pSQL($row[self::FOLIO]))
                {//s45
                    //$num_pedidos++;
                    $orden_de_compra = $this->pSQL($row[self::FOLIO]);
                    $secuencia = 0;
                }//s45
                //$num_pedidos++;
            }//s44
            else
            {//s46
                if(!$num_articulo && $cve_articulo)
                {//s47
                    //if($tipo == "th") $cve_clte = $this->pSQL($row[self::CLAVE_CLIENTE]);
                    $registro_actual = "(Cve_Almacen = ".$cve_almac.", Cve_Articulo = ".$cve_articulo.", Cve_Cliente = ".$cve_clte.", Sku = ".$Sku.")\n";
                    if($registro_actual != $registro_anterior) $articulos_no_existentes .= $registro_actual;
                    $registro_anterior = $registro_actual;
                }//s47

                if(!$cliente && !in_array($cve_clte, $clientes_registrados) && $cve_clte)
                {//s48
                    $registro_actual_cliente = $cve_clte."\n";
                    if($registro_actual_cliente != $registro_anterior_cliente) $clientes_no_existentes .= $registro_actual_cliente;
                    $registro_anterior_cliente = $registro_actual_cliente;
                    array_push($clientes_registrados, $cve_clte);
                }//s48
            }//s46
          }//s54

        $mensaje_no_existentes = ""; $mensaje_clientes_no_existentes = "";
        if($articulos_no_existentes && $num_pedidos)
           $mensaje_no_existentes = "\n\n\nSin embargo, Los siguientes productos no se encuentran en el Catálogo: \n\n\n ". $articulos_no_existentes;

         if($clientes_no_existentes != "")
            $mensaje_clientes_no_existentes = "\n\n\nLos siguientes clientes no existen en el sistema: \n\n $clientes_no_existentes";

         $statusText = "$sql_exist Pedidos importados con exito. Total de Pedidos: \"{$num_pedidos}\" $mensaje_no_existentes".$mensaje_clientes_no_existentes;

         if($num_pedidos == 0 && $mensaje_clientes_no_existentes == "" && $mensaje_folio_exist == "")
            $statusText = "No se ha ingresado ningún pedido";
          else if($mensaje_folio_exist != "")
            $statusText = $mensaje_folio_exist;

      
        $this->response(200, [

            'statusText' =>  $statusText,
            'pedidos' => $num_pedidos,
            'mensaje_dev' => $mensaje_dev,
            'tracking_codigo' => $tracking_codigo,
            "lineas"=>$lineas
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