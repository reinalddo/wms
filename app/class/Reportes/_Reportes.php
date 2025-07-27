<?php

namespace Reportes;

class Reportes 
{
    var $identifier;

    public function __construct( $Fol_Folio = false, $key = false ) {}

    public function concentradoexistencia()
    {
        $sql = "
            SELECT  
                IFNULL(p.Nombre, '--') AS proveedor,
                v.cve_articulo AS articulo,
                IFNULL(a.des_articulo, '--') AS nombre,
                v.Existencia AS existencia
            FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            WHERE v.Existencia > 0;
        ";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $articulos = $sth->fetchAll();
        $sql = "SELECT SUM(Existencia) AS cantidad FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $cantidad = $sth->fetch()['cantidad'];
        $data = array('articulos' => $articulos,'cantidad'  => $cantidad);
        return $data;
    }

    function entrada($id)
    {

        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
        if (!($res_instancia = mysqli_query($conexion, $sql_instancia)))
            echo "Falló la preparación instancia: (" . mysqli_error($conexion) . ") ";
        $instancia = mysqli_fetch_array($res_instancia)['instancia'];

        if($instancia == 'dev')
        {
        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res_charset = mysqli_query($conexion, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conexion) . ") ";
        $charset = mysqli_fetch_array($res_charset)['charset'];
        mysqli_set_charset($conexion , $charset);
        }
        else
            $conexion-> set_charset("utf8mb4");

      $sqlCabecera = "
          SELECT  
              de.fol_folio AS entrada, 
              al.nombre AS almacen, 
              IFNULL(ad.factura, '--') AS orden_entrada,
              p.Nombre AS proveedor, 
              DATE_FORMAT(ce.Fec_Entrada, '%d-%m-%Y %H:%i:%s') AS fecha_entrada, 
              #DATE_FORMAT(ad.fech_pedimento, '%d-%m-%Y %H:%i:%s') AS fecha_entrada, 
              (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = ce.Cve_Usuario) AS dio_entrada,
              IFNULL(u.nombre_completo, '--') AS autorizo
          FROM td_entalmacen de
              LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
              LEFT JOIN th_aduana ad ON ad.num_pedimento = ce.id_ocompra 
              LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
              LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
              LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
              LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
              LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo
              LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
          WHERE de.fol_folio='$id'
          GROUP BY de.fol_folio
      ";

      $sql =  "
          SELECT  
              a.cve_articulo AS clave_articulo, 
              a.des_articulo AS articulo, 
              de.CantidadRecibida AS cantidad_recibida,
              #IFNULL(c_lotes.`LOTE`,'0') as lote,
              #IFNULL(c_serie.`numero_serie`,'0') AS serie,
              #IFNULL(c_lotes.`CADUCIDAD`,'0') AS caducidad,
              #IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), '') AS lote_serie,
              IFNULL(de.cve_lote, '') AS lote_serie,
              IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '') AS caducidad,
              IFNULL(de.cantidadPedida, de.CantidadRecibida) AS total_pedido,
              IFNULL((de.cantidadPedida-de.CantidadRecibida), 0) AS faltante
          FROM td_entalmacen de
              LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
              LEFT JOIN th_aduana ad ON ad.num_pedimento = ce.id_ocompra 
              LEFT JOIN td_aduana  adu on adu.num_orden =ce.Fol_Folio
              LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
              LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
              LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
              LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
              LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo 
              LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
           WHERE de.fol_folio='$id'
          GROUP BY de.cve_articulo,de.cve_ubicacion, de.cve_lote
          ORDER BY clave_articulo, faltante DESC
      ";

      $sthCabecera = \db()->prepare($sqlCabecera);
      $sthCabecera->execute();
      $header = $sthCabecera->fetchAll();
      $sth = \db()->prepare($sql);
      $sth->execute();
      $body = $sth->fetchAll();
      return array("header" => $header,"body" => $body);
    }
    
    function asn($id)
    {
//$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
//$utf8Sql->execute();
        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
        if (!($res_instancia = mysqli_query($conexion, $sql_instancia)))
            echo "Falló la preparación instancia: (" . mysqli_error($conexion) . ") ";
        $instancia = mysqli_fetch_array($res_instancia)['instancia'];

        if($instancia == 'dev')
        {
        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res_charset = mysqli_query($conexion, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conexion) . ") ";
        $charset = mysqli_fetch_array($res_charset)['charset'];
        mysqli_set_charset($conexion , $charset);
        }
        else
            $conexion-> set_charset("utf8mb4");

        //$Sql = "SET NAMES 'utf8mb4';";
        //if (!($res_charset = mysqli_query($conexion, $Sql))) 
        //    echo "Falló la preparación Charset: (" . mysqli_error($conexion) . ") ";
        /*
        $sqlCabecera = "
            SELECT 
                  IFNULL(th_ordenembarque.ID_OEmbarque, '') as folio,
                  DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y') AS FechaEmbarque,
                  DATE_FORMAT(IFNULL(th_ordenembarque.FechaEnvio, ''), '%d-%m-%Y') AS FechaEnvio,
                  IFNULL(c_cliente.RazonSocial, '') as cliente,
                  IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Pick_Num), '') AS factura,
                  IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Fol_folio), '') as pedido,
                  #IFNULL((SELECT SUM(Cantidad) FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = th_pedido.Fol_folio)), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  IFNULL((SELECT SUM(Cantidad) FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id') )), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))) AS cantidad,
                  COUNT(DISTINCT th_cajamixta.Cve_CajaMix) AS total_cajas
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque on td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido on th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN c_cliente on c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta on th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta on td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            WHERE th_ordenembarque.ID_OEmbarque = '$id'
            GROUP BY th_ordenembarque.ID_OEmbarque;
        ";
        */
        $sqlCabecera = "
SELECT 
    cab.folio, cab.FechaEmbarque, cab.FechaEnvio, cab.cliente, cab.factura, cab.pedido,  IF(cab.cs-SUM(cab.cantidad)>0, cab.cs, SUM(cab.cantidad)) AS cantidad, COUNT(cab.caja) AS total_cajas, SUM(cab.peso) AS peso
FROM (
SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y') AS FechaEmbarque,
                  DATE_FORMAT(IFNULL(th_ordenembarque.FechaEnvio, ''), '%d-%m-%Y') AS FechaEnvio,
                  IFNULL(c_cliente.RazonSocial, '') AS cliente,
                  IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Pick_Num), '') AS factura,
                  IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Fol_folio), '') AS pedido,
                  IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo) AS clave,
                  IFNULL(tds.LOTE, '') AS Lote_Serie,
                  IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  tds.Cantidad AS cs,
                  IFNULL(IFNULL(tt.cantidad, IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', tds.Cantidad, (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = td_cajamixta.Cve_articulo AND td_pedido.cve_lote = td_cajamixta.Cve_Lote))), tds.Cantidad) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, '') AS caja,
                  IFNULL(ch.CveLP, '') AS lp,
                  IFNULL(a.peso, 0) AS peso,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            #LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio #AND de.Cve_articulo = tds.Cve_articulo
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio AND th_cajamixta.Sufijo = tds.Sufijo
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = tds.Cve_articulo AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo,tds.Cve_articulo)
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio AND tt.cve_articulo = tds.Cve_articulo AND IFNULL(tt.lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
          LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = td_cajamixta.Cve_articulo
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = tds.LOTE
            WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo)  
            AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio) 
            AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
        GROUP BY clave, Lote_Serie, folio, lp

) AS cab
";
        /*
        $sql = "
            SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) AS clave,
              #IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), IFNULL(de.cve_lote, IFNULL(td_cajamixta.cve_lote, ''))) AS Lote_Serie,
              IFNULL(tds.LOTE, '') AS Lote_Serie,
              IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  IFNULL(tt.cantidad, IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', SUM(de.Num_cantidad), (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = de.Cve_articulo AND td_pedido.cve_lote = de.cve_lote))) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, '') AS caja,
                  IFNULL(ch.CveLP, '') AS lp,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio AND de.Cve_articulo = tds.Cve_articulo
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix #AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(de.cve_lote, IFNULL(tds.LOTE, ''))
            LEFT JOIN c_articulo a ON a.cve_articulo = de.Cve_articulo
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio and tt.cve_articulo = tds.Cve_articulo and ifnull(tt.lote, '') = ifnull(tds.LOTE, '')
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
          LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo 
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
            WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) 
            AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio) 
            AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
            GROUP BY clave, Lote_Serie, folio, lp
            ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja;
        ";
*/
        $sql = "
SELECT * FROM (
        SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo) AS clave,
              #IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), IFNULL(de.cve_lote, IFNULL(td_cajamixta.cve_lote, ''))) AS Lote_Serie,
              IFNULL(tds.LOTE, '') AS Lote_Serie,
              IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  IFNULL(IFNULL(IFNULL(ppt.Num_Cantidad, tt.cantidad), IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', tds.Cantidad, (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = td_cajamixta.Cve_articulo AND td_pedido.cve_lote = td_cajamixta.Cve_Lote))), tds.Cantidad) AS cantidad,                  IFNULL(th_cajamixta.cve_tipocaja, 'Pallet') AS caja,
                  IFNULL(ch.CveLP, '') AS lp,
                  IFNULL(ch.clave_contenedor, '') AS clave_contenedor,
                  IFNULL(a.peso, 0) AS peso,
                  IFNULL(th_cajamixta.Guia, '') AS guia,
                  IFNULL(ch.CveLP, '') AS lp_comp,
                  IFNULL(ch.clave_contenedor, '') AS cc_comp
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            #LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio #AND de.Cve_articulo = tds.Cve_articulo
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio AND th_cajamixta.Sufijo = tds.Sufijo
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = tds.Cve_articulo AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo,tds.Cve_articulo)
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio AND tt.cve_articulo = tds.Cve_articulo AND IFNULL(tt.lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN td_pedidoxtarima ppt ON ppt.Fol_folio = tds.Fol_folio AND ppt.Cve_Articulo = tds.Cve_articulo AND ppt.cve_lote = tds.LOTE
            LEFT JOIN c_charolas ch ON ch.IDContenedor = IFNULL(ppt.nTarima, tt.ntarima) #ch.IDContenedor = tt.ntarima
          LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = td_cajamixta.Cve_articulo
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = tds.LOTE
            WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo)  
            #AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio) 
            AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
            GROUP BY clave, Lote_Serie, folio, lp
            #ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja;

UNION

#SELECT * FROM (
        SELECT DISTINCT o.embarque, o.folio, o.clave, o.Lote_Serie, o.caducidad, o.articulo, (o.cs-SUM(o.cantidad)) AS cantidad, o.caja, '' AS lp, '' AS clave_contenedor, o.peso, o.guia, o.lp AS lp_comp, o.clave_contenedor AS cc_comp 
        FROM (

SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo) AS clave,
              #IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), IFNULL(de.cve_lote, IFNULL(td_cajamixta.cve_lote, ''))) AS Lote_Serie,
              IFNULL(tds.LOTE, '') AS Lote_Serie,
              IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  tds.Cantidad AS cs,
                  IFNULL(IFNULL(IFNULL(ppt.Num_cantidad, tt.cantidad), IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', tds.Cantidad, (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = td_cajamixta.Cve_articulo AND td_pedido.cve_lote = td_cajamixta.Cve_Lote))), tds.Cantidad) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, 'Pallet') AS caja,
                  IFNULL(ch.CveLP, '') AS lp,
                  IFNULL(ch.clave_contenedor, '') AS clave_contenedor,
                  #'' AS lp,
                  IFNULL(a.peso, 0) AS peso,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            #LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio #AND de.Cve_articulo = tds.Cve_articulo
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio AND th_cajamixta.Sufijo = tds.Sufijo
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = tds.Cve_articulo AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo,tds.Cve_articulo)
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio AND tt.cve_articulo = tds.Cve_articulo AND IFNULL(tt.lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN td_pedidoxtarima ppt ON ppt.Fol_folio = tds.Fol_folio AND ppt.Cve_Articulo = tds.Cve_articulo AND ppt.cve_lote = tds.LOTE
            LEFT JOIN c_charolas ch ON ch.IDContenedor = IFNULL(ppt.nTarima, tt.ntarima) #ch.IDContenedor = tt.ntarima
          LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = td_cajamixta.Cve_articulo
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = tds.LOTE
            WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo)  
            #AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio) 
            AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
            GROUP BY clave, Lote_Serie, folio, lp

            ) AS o 
            #) AS p WHERE p.cantidad > 0
            ORDER BY folio, guia
) AS resp
WHERE IFNULL(resp.lp_comp, resp.cc_comp) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = resp.folio) 

            ";
        $sthCabecera = \db()->prepare($sqlCabecera);
        $sthCabecera->execute();
        $header = $sthCabecera->fetchAll();
        $sth = \db()->prepare($sql);
        $sth->execute();
        $body = $sth->fetchAll();
        return array("header" => $header,"body" => $body, "sqlCabecera" => $sqlCabecera, "sql" => $sql);
    }

    function comprobantei()
    {
        $sql='
            select 
                th_entalmacen.Fol_Folio as numero_recepcion,
                th_entalmacen.Fec_Entrada as fecha_ingreso,
                t_protocolo.descripcion as protocolo,
                th_entalmacen.fol_oep as numero_pedimiento,
                "Factura" as manifiesto,
                td_entalmacen.cve_articulo as numero_parte,
                c_articulo.des_articulo as descripcion,
                td_entalmacen.CantidadRecibida as cantidad,
                sum(c_articulo.peso*td_entalmacen.CantidadRecibida) as peso,
                c_compania.des_cia as empresa
            from th_entalmacen
                INNER JOIN td_entalmacen on td_entalmacen.fol_folio=th_entalmacen.Fol_Folio
                INNER JOIN th_aduana on th_aduana.num_pedimento=th_entalmacen.Fol_Folio
                INNER JOIN c_articulo on c_articulo.cve_articulo=td_entalmacen.cve_articulo
                INNER JOIN c_usuario on th_aduana.cve_usuario=c_usuario.id_user
                INNER JOIN c_compania on c_compania.cve_cia= c_usuario.cve_cia
                INNER JOIN t_protocolo on th_aduana.ID_Protocolo=t_protocolo.ID_Protocolo
            GROUP BY td_entalmacen.cve_articulo;
        ';
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function invfidet($id)
    {
        $sqlCabecera = "
            Select 
                c_almacenp.nombre as almacen,
                t_invpiezas.ID_Inventario as inventario,
                t_invpiezas.fecha as fecha_inicio,
                max(t_invpiezas.fecha_fin) as fecha_final,
                max(t_invpiezas.NConteo) as conteos,
                count(distinct(t_invpiezas.cve_articulo)) as nproductos,
                sum(if(t_invpiezas.NConteo = (select max(a.NConteo) from t_invpiezas a where a.ID_Inventario = t_invpiezas.ID_Inventario),(t_invpiezas.Cantidad - t_invpiezas.ExistenciaTeorica),0)) as diferencias,
                (select t_conteoinventario.cve_supervisor from t_conteoinventario where t_conteoinventario.ID_Inventario = t_invpiezas.ID_Inventario GROUP by t_conteoinventario.cve_supervisor)as supervisor
            from t_invpiezas
                left join c_ubicacion on c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
                left join c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
                left join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
            where ID_Inventario = {$id};
        ";

        $sql = "
           Select
            Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
            t_invpiezas.cve_articulo as clave,
            c_articulo.des_articulo as descripcion,
            IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
            Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
            '--' as serie,
            t_invpiezas.NConteo as conteo,
            (t_invpiezas.existenciateorica) as stockTeorico,
            (t_invpiezas.cantidad) as stockFisico,
            (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
            ifnull(c_usuario.nombre_completo,'--') as usuario,
            'Piezas' as unidad_medida,
            c_almacen.des_almac as zona
        FROM t_invpiezas
            LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario
            LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
            LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
            LEFT JOIN c_serie on c_serie.cve_articulo = t_invpiezas.cve_articulo 
            LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
            LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
            LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
        WHERE  t_invpiezas.id_inventario = $id
        order by  t_invpiezas.NConteo, c_articulo.des_articulo
        ";
        $sthCabecera = \db()->prepare($sqlCabecera);
        $sthCabecera->execute();
        $header = $sthCabecera->fetchAll();
        $sth = \db()->prepare($sql);
        $sth->execute();
        $body = $sth->fetchAll();
        return array(
            "header"    => $header,
            "body"    => $body
        );
    }


    function maxmin1($post)
    {
        $tipo_u = $post['tipo'];
        $id_almacen = $post['id_almacen'];
        $es_almacen = $post['es_almacen'];
        $and="where 1 ";
        if($tipo_u=="PTL")
        {
          $and=" WHERE u.TECNOLOGIA = 'PTL' ";
        }
        else if($tipo_u=="Picking")
        {
          $and=" WHERE (u.TECNOLOGIA <> 'PTL' or IFNULL(u.TECNOLOGIA, '')) ";
        }

        $sql='
            SELECT * from(
        SELECT 
            ar.cve_articulo AS clave_articulo,
            ar.`des_articulo` AS descripcion_articulo,
            #concat(u.cve_rack,"-",u.cve_nivel,"-",u.Ubicacion) AS linea,
            u.CodigoCSD AS linea,
            if(u.TECNOLOGIA="PTL","PTL",if(u.picking="S","Picking","")) as ubicacion,
            "Piezas" AS unidad,
            ubi.`CapacidadMaxima` AS maximo,
            ubi.`CapacidadMinima` AS minimo,
            COALESCE(ex.Existencia, 0) AS existencia,
            (CASE
                WHEN COALESCE(ex.Existencia, 0) > ubi.CapacidadMinima THEN 0
                WHEN COALESCE(ex.Existencia, 0) < ubi.CapacidadMinima THEN ubi.CapacidadMaxima - COALESCE(ex.Existencia, 0)
                ELSE 0
            END) AS pedir,
            IF(ex.`Existencia`>ubi.`CapacidadMaxima`,"amarillo",IF(ex.`Existencia`>ubi.`CapacidadMinima`,"verde",IF(ex.`Existencia`<ubi.`CapacidadMinima`,"rojo","rojo")))AS sta
        FROM 
            ts_ubicxart AS ubi 
        INNER JOIN c_articulo AS ar ON ar.cve_articulo = ubi.cve_articulo 
        INNER JOIN c_ubicacion AS u ON u.`idy_ubica`=ubi.`idy_ubica`
        LEFT JOIN V_ExistenciaGralProduccion AS ex ON ex.`cve_ubicacion`=u.`idy_ubica` AND ex.`cve_articulo`=ubi.`cve_articulo` AND ex.`cve_almac` = '.$id_almacen.'
        LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo`
        '.$and.'and u.picking <> "N"
        GROUP BY ubi.`cve_articulo`,ubi.`idy_ubica`
        ORDER BY ar.`des_articulo`ASC
    )x WHERE x.maximo > 0 AND x.minimo > 0 AND x.sta = "rojo" 
        ';

    if($es_almacen == 1)
        $sql = '
        SELECT * from(
            SELECT 
                ar.cve_articulo AS clave_articulo,
                ar.`des_articulo` AS descripcion_articulo,
                "Piezas" AS unidad,
                ubi.`CapacidadMaxima` AS maximo,
                ubi.`CapacidadMinima` AS minimo,
                COALESCE(ex.Existencia, 0) AS existencia,
                (CASE
                    WHEN COALESCE(ex.Existencia, 0) > ubi.CapacidadMinima THEN 0
                    WHEN COALESCE(ex.Existencia, 0) < ubi.CapacidadMinima THEN ubi.CapacidadMaxima - COALESCE(ex.Existencia, 0)
                    ELSE 0
                END) AS pedir,
                IF(ex.`Existencia`>ubi.`CapacidadMaxima`,"amarillo",IF(ex.`Existencia`>ubi.`CapacidadMinima`,"verde",IF(ex.`Existencia`<ubi.`CapacidadMinima`,"rojo","rojo")))AS sta
            FROM 
                ts_ubicxart AS ubi 
            INNER JOIN c_articulo AS ar ON ar.cve_articulo = ubi.cve_articulo 
            LEFT JOIN V_ExistenciaGralProduccion AS ex ON ex.`cve_articulo`=ubi.`cve_articulo` AND ex.`cve_almac` = '.$id_almacen.' 
            LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo`
            GROUP BY ubi.`cve_articulo`
            ORDER BY ar.`des_articulo`ASC
        )x WHERE x.maximo > 0 AND x.minimo > 0
            '; 


        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function maxmin2()
    {
        $sql = "
            SELECT
                ar.`cve_articulo` as idy_ubica,
                c_ubicacion.cve_almac,
                c_ubicacion.cve_pasillo,
                c_ubicacion.cve_rack,
                c_ubicacion.cve_nivel,
                c_ubicacion.num_ancho,
                c_ubicacion.num_largo,
                c_ubicacion.num_alto,
                c_ubicacion.picking,
                IF(c_ubicacion.TECNOLOGIA='PTL','S','N') AS ptl,
                IF(c_ubicacion.Maximo IS NULL,0,c_ubicacion.Maximo) AS maximo,
                IF(c_ubicacion.Minimo IS NULL,0,c_ubicacion.Minimo) AS minimo,
                c_ubicacion.Seccion,
                c_ubicacion.Ubicacion,
                c_ubicacion.PesoMaximo,
                IFNULL(TRUNCATE((c_ubicacion.num_ancho / 1000) * (c_ubicacion.num_alto / 1000) * (c_ubicacion.num_largo / 1000), 2), 0) AS volumen,
                c_ubicacion.CodigoCSD,
                c_ubicacion.TECNOLOGIA,
                c_ubicacion.Activo,
                c_almacen.cve_almac,
                c_almacen.des_almac,
                CONCAT(c_ubicacion.num_largo,' X',c_ubicacion.num_ancho,'  X',c_ubicacion.num_alto)AS dim,
                ar.`des_articulo`,
                IF((e.Existencia) > a.CapacidadMinima, 0, IF((a.CapacidadMaxima)-(e.Existencia) is null, 0, (a.CapacidadMaxima) - (e.`Existencia`))) AS reabastecer,
                COALESCE(e.Existencia, 0) AS existencia
            FROM ts_ubicxart AS a
                LEFT JOIN c_ubicacion ON a.`idy_ubica`=c_ubicacion.`idy_ubica`
                LEFT JOIN c_almacen ON c_almacen.cve_almacenp=c_ubicacion.`cve_almac`
                LEFT JOIN c_articulo AS ar ON ar.cve_articulo = a.`cve_articulo`
                LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = a.cve_articulo AND e.cve_ubicacion = a.idy_ubica
            WHERE c_ubicacion.Activo = '1' 
                AND c_ubicacion.`picking`='S' OR c_ubicacion.`TECNOLOGIA`='PTL'
            GROUP BY a.`idy_ubica`
            ORDER BY c_ubicacion.idy_ubica;
        "; 
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function difconteos()
    {
        $sql='
            SELECT
                th_inventario.ID_Inventario AS inventario,
                th_inventario.Fecha AS fecha,
                c1.NConteo AS pconteo,
                c2.NConteo AS sconteo,
                c_proveedores.Nombre as proveedor,
                t_invpiezas.cve_articulo AS art,
                c_articulo.des_articulo AS nombre,
                sum(ts_existenciapiezas.Existencia) AS conteo1,
                sum(ex2.Existencia) AS conteo2,
                sum( ts_existenciapiezas.Existencia-ex2.Existencia) as diferencia,
                c_almacenp.nombre AS almacen,
                t_invpiezas.idy_ubica AS ubicacion,
                c_ubicacion.cve_pasillo as pasillo,
                c_ubicacion.cve_rack as rack,
                c_ubicacion.cve_nivel as nivel
            FROM t_invpiezas
                LEFT JOIN th_inventario ON t_invpiezas.ID_Inventario = th_inventario.ID_Inventario
                LEFT JOIN t_conteoinventario c1 ON c1.ID_Inventario = th_inventario.ID_Inventario
                LEFT JOIN t_conteoinventario c2 ON c2.ID_Inventario = th_inventario.ID_Inventario
                LEFT JOIN c_almacenp ON th_inventario.cve_almacen = c_almacenp.clave
                LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
                LEFT JOIN ts_existenciapiezas ON ts_existenciapiezas.cve_almac = t_invpiezas.cve_articulo and ts_existenciapiezas.idy_ubica = t_invpiezas.idy_ubica
                LEFT JOIN ts_existenciapiezas ex2 ON ex2.cve_almac = t_invpiezas.cve_articulo and ex2.idy_ubica = t_invpiezas.idy_ubica
                LEFT JOIN c_proveedores on c_articulo.cve_codprov= c_proveedores.cve_proveedor
                LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica=ex2.idy_ubica and ts_existenciapiezas.idy_ubica=c_ubicacion.idy_ubica
            GROUP BY t_invpiezas.cve_articulo;
        ';
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function invcicl()
    {
        $sql='
            SELECT
                td_pedido.Cve_articulo as clave,
                c_articulo.des_articulo as nombre,
                c_lotes.LOTE as lote,
                td_pedido.Num_cantidad as cantidad,
                "nose" as existencia,
                "nose" as diferencia,
                c_usuario.des_usuario as usuario
            FROM td_pedido
                left join th_pedido on td_pedido.Fol_folio=th_pedido.Fol_folio
                left join c_articulo on td_pedido.Cve_articulo=c_articulo.cve_articulo
                left join c_usuario on th_pedido.Cve_Usuario=c_usuario.cve_usuario
                left join c_lotes on td_pedido.cve_lote=c_lotes.LOTE;
        ';
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function invcicldet($id)
    {
        $sqlCabecera = "
            SELECT  
                al.nombre AS almacen,   
                hi.ID_PLAN AS inventario,
                DATE_FORMAT(hi.FECHA_APLICA, '%d-%m-%Y %H:%i:%s') AS fecha
            FROM det_planifica_inventario hi 
                LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
                LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
                LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
                LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	              LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
                LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
                LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
                LEFT JOIN c_serie s ON s.cve_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
                and hi.ID_PLAN={$id}
            GROUP BY hi.ID_PLAN
            ORDER BY hi.ID_PLAN DESC;
        ";

        $sql="
            SELECT  
                al.nombre AS almacen,   
                us.nombre_completo AS usuario,
                hi.ID_PLAN AS inventario,
                DATE_FORMAT(hi.FECHA_APLICA, '%d-%m-%Y %H:%i:%s') AS fecha, 
                ip.cve_articulo AS clave_articulo,
                ar.des_articulo AS descripcion_articulo,
                ip.`ExistenciaTeorica` AS stockTeorico,
                ip.NConteo AS conteo,
                (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$id} AND cve_articulo = ip.cve_articulo AND idy_ubica =ip.idy_ubica AND NConteo = ip.NConteo) AS stockFisico,
                ub.CodigoCSD AS ubicacion,
                IFNULL(l.LOTE,'0') AS lote,
                IFNULL(l.CADUCIDAD,'0') AS caducidad,
                IFNULL(s.numero_serie,'0') AS numero_serie,
                IFNULL(ip.ExistenciaTeorica,'0') AS existencia,
                (ip.Cantidad - ip.ExistenciaTeorica) AS diferencia
            FROM det_planifica_inventario hi 
                LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
                LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
                LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
                LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
                LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
                LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
                LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
                LEFT JOIN c_serie s ON s.cve_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
                and hi.ID_PLAN={$id}
            GROUP BY hi.ID_PLAN,ip.`idy_ubica`, ip.NConteo
            ORDER BY hi.ID_PLAN DESC, ip.`idy_ubica` ASC, ip.NConteo ASC;
        ";
        $sthCabecera = \db()->prepare($sqlCabecera);
        $sthCabecera->execute();
        $header = $sthCabecera->fetchAll();
        $sth = \db()->prepare($sql);
        $sth->execute();
        $body = $sth->fetchAll();
        return array("header" => $header,"body" => $body);
    }

    function guiasembarque($FolPedidoCon=null, $FacturaMadre=null)
    {
        $sql='
            SELECT
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
            FROM t_consolidado
                INNER JOIN th_consolidado ON t_consolidado.Fol_PedidoCon = th_consolidado.Fol_PedidoCon
                INNER JOIN td_consolidado ON th_consolidado.Fol_PedidoCon = td_consolidado.Fol_PedidoCon And t_consolidado.Fol_Folio=td_consolidado.Fol_Folio
                INNER JOIN th_pedido ON td_consolidado.Fol_Folio = th_pedido.Fol_folio
                INNER JOIN td_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
                LEFT JOIN c_cliente ON th_pedido.Cve_clte = c_cliente.Cve_Clte;
        ';
        if (!empty($FolPedidoCon)) $sql .= "Where t_consolidado.Fol_PedidoCon='$FolPedidoCon' AND td_consolidado.Fact_Madre='$FacturaMadre'";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function ubicaciones($id_ubicacion, $cve_cia)
    {
        $ubi = "u.CodigoCSD";
        if(!empty($cve_cia))
        {
            $sql = "SELECT codigo FROM t_codigocsd WHERE cve_cia = '".$cve_cia."'"; 
            if($result = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2())))
            {
                $obj = $result->fetch_object();
                if($obj->codigo != null)
                {
                    $array = explode("-", $obj->codigo);
                    $ubi = "CONCAT(";
                    for($l = 0; $l < count($array); $l++)
                    {

                        if($array[$l] === "cve_rack")
                            $ubi .= "u.cve_rack";
                        else if($array[$l] === "cve_nivel")
                            $ubi .= "cve_nivel";
                        else if($array[$l] === "Seccion")
                            $ubi .= "u.Seccion";
                        else if($array[$l] === "Ubicacion")
                            $ubi .= "u.Ubicacion";
                        else if($array[$l] === "cve_pasillo")
                            $ubi .= "u.cve_pasillo";
                        if($l !== (count($array) - 1))
                            $ubi .= ",'-',";
                    }
                    $ubi .= ")";
                }
            }
        }

        $sql = "
            SELECT $ubi as ubicacion,
                u.cve_rack as rack,
                u.Seccion as seccion,
                u.cve_nivel as nivel,
                u.Ubicacion as ubic,			
                u.idy_ubica as id_ubicacion
			      from c_ubicacion u ;
        ";
        if (!empty($id_ubicacion)) $sql .= "Where u.idy_ubica=$id_ubicacion";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function lotesVencidos($post)
    {
      $almacen = $post['almacen'];
      /*
        $sql = "
            SELECT 
                a.cve_articulo as cve_articulo,
                a.des_articulo as articulo, 
                l.LOTE as lote,
                l.CADUCIDAD as caducidad,
                 CONCAT(u.cve_rack,'-',u.Seccion,'-',u.cve_nivel,'-',u.Ubicacion) as ubicacion,
                v.Existencia as existencia
            FROM c_lotes l
                INNER JOIN c_articulo a on a.cve_articulo=l.cve_articulo
                LEFT JOIN c_almacenp on a.cve_almac= c_almacenp.id
                INNER JOIN vs_existencia v on l.cve_articulo=v.cve_articulo  and l.LOTE=v.cve_lote
                LEFT JOIN c_ubicacion u on u.idy_ubica=v.idy_ubica
            where l.Activo = '1' 
                AND STR_TO_DATE(CADUCIDAD,'%d-%m-%Y') BETWEEN DATE_SUB(NOW(), INTERVAL 3 MONTH) AND NOW();
        ";
        */
/*  
        $sql = "SELECT 
                c_articulo.cve_articulo AS cve_articulo,
                c_articulo.des_articulo AS articulo,
                c_lotes.LOTE AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                c_ubicacion.CodigoCSD AS ubicacion,
                vp.Existencia AS existencia,
                p.Nombre AS Proveedor
            FROM V_ExistenciaGral vp
            LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
            LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
            LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vp.ID_Proveedor
            WHERE c_lotes.Caducidad < CURDATE()
            AND c_lotes.Caducidad != '0000-00-00'
            AND vp.tipo = 'ubicacion'
            AND Existencia > 0
            AND c_ubicacion.CodigoCSD != ''
            AND c_almacenp.id='{$almacen}'
            ORDER BY vp.cve_articulo, vp.cve_lote";
*/
    $sql = "SELECT 
                c_articulo.cve_articulo AS cve_articulo,
                c_articulo.des_articulo AS articulo,
                c_lotes.LOTE AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                c_ubicacion.CodigoCSD AS ubicacion,
                vp.Existencia AS existencia,
                (SELECT IFNULL(DATE_FORMAT(fecha_fin, '%d-%m-%Y'), '--') FROM td_entalmacen WHERE cve_articulo = c_lotes.cve_articulo ORDER BY id DESC LIMIT 1) AS fecha_ingreso,
                p.Nombre AS Proveedor
            FROM V_ExistenciaGral vp
            LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
            LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
            LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vp.ID_Proveedor
            WHERE DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') < DATE_FORMAT(CURDATE(), '%d-%m-%Y') 
            AND DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') != DATE_FORMAT('00-00-0000', '%d-%m-%Y')
            AND vp.tipo = 'ubicacion'
            AND Existencia > 0
            AND c_ubicacion.CodigoCSD != ''
            AND c_almacenp.id='{$almacen}' 
            ORDER BY vp.cve_articulo, vp.cve_lote
            ";
            $limit = "LIMIT $page, $limit; ";
        //if (!empty($id_ubicacion)) $sql .= "Where u.idy_ubica=$id_ubicacion";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function lotesPorVencer()
    {
        $sql = "  
            SELECT 
                a.cve_articulo as cve_articulo,
                a.des_articulo as articulo, 
                l.LOTE as lote,
                l.CADUCIDAD as caducidad,
                CONCAT(u.cve_rack,'-',u.Seccion,'-',u.cve_nivel,'-',u.Ubicacion) as ubicacion,
                v.Existencia as existencia
            FROM c_lotes l
                INNER JOIN c_articulo a on a.cve_articulo=l.cve_articulo
                LEFT JOIN c_almacenp on a.cve_almac= c_almacenp.id
                INNER JOIN vs_existencia v on l.cve_articulo=v.cve_articulo  and l.LOTE=v.cve_lote
                LEFT JOIN c_ubicacion u on u.idy_ubica=v.idy_ubica
            where l.Activo = '1'  
                AND STR_TO_DATE(CADUCIDAD,'%d-%m-%Y') BETWEEN NOW()
                AND DATE_ADD(NOW(),INTERVAL 3 MONTH);
        ";
        if (!empty($id_ubicacion)) $sql .= "Where u.idy_ubica=$id_ubicacion";
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function existenciaubica($almacen,$articulo, $zona, $bl, $contenedor, $cve_proveedor, $proveedor, $grupo, $clasificacion, $lp, $art_obsoletos, $mostrar_folios_excel_existencias, $existencia_cajas, $lote)
    {
    $zona_produccion = "";
    $num_produccion = 0;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $utf8Sql = "SET NAMES 'utf8mb4';";
    $res_charset = mysqli_query($conn, $utf8Sql);
/*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    $sql_obsoletos = "";
    if($art_obsoletos == 1)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad < CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";

    if($art_obsoletos == 0)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad >= CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";


    $zona_produccion = "";
    $num_produccion = 0;
    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}'";
        //AND AreaProduccion = 'S'
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        $num_produccion = mysqli_num_rows($query_zona_produccion);
        //if($query_zona_produccion){
        $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        //}
    }

    $_page = 0;

      if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = ' WHERE id = "'.$almacen.'" ';
/*
    $sql1 = 'SELECT * FROM c_almacenp $sqlAlmacen';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
*/
    $sql_folios = ""; $sql_foliox = ""; $group_mostrar_folios = "";$left_join_folios = ""; 
    $field_folios = " a.cve_articulo as clave, ";
    if($mostrar_folios_excel_existencias) 
    {
        //$sql_folios = " IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')))), '')  AS folio, ";

        $field_folios = " IF((tdt.fol_folio IS NOT NULL AND e.Cve_Contenedor != '') OR (tdt.fol_folio IS NULL AND e.Cve_Contenedor = ''), a.cve_articulo, '') AS clave, ";
        $left_join_folios = " LEFT JOIN td_entalmacenxtarima tdt ON IFNULL(tdt.fol_folio, '') = IFNULL(th.Fol_folio, '') AND IFNULL(tdt.ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(tdt.Cve_Articulo, '') = IFNULL(td.cve_articulo, '') AND IFNULL(ta.Cve_Almac, '') = IFNULL(ap.clave, '') AND IFNULL(tdt.Cve_Lote, '') = IFNULL(e.cve_lote, '') AND IFNULL(th.Fol_OEP, '') =  IFNULL(ta.Factura, '') "; 
        $sql_folios = " IFNULL(ta.Factura, '')  AS folio, ";
        $sql_foliox = ", x.folio";
        $group_mostrar_folios = ", folio";
    }

    $sql_proyecto = " IFNULL(th.Proyecto, '') as proyecto, ";
    $sql_proyectox = ", x.proyecto";
    $group_mostrar_proyecto = ", proyecto";

    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $zona_rts = "";
    $zona_rtm_tipo = "ubicacion";
    $zona_rtm_tipo2 = "";
    if($zona == "RTS")
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') ";

        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen} AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
        $zona_rtm_tipo2 = " AND x.tipo_ubicacion = '' ";
    }


    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";

    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
    $sqlCliente = !empty($cve_cliente) ? "INNER JOIN c_cliente c ON c.ID_Proveedor = p.ID_Proveedor AND e.ID_Proveedor = c.ID_Proveedor AND c.Cve_Clte = '{$cve_cliente}'" : "";
    $sqlProveedor2 = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
  
    $sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";

    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";
    $sqlLP = !empty($lp) ? "AND x.LP like '%{$lp}%'" : "";

    $sqlGrupo = !empty($grupo) ? "AND gr.cve_gpoart = '{$grupo}'" : "";
    $sqlClasif = !empty($clasificacion) ? "AND cl.cve_sgpoart = '{$clasificacion}'" : "";


    $sqlbl_search = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP_search = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";
    

    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo2 = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $SQLrefWell = "";
    if($refWell)
        $SQLrefWell = " AND ta.recurso LIKE '%$refWell%' ";

    $SQLpedimentoW = "";
    if($pedimentoW)
        $SQLpedimentoW = " AND ta.Pedimento LIKE '%$pedimentoW%' ";


    $sqlCollation = "";
    if($instancia == 'foam')
    {
        $sqlCollation = " COLLATE utf8mb4_unicode_ci ";
    }

    $field_bl = " u.CodigoCSD AS codigo, ";
    if($instancia == 'asl' || $instancia == 'dicoisa')// || $instancia == 'oslo'
        $field_bl = " REPLACE(u.CodigoCSD, '-', '_') AS codigo, ";


   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $field_folio_ot = "''";
   $field_NCaja = "''";
   $SQL_FolioOT = "";
   if($zona_produccion == 'S' && $num_produccion < 2)
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";

       $field_folio_ot = "IFNULL(op.Folio_Pro, '')";
       $field_NCaja = "IFNULL(cm.NCaja, '')";
       $SQL_FolioOT = "
            LEFT JOIN t_tarima tt ON tt.ntarima = ch.IDContenedor 
            LEFT JOIN t_ordenprod op ON op.Cve_Articulo = IFNULL(e.cve_articulo, tt.cve_articulo ) AND IFNULL(op.Cve_Lote,'') = IFNULL(tt.lote, e.cve_lote) AND op.Folio_Pro = IFNULL(tt.Fol_Folio, op.Folio_Pro) 
            LEFT JOIN th_cajamixta cm ON cm.fol_folio = tt.Fol_Folio AND cm.Cve_CajaMix = tt.Caja_ref 
        ";

   }
   else if($num_produccion == 2)
    {
       $tabla_from = "V_ExistenciaGralProduccion";
       $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
    }
/*
    $sqlCount = "SELECT
                    count(e.cve_articulo) as total
                  FROM {$tabla_from} e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                  WHERE {$sqlAlmacen} e.tipo = 'ubicacion' AND e.Existencia > 0 {$sqlArticulo}  {$sqlZona} {$sqlProveedor}  {$sqlbl} {$sqlGrupo}";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }
*/
    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " (e.cve_almac = '{$almacen}')  AND ";//OR zona.cve_almacp = '{$almacen}'

$sql = "SET NAMES utf8mb4;";
$sth = \db()->prepare( $sql );
$sth->execute();

    $sql = "
      SELECT x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.zona, x.codigo, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.descripcion, x.des_grupo, x.des_clasif, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.caducidad, x.nserie, x.RP, x.peso, (x.cantidad) AS cantidad, (x.cantidad_kg) AS cantidad_kg, x.id_proveedor, (x.proveedor) AS proveedor, (x.empresa_proveedor) AS empresa_proveedor, x.fecha_ingreso, x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso, x.referencia_well, x.pedimento_well {$sql_foliox} {$sql_proyectox} FROM(
         SELECT DISTINCT 
            #IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            {$field_bl}
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(IFNULL(e.Cuarentena, 0) = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
             {$field_folios} 
            a.des_articulo as descripcion,
            IFNULL(trs.Cantidad, 0) AS RP,
            IFNULL(DATE_FORMAT(td.fecha_fin, '%d-%m-%Y'), '') AS fecha_ingreso,
            IFNULL(gr.des_gpoart, '') as des_grupo,
            IFNULL(cl.cve_sgpoart, '') as des_clasif,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            ta.recurso as referencia_well,
            ta.Pedimento as pedimento_well,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,

            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,

            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', ROUND(TRUNCATE(e.Existencia, 5), 4), e.Existencia) as cantidad,
            ROUND(TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 5), 4) AS cantidad_kg,

            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,

            IFNULL(poc.Nombre, '') AS empresa_proveedor,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            {$sql_folios}
            {$sql_proyecto}
            IFNULL(um.cve_umed, '') as um,
            a.control_peso
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.cve_ubicacion {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(z.cve_almacenp, e.cve_almac) #OR ap.id = zona.cve_almacp
            LEFT JOIN t_recorrido_surtido trs ON trs.Cve_articulo = e.cve_articulo AND trs.cve_lote = e.cve_lote AND trs.cve_almac = z.cve_almac AND e.cve_ubicacion = trs.idy_ubica 
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote 
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor
            {$left_join_folios}
            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} 
                $zona_rts

            #GROUP BY id_proveedor
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie

            ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC

                )x
            #where x.lote != '--'
            WHERE 1 AND x.id_almacen = '{$almacen}' AND x.clave != ''#AND x.id_proveedor IS NOT NULL
            {$sqlbl} 
            {$sqlLP} 
            {$sqlLotes} 
            {$sqlproveedor_tipo} 
            {$sqlProveedor2}
            GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie, id_proveedor {$group_mostrar_folios} {$group_mostrar_proyecto} 
            ";

//       echo var_dump($sth->fetchAll());
//       die();

        if($existencia_cajas == 1)
        {   

            $sql_BL = "";
            if($bl != "")
                $sql_BL = " AND u.CodigoCSD LIKE '%$bl%' ";

            $sql_cve_articulo = "";
            if($articulo)
                $sql_cve_articulo = " AND a.Cve_Articulo = '$articulo' ";

            $sql_lote = "";
            if($lote)
                $sql_lote = " AND ec.cve_lote = '$lote' ";

            $sql = "
            SELECT u.CodigoCSD AS ubicacion, IFNULL(ch.CveLP, '') AS LP, IFNULL(cj.CveLP, '') AS Caja, a.cve_articulo, a.des_articulo, IFNULL(ec.cve_lote, '') as cve_lote, 
                   COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(ec.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
                   ec.PiezasXCaja
            FROM ts_existenciacajas ec
            LEFT JOIN c_almacenp al ON al.id = ec.Cve_Almac
            LEFT JOIN c_almacen z ON z.cve_almacenp = al.id
            LEFT JOIN c_charolas ch ON ch.cve_almac = al.id AND ec.nTarima = ch.IDContenedor 
            LEFT JOIN c_charolas cj ON cj.cve_almac = al.id AND ec.Id_Caja = cj.IDContenedor AND cj.tipo = 'Caja'
            LEFT JOIN c_ubicacion u ON u.cve_almac = z.cve_almac
            LEFT JOIN c_articulo a ON ec.cve_articulo = a.cve_articulo
            LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Almac = al.id AND ra.Cve_Articulo = a.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ra.Cve_Articulo AND l.Lote = ec.cve_lote
            WHERE ec.Cve_Almac = $almacen AND ec.idy_ubica = u.idy_ubica {$sql_BL} {$sql_cve_articulo} {$sql_lote} ";
        }

      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
      //return $sql;
    }

    function pendienteAcomodoPDF($almacen)
    {
      $sql = "SELECT * FROM (
        SELECT 
           IFNULL(th_entalmacen.fol_folio,'') AS numero_oc,
            IFNULL(th_aduana.num_pedimento, '') AS folio_oc,
            IFNULL(th_aduana.factura, '') AS folio_erp,
            IFNULL(th_entalmacen.tipo, '') AS tipo,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = th_entalmacen.Cve_Usuario) AS usuario_activo,
            IFNULL(DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p'), '') AS fecha_entrega,
            (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %H:%i:%s %p')))  AS fecha_recepcion,
            (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %H:%i:%s %p'))  AS fecha_fin_recepcion,
            SUM(td_entalmacen.CantidadRecibida) - SUM(IFNULL(td_entalmacen.CantidadUbicada,0)) AS total_pedido,     
            #IFNULL(sum(c_articulo.peso * td_entalmacen.CantidadDisponible), 0) AS peso_estimado,
            #TRUNCATE(IFNULL(SUM(c_articulo.peso * td_entalmacen.CantidadRecibida), 0), 4) AS peso_estimado,
            TRUNCATE(IFNULL(SUM(c_articulo.peso * (td_entalmacen.CantidadRecibida - IFNULL(td_entalmacen.CantidadUbicada,0))), 0), 4) AS peso_estimado,
            c_proveedores.Nombre AS proveedor,
            '0' AS  cantidad_recibida,
            th_entalmacen.Fact_Prov AS facprov,
            th_entalmacen.Cve_Almac AS clave_almacen,
            th_entalmacen.Cve_Almac AS almacen,
            tubicacionesretencion.desc_ubicacion AS retencion
        FROM th_entalmacen
      LEFT JOIN td_entalmacen ON td_entalmacen.fol_folio = th_entalmacen.fol_folio
            LEFT JOIN c_usuario ON c_usuario.id_user = th_entalmacen.Cve_Usuario
            LEFT JOIN c_almacenp ON c_almacenp.clave = th_entalmacen.Cve_Almac
            LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor
            LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_entalmacen.cve_articulo
            LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = td_entalmacen.cve_ubicacion
        WHERE tubicacionesretencion.activo = '1'
        AND (td_entalmacen.CantidadRecibida - IFNULL(td_entalmacen.CantidadUbicada,0)) > 0
        AND th_entalmacen.Cve_Almac = '{$almacen}'
        GROUP BY th_entalmacen.Fol_Folio
        ORDER BY  th_entalmacen.Fol_Folio DESC) tabla";
      $sth = \db()->prepare( $sql );
      $sth->execute();
//       echo var_dump($sth->fetchAll());
//       die();
      return $sth->fetchAll();
    }


}
