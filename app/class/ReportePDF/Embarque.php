<?php

namespace ReportePDF;

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";

class Embarque{
  
  
  public function __construct()
  {
    
  }

    public function getDataPDF($folio, $cia, $folio_pedidos)
  {
    //$utf8Sql = "SET NAMES 'utf8mb4'";
    //$query = mysqli_query(\db2(), $utf8Sql);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query(\db2(), $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error(\db2()) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset(\db2() , $charset);

    $sqlHeader1 = "
        SELECT  
            COALESCE(e.descripcion,'--') as ubicacion,
            COALESCE(u.nombre_completo,'--')as usuario
        FROM th_ordenembarque o        
            LEFT join c_usuario u on u.cve_usuario = o.cve_usuario
            left join t_ubicacionembarque e on e.ID_Embarque = o.t_ubicacionembarque_id
        WHERE o.ID_OEmbarque = {$folio};
    ";
    $queryHeader1 = mysqli_query(\db2(), $sqlHeader1);
    
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryHeader1, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_header1 = $rowx;
    }

    $sqlHeader = "
        SELECT  
            o.ID_OEmbarque AS id,
            COALESCE(DATE_FORMAT(o.fecha, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(o.destino, '--') AS destino,
            COALESCE(o.comentarios, '--') AS comentarios,
            '--' AS chofer,
            COALESCE(t.Nombre,'--') AS transporte,
            COALESCE(o.status, '--') AS status,
            TRUNCATE((SELECT (COALESCE(SUM(c_articulo.peso*td_surtidopiezas.Cantidad),0)) FROM c_articulo INNER JOIN td_surtidopiezas ON td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo WHERE td_surtidopiezas.fol_folio IN ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS peso,
            TRUNCATE((SELECT COALESCE(SUM(((alto/1000) * (ancho/1000) * (fondo/1000))*td_surtidopiezas.Cantidad), 0) FROM c_articulo INNER JOIN td_surtidopiezas ON td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo WHERE td_surtidopiezas.fol_folio IN ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS volumen,
            #(SELECT COALESCE(SUM(1), 0) FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)) AS total_cajas,
            IFNULL(o.seguro, '') AS seguro,
            IFNULL(o.flete, '') AS flete,
            IFNULL(o.origen, '') AS origen,
            TRUNCATE((SELECT COALESCE(SUM(Cantidad), 0) FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),0) AS total_piezas
        FROM th_ordenembarque o        
            LEFT JOIN t_transporte t ON t.id = o.ID_Transporte
        WHERE o.ID_OEmbarque = {$folio};
    ";
    
    $queryHeader = mysqli_query(\db2(), $sqlHeader);

    $sqlHeaderCajas = "
        SELECT SUM(n_cjs.n_cajas) AS n_cajas FROM (
        SELECT s.fol_folio, s.Cve_articulo, s.Cantidad, IF(IFNULL(a.num_multiplo, 0) != 0, IFNULL(a.num_multiplo, 0), 1) AS pzasxcaja, (TRUNCATE(s.Cantidad/IF(IFNULL(a.num_multiplo, 0) != 0, IFNULL(a.num_multiplo, 0), 1), 0)) AS n_cajas
        FROM td_surtidopiezas s
        LEFT JOIN c_articulo a ON a.cve_articulo = s.Cve_articulo
        WHERE s.fol_folio IN (SELECT fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio})
        ) AS n_cjs;
    ";
    
    $queryHeaderCajas = mysqli_query(\db2(), $sqlHeaderCajas);

    $rows = array();
    while(($rowx = mysqli_fetch_array($queryHeader, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_Header = $rowx;
    }
  
    $rows_ncajas = array();
    while(($rowx_ncajas = mysqli_fetch_array($queryHeaderCajas, MYSQLI_ASSOC))) 
    {
        $rows_ncajas[] = $rowx_ncajas;
        $datos_Header_ncajas = $rowx_ncajas;
    }
  
/*
    $sql_total_cajas_tipo1 = "
        SELECT IF(art.num_multiplo>0, IFNULL(TRUNCATE(SUM(td.Cantidad)/art.num_multiplo,0), 0), COALESCE(SUM(1), 0)) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('$folio_pedidos') AND art.tipo_caja = th.cve_tipocaja";
    $query_total_cajas_tipo1 = mysqli_query(\db2(), $sql_total_cajas_tipo1);
    $total_cajas_tipo1 = mysqli_fetch_array($query_total_cajas_tipo1, MYSQLI_ASSOC)['Cantidad'];


    $sql_total_cajas_tipo2 = "
        SELECT COALESCE(SUM(1), 0) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('$folio_pedidos') AND art.tipo_caja != th.cve_tipocaja";
    $query_total_cajas_tipo2 = mysqli_query(\db2(), $sql_total_cajas_tipo2);
    $total_cajas_tipo2 = mysqli_fetch_array($query_total_cajas_tipo2, MYSQLI_ASSOC)['Cantidad'];
*/
    //$total_cajas = $total_cajas_tipo1 + $total_cajas_tipo2;
    $total_cajas = $datos_Header_ncajas["n_cajas"];

    //muestra clave caja
    //$tipo_caja = "t.clave as tipo_caja,";

    //muestra clave producto
    $tipo_caja = "(SELECT cve_articulo FROM c_articulo WHERE tipo_caja = caja.cve_tipocaja LIMIT 1) AS tipo_caja,";
    $sqlBody = "
        SELECT
            caja.fol_folio as folio,
            caja.NCaja as no_partida,
            cm.Cve_articulo,
            $tipo_caja
            t.descripcion descripcion,
            caja.Guia as guia, 
            '' as ntarima,
            TRUNCATE(
                (CASE 
                    WHEN caja.cve_tipocaja = 1 THEN
                    (
                        SELECT
                            IFNULL(ROUND(SUM(td_cajamixta.Cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
                        FROM td_cajamixta
                            LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
                    ) 
                END),4) AS volumen,
            (SELECT
                            IFNULL(ROUND(SUM(td_cajamixta.Cantidad * a.peso),3), 0) AS volumentotal
                        FROM td_cajamixta
                            LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix)  as peso, 
            (select DISTINCT RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio LIMIT 1) as cliente,
            (select DISTINCT Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente
        FROM th_cajamixta caja
            LEFT JOIN td_cajamixta cm ON cm.Cve_CajaMix = caja.Cve_CajaMix
            LEFT JOIN c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT DISTINCT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND cm.Cve_articulo IS NOT NULL
        GROUP BY guia, Cve_articulo

        UNION

        SELECT
                    tt.Fol_Folio AS folio,
                    0 AS no_partida,
                    tt.cve_articulo,
                    ch.CveLP AS tipo_caja,
                    'Pallet' AS descripcion,
                    '' AS guia, 
                    tt.ntarima,
                    TRUNCATE(IFNULL(ROUND(SUM(tt.cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0),4) AS volumen,
                    IFNULL(ROUND(SUM(tt.cantidad * a.peso),3), 0)  AS peso, 
                    (SELECT DISTINCT RazonSocial FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cliente,
                    (SELECT DISTINCT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cve_cliente
                FROM t_tarima tt
                LEFT JOIN c_articulo a ON a.cve_articulo = tt.cve_articulo
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                WHERE tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.cve_articulo IS NOT NULL AND tt.Ban_Embarcado = 'S'
            GROUP BY ntarima

    ";
    
    $queryBody = mysqli_query(\db2(), $sqlBody);

    $rows = array();
    while(($rowx = mysqli_fetch_array($queryBody, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        
    }
    $datos_Body = $rows;
/*
    $sqlTotal = "
        SELECT
            COALESCE(COUNT(DISTINCT(caja.fol_folio)),0) as pedidos,
            COALESCE(COUNT(DISTINCT(caja.Guia)),0) as guia
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio});
    ";
    
    $queryTotal = mysqli_query(\db2(), $sqlTotal);
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryTotal, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_Total = $rowx;
    }
*/
    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //$db->set_charset('utf8');

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query(\db2(), $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error(\db2()) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset(\db2() , $charset);


    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      $url = $_SERVER['DOCUMENT_ROOT']."/";
      $this->companyLogo = $url.$data->logo;
      $query->free_result();
      $db->close();
    }

     $sql_conf = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf='tituloguiaembarque'";
     $query_conf = mysqli_query(\db2(), $sql_conf);
     $titulo_reporte = mysqli_fetch_array($query_conf, MYSQLI_ASSOC)["Valor"];

     $sql_conf = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf='pieguiaembarque'";
     $query_conf = mysqli_query(\db2(), $sql_conf);
     $pie_pagina = mysqli_fetch_array($query_conf, MYSQLI_ASSOC)["Valor"];

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle($titulo_reporte);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $filename = "{$titulo_reporte} #{$folio}.pdf";
    $reporte = "{$titulo_reporte} #{$folio}";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    ob_start();
    ?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                      </tr>
                        <tr>
                            <td colspan="4" style=" text-align: right;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName?>" height="100px">
                            </td>
                            <td class="mt-6" colspan="20" style="white-space:nowrap; text-align: right;">
                                <span style="text-align: center; font-size:8px"><?php echo utf8_decode($reporte); ?></span><br>
                                <?php echo date('d-m-Y');?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style=" text-align: right;">
                            </td>
                            <td class="mt-6" colspan="16" style="white-space:nowrap; text-align: center;">
                                <h1 style="font-size:8px;"><?php echo $this->companyName?></h1>
                            </td>
                            <td colspan="4"  style="text-align: right;">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        if(strpos($_SERVER['HTTP_HOST'], 'dev') !== false || strpos($_SERVER['HTTP_HOST'], 'avavex') !== false)
                        {
                                $sql_folios = "
                                    SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio};
                                ";
                                $query_folios = mysqli_query(\db2(), $sql_folios);

                                $cliente_folio = "";
                                $origen_folio  = $datos_Header["origen"];
                                $destino_folio = "";

                                while(($row_folios = mysqli_fetch_array($query_folios, MYSQLI_ASSOC))) 
                                {
                                    $folio_tabla = $row_folios["Fol_folio"];
                                    $sql_tabla = "
                                    SELECT DISTINCT th.Cve_clte, c.RazonSocial, c.CalleNumero, c.Ciudad as Ciudad_Cliente, 
                                                    c.CodigoPostal, c.Colonia, c.RFC,
                                                    th.destinatario, d.ciudad as Ciudad_Destinatario, d.colonia, d.direccion, 
                                                    d.estado, d.razonsocial, d.postal,d.telefono
                                    FROM th_pedido th 
                                    LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
                                    LEFT JOIN c_destinatarios d ON d.id_destinatario = th.destinatario
                                    WHERE th.Fol_folio = '{$folio_tabla}' 
                                    ";
                                    $query_tabla = mysqli_query(\db2(), $sql_tabla);
                                    $row_tabla = mysqli_fetch_array($query_tabla, MYSQLI_ASSOC);

                                    $clientef  = $row_tabla["RazonSocial"];
                                    $rfc_c     = $row_tabla["RFC"];
                                    $dom_cf    = $row_tabla["CalleNumero"];
                                    $ciudad_cf = $row_tabla["Ciudad_Cliente"];

                                    $cliente_folio .= "Cliente: {$clientef} **********
                                                       RFC: {$rfc_c} **********
                                                       Domicilio: {$dom_cf} **********
                                                       Ciudad: {$ciudad_cf}
                                                    ";

                                    $destinatariof = $row_tabla["razonsocial"];
                                    //$rfc_d         = $row_tabla[""];
                                    $dom_df        = $row_tabla["direccion"];
                                    $ciudad_df     = $row_tabla["Ciudad_Destinatario"];
                                    //$entrega_d     = $row_tabla[""];

                                    $destino_folio .= "Destinatario: {$destinatariof} **********
                                                       RFC: {$rfc_c} **********
                                                       Domicilio: {$dom_df} **********
                                                       Ciudad: {$ciudad_df} **********
                                                       Se Entregará En: 
                                                       ";
                                }
                        ?>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Cliente</td>
                            <td colspan="12" border='0' style="white-space:nowrap; line-height: 7px; text-align: left; font-size: 4px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">  <?php 
                                                        $lineas = explode("**********", $cliente_folio);
                                                        echo "<br>";
                                                        echo "  ".utf8_encode($lineas[0])."<br>";
                                                        echo " ".utf8_encode($lineas[1])."<br>";
                                                        echo " ".utf8_encode($lineas[2])."<br>";
                                                        echo " ".utf8_encode($lineas[3])."<br>";
                            ?></td>
                            <td colspan="12" style="white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Origen </td>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Destino </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;"><?php echo nl2br(utf8_decode(utf8_encode($origen_folio))); ?></td>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">  <?php 
                                                        $lineas = explode("**********", $destino_folio);
                                                        echo "<br>";
                                                        echo "  ".utf8_encode($lineas[0])."<br>";
                                                        echo " ".utf8_encode($lineas[1])."<br>";
                                                        echo " ".utf8_encode($lineas[2])."<br>";
                                                        echo " ".utf8_encode($lineas[3])."<br>";
                                                        echo " ".utf8_decode(utf8_encode($lineas[4])).utf8_encode($ciudad_df)."<br>";
                            ?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        }
                        ?>

                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Área de Embarque </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Usuario </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($datos_header1["ubicacion"])?></td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($datos_header1["usuario"])?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Folio </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Embarque </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Entrega </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Destino </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Comentarios </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Chofer </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Transporte </td>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Status </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Peso </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Volumen </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Cajas </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Piezas </td>
                        </tr>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["id"]);?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["fecha_embarque"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["fecha_entrega"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["destino"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["comentarios"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["chofer"]);?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["transporte"]);?></td>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["status"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["peso"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["volumen"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php /*if($total_cajas == 0) echo mysqli_num_rows($queryBody); else*/ echo $total_cajas;?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["total_piezas"]);?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Pedido</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Partida</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Clave</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Tipo caja</td>
                            <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Guia</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Volumen</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Peso</td>
                        </tr>
                        <?php 
                        $folios_exist = array(); $pedidos = 0; $guias_exist = array(); $num_guias = 0;
                        $n_partida = 0;

                        foreach($datos_Body as $rows){ 
                                if($rows["no_partida"] == 0)
                                    $n_partida++;
                                else
                                    $n_partida = $rows["no_partida"];
                            ?>
                            <tr>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["folio"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($n_partida); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["tipo_caja"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["descripcion"])?></td>
                                <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["guia"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["volumen"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["peso"]); ?></td>
                            </tr>
                            <?php 
                                if(!in_array($rows["folio"], $folios_exist)) 
                                {
                                    $pedidos++;
                                    array_push($folios_exist, $rows["folio"]);
                                }

                                if(!in_array($rows["guia"], $guias_exist) && $rows["guia"]) 
                                {
                                    $num_guias++;
                                    array_push($guias_exist, $rows["guia"]);
                                }
                            ?>
                        <?php } ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Pedidos </td>  
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Guias </td>    
                        </tr>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($pedidos); //$datos_Total["pedidos"]; ?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $num_guias; ?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        if($pie_pagina)
                        {
                        ?>
                            <tr>
                                <td colspan="24" style="line-height: 3px; font-size: 3px; text-align: justify;"><?php echo utf8_encode(nl2br($pie_pagina)); ?></td>
                            </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                            <tr>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Seguro de la carga:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Flete pagadero en:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Número de originales:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Lugar y fecha de expedición</td>
                            </tr>

                            <tr>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">      <?php echo utf8_decode($datos_Header["seguro"]); ?></td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">      <?php echo utf8_decode($datos_Header["flete"]); ?></td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: center;">1</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">  <?php echo date('d-m-Y'); ?></td>
                            </tr>

                            <tr>
                                <td colspan="6" style="">&nbsp;</td>
                                <td colspan="6" style="">&nbsp;</td>
                                <td colspan="6" style="line-height: 8px; font-size: 4px; text-align: left;">REALIZÓ:</td>
                                <td colspan="6" style="line-height: 8px; font-size: 4px; text-align: left;"><?php echo utf8_decode($datos_header1["usuario"]); ?></td>
                            </tr>

                        <?php  
                        }
                        ?>

                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
    <?php
    $desProducto = ob_get_clean();
    $pdf->AddPage();
    $style = array(
          'position'     => '',
          'align'        => 'C',
          'stretch'      => false,
          'fitwidth'     => false,
          'cellfitalign' => '',
          'border'       => false,
          'hpadding'     => 'auto',
          'vpadding'     => 'auto',
          'fgcolor'      => array(0, 0, 0),
          'bgcolor'      => false,
          'text'         => true,
          'font'         => 'helvetica',
          'fontsize'     => 6,
          'stretchtext'  => 6
      );
      $pdf->SetAutoPageBreak(TRUE, 5);
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
      $pdf->setMargins(0, 5, 0, 0);
      $pdf->SetXY(5, 5);
      $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
      $pdf->WriteHTML($desProducto, true, false, true, '');
      ob_end_clean();
      $pdf->Output($filename, 'I');
    
        
   
  }

  public function getDataPDFPrecios($folio, $cia, $folio_pedidos)
  {
    $sqlHeader1 = "
        SELECT  
            COALESCE(e.descripcion,'--') as ubicacion,
            COALESCE(u.nombre_completo,'--')as usuario
        FROM th_ordenembarque o        
            LEFT join c_usuario u on u.cve_usuario = o.cve_usuario
            left join t_ubicacionembarque e on e.ID_Embarque = o.t_ubicacionembarque_id
        WHERE o.ID_OEmbarque = {$folio};
    ";
    $queryHeader1 = mysqli_query(\db2(), $sqlHeader1);
    
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryHeader1, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_header1 = $rowx;
    }

    $sqlHeader = "
        SELECT  
            o.ID_OEmbarque AS id,
            COALESCE(DATE_FORMAT(o.fecha, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(o.destino, '--') AS destino,
            COALESCE(o.comentarios, '--') AS comentarios,
            '--' AS chofer,
            COALESCE(t.Nombre,'--') AS transporte,
            COALESCE(o.status, '--') AS status,
            TRUNCATE((SELECT (COALESCE(SUM(c_articulo.peso*td_surtidopiezas.Cantidad),0)) FROM c_articulo INNER JOIN td_surtidopiezas ON td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo WHERE td_surtidopiezas.fol_folio IN ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS peso,
            TRUNCATE((SELECT COALESCE(SUM(((alto/1000) * (ancho/1000) * (fondo/1000))*td_surtidopiezas.Cantidad), 0) FROM c_articulo INNER JOIN td_surtidopiezas ON td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo WHERE td_surtidopiezas.fol_folio IN ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS volumen,
            #(SELECT COALESCE(SUM(1), 0) FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)) AS total_cajas,
            IFNULL(o.seguro, '') AS seguro,
            IFNULL(o.flete, '') AS flete,
            IFNULL(o.origen, '') AS origen,
            TRUNCATE((SELECT COALESCE(SUM(Cantidad), 0) FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),0) AS total_piezas
        FROM th_ordenembarque o        
            LEFT JOIN t_transporte t ON t.id = o.ID_Transporte
        WHERE o.ID_OEmbarque = {$folio};
    ";
    
    $queryHeader = mysqli_query(\db2(), $sqlHeader);
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryHeader, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_Header = $rowx;
    }
  
    $sql_total_cajas_tipo1 = "
        SELECT IF(art.num_multiplo>0, IFNULL(TRUNCATE(SUM(td.Cantidad)/art.num_multiplo,0), 0), COALESCE(SUM(1), 0)) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('$folio_pedidos') AND art.tipo_caja = th.cve_tipocaja";
    $query_total_cajas_tipo1 = mysqli_query(\db2(), $sql_total_cajas_tipo1);
    $total_cajas_tipo1 = mysqli_fetch_array($query_total_cajas_tipo1, MYSQLI_ASSOC)['Cantidad'];


    $sql_total_cajas_tipo2 = "
        SELECT COALESCE(SUM(1), 0) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('$folio_pedidos') AND art.tipo_caja != th.cve_tipocaja";
    $query_total_cajas_tipo2 = mysqli_query(\db2(), $sql_total_cajas_tipo2);
    $total_cajas_tipo2 = mysqli_fetch_array($query_total_cajas_tipo2, MYSQLI_ASSOC)['Cantidad'];

    $total_cajas = $total_cajas_tipo1 + $total_cajas_tipo2;


    //muestra clave caja
    //$tipo_caja = "t.clave as tipo_caja,";

    //muestra clave producto
    //$tipo_caja = "(SELECT cve_articulo FROM c_articulo WHERE tipo_caja = caja.cve_tipocaja LIMIT 1) AS tipo_caja,";
    $sqlBody = "
        SELECT
            caja.fol_folio as folio,
            caja.NCaja as no_partida,
            cm.Cve_articulo,
            (SELECT cve_articulo FROM c_articulo WHERE tipo_caja = caja.cve_tipocaja LIMIT 1) AS tipo_caja,
            t.descripcion descripcion,
            caja.Guia as guia, 
            '' as ntarima,
            TRUNCATE(
                (CASE 
                    WHEN caja.cve_tipocaja = 1 THEN
                    (
                        SELECT
                            IFNULL(ROUND(SUM(td_cajamixta.Cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
                        FROM td_cajamixta
                            LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
                    ) 
                END),4) AS volumen,
            (SELECT
                            IFNULL(ROUND(SUM(td_cajamixta.Cantidad * a.peso),3), 0) AS volumentotal
                        FROM td_cajamixta
                            LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix)  as peso, 
            (select DISTINCT RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio LIMIT 1) as cliente,
            (select DISTINCT Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente
        FROM th_cajamixta caja
            LEFT JOIN td_cajamixta cm ON cm.Cve_CajaMix = caja.Cve_CajaMix
            LEFT JOIN c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT DISTINCT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND cm.Cve_articulo IS NOT NULL
        GROUP BY guia, Cve_articulo

        UNION

        SELECT
                    tt.Fol_Folio AS folio,
                    0 AS no_partida,
                    tt.cve_articulo,
                    ch.CveLP AS tipo_caja,
                    'Pallet' AS descripcion,
                    '' AS guia, 
                    tt.ntarima,
                    TRUNCATE(IFNULL(ROUND(SUM(tt.cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0),4) AS volumen,
                    IFNULL(ROUND(SUM(tt.cantidad * a.peso),3), 0)  AS peso, 
                    (SELECT DISTINCT RazonSocial FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cliente,
                    (SELECT DISTINCT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cve_cliente
                FROM t_tarima tt
                LEFT JOIN c_articulo a ON a.cve_articulo = tt.cve_articulo
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                WHERE tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.cve_articulo IS NOT NULL AND tt.Ban_Embarcado = 'S'
            GROUP BY ntarima

    ";
    
    $queryBody = mysqli_query(\db2(), $sqlBody);

    $rows = array();
    while(($rowx = mysqli_fetch_array($queryBody, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        
    }
    $datos_Body = $rows;
/*
    $sqlTotal = "
        SELECT
            COALESCE(COUNT(DISTINCT(caja.fol_folio)),0) as pedidos,
            COALESCE(COUNT(DISTINCT(caja.Guia)),0) as guia
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio});
    ";
    
    $queryTotal = mysqli_query(\db2(), $sqlTotal);
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryTotal, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        $datos_Total = $rowx;
    }
*/
    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //$db->set_charset('utf8');

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query(\db2(), $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error(\db2()) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset(\db2() , $charset);


    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      $url = $_SERVER['DOCUMENT_ROOT']."/";
      $this->companyLogo = $url.$data->logo;
      $query->free_result();
      $db->close();
    }

     $sql_conf = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf='tituloguiaembarque'";
     $query_conf = mysqli_query(\db2(), $sql_conf);
     $titulo_reporte = mysqli_fetch_array($query_conf, MYSQLI_ASSOC)["Valor"];

     $sql_conf = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf='pieguiaembarque'";
     $query_conf = mysqli_query(\db2(), $sql_conf);
     $pie_pagina = mysqli_fetch_array($query_conf, MYSQLI_ASSOC)["Valor"];

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle($titulo_reporte);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $filename = "{$titulo_reporte} #{$folio}.pdf";
    $reporte = "{$titulo_reporte} #{$folio}";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    ob_start();
    ?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                      </tr>
                        <tr>
                            <td colspan="4" style=" text-align: right;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName?>" height="100px">
                            </td>
                            <td class="mt-6" colspan="20" style="white-space:nowrap; text-align: right;">
                                <span style="text-align: center; font-size:8px"><?php echo utf8_decode($reporte); ?></span><br>
                                <?php echo date('d-m-Y');?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style=" text-align: right;">
                            </td>
                            <td class="mt-6" colspan="16" style="white-space:nowrap; text-align: center;">
                                <h1 style="font-size:8px;"><?php echo $this->companyName?></h1>
                            </td>
                            <td colspan="4"  style="text-align: right;">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        if(strpos($_SERVER['HTTP_HOST'], 'dev') !== false || strpos($_SERVER['HTTP_HOST'], 'avavex') !== false)
                        {
                                $sql_folios = "
                                    SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio};
                                ";
                                $query_folios = mysqli_query(\db2(), $sql_folios);

                                $cliente_folio = "";
                                $origen_folio  = $datos_Header["origen"];
                                $destino_folio = "";

                                while(($row_folios = mysqli_fetch_array($query_folios, MYSQLI_ASSOC))) 
                                {
                                    $folio_tabla = $row_folios["Fol_folio"];
                                    $sql_tabla = "
                                    SELECT DISTINCT th.Cve_clte, c.RazonSocial, c.CalleNumero, c.Ciudad as Ciudad_Cliente, 
                                                    c.CodigoPostal, c.Colonia, c.RFC,
                                                    th.destinatario, d.ciudad as Ciudad_Destinatario, d.colonia, d.direccion, 
                                                    d.estado, d.razonsocial, d.postal,d.telefono
                                    FROM th_pedido th 
                                    LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
                                    LEFT JOIN c_destinatarios d ON d.id_destinatario = th.destinatario
                                    WHERE th.Fol_folio = '{$folio_tabla}' 
                                    ";
                                    $query_tabla = mysqli_query(\db2(), $sql_tabla);
                                    $row_tabla = mysqli_fetch_array($query_tabla, MYSQLI_ASSOC);

                                    $clientef  = $row_tabla["RazonSocial"];
                                    $rfc_c     = $row_tabla["RFC"];
                                    $dom_cf    = $row_tabla["CalleNumero"];
                                    $ciudad_cf = $row_tabla["Ciudad_Cliente"];

                                    $cliente_folio .= "Cliente: {$clientef} **********
                                                       RFC: {$rfc_c} **********
                                                       Domicilio: {$dom_cf} **********
                                                       Ciudad: {$ciudad_cf}
                                                    ";

                                    $destinatariof = $row_tabla["razonsocial"];
                                    //$rfc_d         = $row_tabla[""];
                                    $dom_df        = $row_tabla["direccion"];
                                    $ciudad_df     = $row_tabla["Ciudad_Destinatario"];
                                    //$entrega_d     = $row_tabla[""];

                                    $destino_folio .= "Destinatario: {$destinatariof} **********
                                                       RFC: {$rfc_c} **********
                                                       Domicilio: {$dom_df} **********
                                                       Ciudad: {$ciudad_df} **********
                                                       Se Entregará En: 
                                                       ";
                                }
                        ?>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Cliente</td>
                            <td colspan="12" border='0' style="white-space:nowrap; line-height: 7px; text-align: left; font-size: 4px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">  <?php 
                                                        $lineas = explode("**********", $cliente_folio);
                                                        echo "<br>";
                                                        echo "  ".utf8_encode($lineas[0])."<br>";
                                                        echo " ".utf8_encode($lineas[1])."<br>";
                                                        echo " ".utf8_encode($lineas[2])."<br>";
                                                        echo " ".utf8_encode($lineas[3])."<br>";
                            ?></td>
                            <td colspan="12" style="white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Origen </td>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: left; background-color: #DCDCDC; font-size: 4px;"> Destino </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;"><?php echo nl2br(utf8_decode(utf8_encode($origen_folio))); ?></td>
                            <td colspan="12" style="border: 1px solid black; white-space:nowrap; line-height: 5px; text-align: left; font-size: 4px;">  <?php 
                                                        $lineas = explode("**********", $destino_folio);
                                                        echo "<br>";
                                                        echo "  ".utf8_encode($lineas[0])."<br>";
                                                        echo " ".utf8_encode($lineas[1])."<br>";
                                                        echo " ".utf8_encode($lineas[2])."<br>";
                                                        echo " ".utf8_encode($lineas[3])."<br>";
                                                        echo " ".utf8_decode(utf8_encode($lineas[4])).utf8_encode($ciudad_df)."<br>";
                            ?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        }
                        ?>

                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Área de Embarque </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Usuario </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($datos_header1["ubicacion"])?></td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($datos_header1["usuario"])?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Folio </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Embarque </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Entrega </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Destino </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Comentarios </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Chofer </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Transporte </td>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Status </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Peso </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Volumen </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Cajas </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Piezas </td>
                        </tr>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["id"]);?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["fecha_embarque"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["fecha_entrega"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["destino"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["comentarios"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["chofer"]);?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["transporte"]);?></td>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["status"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["peso"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["volumen"]);?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php if($total_cajas == 0) echo mysqli_num_rows($queryBody); else echo $total_cajas;?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($datos_Header["total_piezas"]);?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Pedido</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Partida</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Clave</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Tipo caja</td>
                            <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Guia</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Volumen</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Peso</td>
                        </tr>
                        <?php 
                        $folios_exist = array(); $pedidos = 0; $guias_exist = array(); $num_guias = 0;
                        $n_partida = 0;

                        foreach($datos_Body as $rows){ 
                                if($rows["no_partida"] == 0)
                                    $n_partida++;
                                else
                                    $n_partida = $rows["no_partida"];
                            ?>
                            <tr>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["folio"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($n_partida); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["tipo_caja"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["descripcion"])?></td>
                                <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["guia"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["volumen"]); ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo ($rows["peso"]); ?></td>
                            </tr>
                            <?php 
                                if(!in_array($rows["folio"], $folios_exist)) 
                                {
                                    $pedidos++;
                                    array_push($folios_exist, $rows["folio"]);
                                }

                                if(!in_array($rows["guia"], $guias_exist) && $rows["guia"]) 
                                {
                                    $num_guias++;
                                    array_push($guias_exist, $rows["guia"]);
                                }
                            ?>
                        <?php } ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Pedidos </td>  
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Total Guias </td>    
                        </tr>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_decode($pedidos); //$datos_Total["pedidos"]; ?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $num_guias; ?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        if($pie_pagina)
                        {
                        ?>
                            <tr>
                                <td colspan="24" style="line-height: 3px; font-size: 3px; text-align: justify;"><?php echo utf8_encode(nl2br($pie_pagina)); ?></td>
                            </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                            <tr>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Seguro de la carga:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Flete pagadero en:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Número de originales:</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 7px; font-size: 4px; text-align: left;">   Lugar y fecha de expedición</td>
                            </tr>

                            <tr>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">      <?php echo utf8_decode($datos_Header["seguro"]); ?></td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">      <?php echo utf8_decode($datos_Header["flete"]); ?></td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: center;">1</td>
                                <td colspan="6" style="border: 0.2px solid black;line-height: 9px; font-size: 7px; text-align: left;">  <?php echo date('d-m-Y'); ?></td>
                            </tr>

                            <tr>
                                <td colspan="6" style="">&nbsp;</td>
                                <td colspan="6" style="">&nbsp;</td>
                                <td colspan="6" style="line-height: 8px; font-size: 4px; text-align: left;">REALIZÓ:</td>
                                <td colspan="6" style="line-height: 8px; font-size: 4px; text-align: left;"><?php echo utf8_decode($datos_header1["usuario"]); ?></td>
                            </tr>

                        <?php  
                        }
                        ?>

                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
    <?php
    $desProducto = ob_get_clean();
    $pdf->AddPage();
    $style = array(
          'position'     => '',
          'align'        => 'C',
          'stretch'      => false,
          'fitwidth'     => false,
          'cellfitalign' => '',
          'border'       => false,
          'hpadding'     => 'auto',
          'vpadding'     => 'auto',
          'fgcolor'      => array(0, 0, 0),
          'bgcolor'      => false,
          'text'         => true,
          'font'         => 'helvetica',
          'fontsize'     => 6,
          'stretchtext'  => 6
      );
      $pdf->SetAutoPageBreak(TRUE, 5);
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
      $pdf->setMargins(0, 5, 0, 0);
      $pdf->SetXY(5, 5);
      $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
      $pdf->WriteHTML($desProducto, true, false, true, '');
      ob_end_clean();
      $pdf->Output($filename, 'I');
    
        
   
  }

  public function getDataPDFEmpaque($folio, $cia, $folio_pedidos)
  {
    $sqlHeader = "
        SELECT DISTINCT tt.ntarima, tt.Fol_Folio, c.RazonSocial AS razonsocial, ch.CveLP
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_cliente c ON c.Cve_Clte = tp.Cve_clte
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.ntarima = ch.IDContenedor AND tt.Ban_Embarcado = 'S'
        ORDER BY tt.ntarima
        #LIMIT 2
        ;
    ";

    $queryHeader = mysqli_query(\db2(), $sqlHeader);
    $num_tarimas = mysqli_num_rows($queryHeader);

    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //$db->set_charset('utf8');

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query(\db2(), $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error(\db2()) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset(\db2() , $charset);

    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $data->logo = str_replace('/img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";
      $this->companyLogo = $url.$data->logo;

      //$query->free_result();
      //$db->close();
    }

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle('Reporte de Empaque');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $filename = "Reporte de Empaque #{$folio}.pdf";
    //$reporte = "Reporte de Empaque #{$folio}";img/compania/asl.png
    $reporte = "LICENSE PLATE";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    //ob_start();
    //for($ipdf = 0; $ipdf < $num_tarimas; $ipdf++)

        if($num_tarimas == 0)
        {
            ob_start();
        ?>
            <div class="row"></div>
            <table style="width:100%;">
                <tr style="width:100%;">
                    <td style="width: 10px;"></td>
                    <td style="width:340px;">
                        <table>
                            <tr>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>

                            <tr>
                                <td colspan="5" style=" text-align: center;">
                                    <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                </td>
                                <td colspan="19"></td>
                            </tr>
                            <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            <tr>
                                <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                    <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="22" style="text-align: center;"># DE TARIMA</td>
                                <td colspan="2" style=""><?php echo $ipdf; ?></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                                <tr>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                    <td colspan="5" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">LOTE</td>
                                    <td colspan="3" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CADUCIDAD</td>
                                    <td colspan="4" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANT. PIEZAS|KG</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANT. CAJAS</td>
                                </tr>

                                <tr>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="5" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="3" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="16"></td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                            </tr>
                           <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10px;"></td>
                </tr>
            </table>
        <?php 
            $desProducto = ob_get_clean(); 
            $pdf->AddPage(); 
            $pdf->SetAutoPageBreak(TRUE, 5); 
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
            $pdf->setMargins(0, 5, 0, 0); 
            $pdf->SetXY(5, 5); 
            $pdf->SetFont('helvetica', '', '8px', '', 'default', true); 
            $pdf->WriteHTML($desProducto, true, false, true, '');
            ob_end_clean();
            $pdf->Output($filename, 'I');
            return;
        }

    $ipdf = 0;
    
    while($row_pdf = mysqli_fetch_assoc($queryHeader))
    {
        ob_start();
        $ipdf++;
?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <td colspan="5" style=" text-align: center;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                
                            </td>
                            <td colspan="19"></td>
                        </tr>
                        <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo utf8_decode($row_pdf['razonsocial']); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $row_pdf['Fol_Folio']; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        //****************************************************************************************************
                        ?>
                        <tr>
                            <td colspan="24" style="text-align: center;"># DE TARIMA: <?php echo $row_pdf['CveLP']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                            <tr>
                                <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                <td colspan="5" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">LOTE</td>
                                <td colspan="3" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CADUCIDAD</td>
                                <td colspan="6" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANT. PIEZAS|KG</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANT. CAJAS</td>
                            </tr>
                        <?php 
                        //foreach($datos_Body as $rows){ 

                        $folio_pdf = $row_pdf['Fol_Folio'];
                        $ntarima = $row_pdf['ntarima'];
                        //$guia_pdf = $row_pdf['Guia'];

$sqlBody = "
    SELECT DISTINCT tt.Fol_Folio AS Fol_Folio, tt.cve_articulo AS codigo, ca.des_articulo AS des_articulo, 
            IF(ca.num_multiplo = 1, SUM(tt.cantidad), 0) AS cantidad,
            IF(ca.control_lotes = 'S', tt.lote, '') as lote,
            IF(ca.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
            #IF(IFNULL(ca.num_multiplo, 0) > 0, tt.Num_Empacados/ca.num_multiplo, 0) AS total_cajas
            IF(IFNULL(ca.num_multiplo, 1) = 1, 0, TRUNCATE(SUM(tt.cantidad)/ca.num_multiplo, 0)) AS total_cajas
            #IF(IFNULL(ca.num_multiplo, 0) > 0, COUNT(tt.ntarima), 0) AS total_cajas
    FROM t_tarima tt 
    LEFT JOIN c_articulo ca ON ca.cve_articulo = tt.cve_articulo 
    LEFT JOIN c_lotes l ON l.cve_articulo = tt.cve_articulo AND l.Lote = tt.lote
    WHERE tt.ntarima = $ntarima AND tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio})
    GROUP BY tt.cve_articulo;
";
                            
                        $queryBody = mysqli_query(\db2(), $sqlBody);
                        $cant_piezas = 0; $cant_cajas = 0;
                        while($row_td = mysqli_fetch_array($queryBody))
                        {
                        ?>
                            <tr>
                                <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['codigo']; ?></td>
                                <td colspan="5" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['lote']); ?></td>
                                <td colspan="3" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['caducidad']); ?></td>
                                <td colspan="6" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['des_articulo']); ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['cantidad']; ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['total_cajas']; ?></td>
                            </tr>
                        <?php 
                            $cant_piezas += $row_td['cantidad'];
                            $cant_cajas  += $row_td['total_cajas'];
                        } 
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="5" style="text-align:center; font-size: 6px; font-weight: bold;"></td>
                            <td colspan="3" style="text-align:center; font-size: 6px; font-weight: bold;"></td>
                            <td colspan="6" style="text-align:center; font-size: 6px; font-weight: bold;">TOTAL: </td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"><?php echo $cant_piezas; ?></td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"><?php echo $cant_cajas; ?></td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>


                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
        <?php
          $desProducto = ob_get_clean();
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
          ob_end_clean();
    }
    

//error_reporting(0); //Don't show errors in the PDF
//ob_clean(); //Clear any previous output
//ob_start(); //Start new output buffer

    $pdf->Output($filename, 'I');
    }


  public function getDataPDFAuditoria($folio, $cia, $folio_pedidos)
  {
    $sqlHeader = "
        SELECT DISTINCT tt.Fol_Folio, c.RazonSocial as razonsocial
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_cliente c ON c.Cve_Clte = tp.Cve_clte
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $queryHeader = mysqli_query(\db2(), $sqlHeader);

    $sql_tarimas = "
        SELECT COUNT(DISTINCT tt.ntarima) AS num_tarimas
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_destinatarios cd ON cd.id_destinatario = tp.destinatario
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $query_tarimas = mysqli_query(\db2(), $sql_tarimas);
    $num_tarimas = mysqli_fetch_assoc($query_tarimas)['num_tarimas'];

    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8');
    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $data->logo = str_replace('/img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";
      $this->companyLogo = $url.$data->logo;

      //$query->free_result();
      //$db->close();
    }

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle('Auditoria de Embarque');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $filename = "Auditoria de Embarque #{$folio}.pdf";
    //$reporte = "Reporte de Empaque #{$folio}";img/compania/asl.png
    $reporte = "AUDITORÍA DE EMBARQUE";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    //ob_start();
    //for($ipdf = 0; $ipdf < $num_tarimas; $ipdf++)

        if($num_tarimas == 0)
        {
            ob_start();
        ?>
            <div class="row"></div>
            <table style="width:100%;">
                <tr style="width:100%;">
                    <td style="width: 10px;"></td>
                    <td style="width:340px;">
                        <table>
                            <tr>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>

                            <tr>
                                <td colspan="5" style=" text-align: center;">
                                    <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                    
                                </td>
                                <td colspan="19"></td>
                            </tr>
                            <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            <tr>
                                <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                    <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="22" style="text-align: center;"># DE TARIMA</td>
                                <td colspan="2" style=""><?php echo $ipdf; ?></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                                <tr>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                    <td colspan="8" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">PIEZAS PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS EMBARCADAS</td>
                                </tr>

                                <tr>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="8" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="12"></td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                            </tr>
                           <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10px;"></td>
                </tr>
            </table>
        <?php 
            $desProducto = ob_get_clean(); 
            $pdf->AddPage(); 
            $pdf->SetAutoPageBreak(TRUE, 5); 
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
            $pdf->setMargins(0, 5, 0, 0); 
            $pdf->SetXY(5, 5); 
            $pdf->SetFont('helvetica', '', '8px', '', 'default', true); 
            $pdf->WriteHTML($desProducto, true, false, true, '');
            ob_end_clean();
            $pdf->Output($filename, 'I');
            return;
        }

    $ipdf = 0;
    
    while($row_pdf = mysqli_fetch_assoc($queryHeader))
    {
        ob_start();
        $ipdf++;
?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <td colspan="5" style=" text-align: center;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                
                            </td>
                            <td colspan="19"></td>
                        </tr>
                        <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo utf8_decode($row_pdf['razonsocial']); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $row_pdf['Fol_Folio']; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        //****************************************************************************************************
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                            <tr>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                <td colspan="3" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">LOTE</td>
                                <td colspan="3" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CADUCIDAD</td>
                                <td colspan="3" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">PIEZAS|KG PEDIDAS</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS PEDIDAS</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS EMBARCADAS</td>
                                <td colspan="3"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;"># TARIMA</td>
                            </tr>
                        <?php 
                        //foreach($datos_Body as $rows){ 

                        $folio_pdf = $row_pdf['Fol_Folio'];
                        //$guia_pdf = $row_pdf['Guia'];

$sqlBody = "
    SELECT DISTINCT tt.Fol_Folio AS Fol_Folio, tt.cve_articulo AS codigo, ca.des_articulo AS des_articulo, 
        IF(IFNULL(ca.num_multiplo, 1) = 1, SUM(tt.cantidad), 0) AS cantidad,
        ca.num_multiplo, ch.CveLP,
        IF(ca.control_lotes = 'S', tt.lote, '') as lote,
        IF(ca.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
        #IF(IFNULL(ca.num_multiplo, 0) > 0, TRUNCATE(ts.Cantidad/ca.num_multiplo, 0), 0) AS cajas_embarcadas,
        IF(IFNULL(ca.num_multiplo, 1) = 1, 0, TRUNCATE((SUM(tt.cantidad)/ca.num_multiplo), 0)) AS cajas_embarcadas,
        IF(IFNULL(ca.num_multiplo, 1) = 1, 0, TRUNCATE((SUM(tt.cantidad)/ca.num_multiplo), 0)) AS total_cajas
        #IF(IFNULL(ca.num_multiplo, 0) > 0, COUNT(tt.ntarima), 0) AS total_cajas
    FROM t_tarima tt 
    LEFT JOIN c_articulo ca ON ca.cve_articulo = tt.cve_articulo 
    LEFT JOIN th_subpedido s ON s.fol_folio = tt.Fol_Folio AND s.Sufijo = tt.Sufijo
    LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tt.cve_articulo AND ts.fol_folio = tt.Fol_Folio AND ts.Sufijo = tt.Sufijo AND s.status = 'T'
    LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
    LEFT JOIN c_destinatarios cd ON cd.id_destinatario = tp.destinatario
    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima AND tt.lote = ts.LOTE
    LEFT JOIN c_lotes l ON l.cve_articulo = tt.cve_articulo AND l.Lote = tt.lote
    WHERE tt.Fol_Folio = '$folio_pdf' AND tt.ntarima = ch.IDContenedor
    GROUP BY tt.cve_articulo, tt.ntarima;
";//, tt.lote
                            
                        $queryBody = mysqli_query(\db2(), $sqlBody);
                        $cant_piezas = 0; $cant_cajas = 0; $cajas_embarcadas = 0;
                        while($row_td = mysqli_fetch_array($queryBody))
                        {
                        ?>
                            <tr>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['codigo']; ?></td>
                                <td colspan="3" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['lote']); ?></td>
                                <td colspan="3" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['caducidad']); ?></td>
                                <td colspan="3" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['des_articulo']); ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['cantidad']; ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['total_cajas']; ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['cajas_embarcadas']; ?></td>
                                <td colspan="3"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['CveLP']; ?></td>
                            </tr>
                        <?php 
                            $cant_piezas += $row_td['cantidad'];
                            $cant_cajas  += $row_td['total_cajas'];
                            $cajas_embarcadas  += $row_td['cajas_embarcadas'];
                        } 
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td colspan="3" style="text-align:center; font-size: 6px; font-weight: bold;"></td>
                            <td colspan="3" style="text-align:center; font-size: 6px; font-weight: bold;"></td>
                            <td colspan="3" style="text-align:center; font-size: 6px; font-weight: bold;">TOTAL: </td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"><?php echo $cant_piezas; ?></td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"><?php echo $cant_cajas; ?></td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"><?php echo $cajas_embarcadas; ?></td>
                            <td colspan="3" style="text-align: center; font-size: 6px;"></td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>


                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
        <?php
          $desProducto = ob_get_clean();
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
          ob_end_clean();
    }
    

//error_reporting(0); //Don't show errors in the PDF
//ob_clean(); //Clear any previous output
//ob_start(); //Start new output buffer

    $pdf->Output($filename, 'I');
    }


  public function getDataPDFDiscrepancias($folio, $cia, $folio_pedidos)
  {
    $sqlHeader = "
        SELECT DISTINCT tt.Fol_Folio, c.RazonSocial AS razonsocial
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_cliente c ON c.Cve_Clte = tp.Cve_clte
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $queryHeader = mysqli_query(\db2(), $sqlHeader);


    $sql_tarimas = "
        SELECT COUNT(DISTINCT tt.ntarima) AS num_tarimas
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_destinatarios cd ON cd.id_destinatario = tp.destinatario
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}) AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $query_tarimas = mysqli_query(\db2(), $sql_tarimas);
    $num_tarimas = mysqli_fetch_assoc($query_tarimas)['num_tarimas'];

    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8');
    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $data->logo = str_replace('/img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";
      $this->companyLogo = $url.$data->logo;

      //$query->free_result();
      //$db->close();
    }

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle('Discrepancia de Embarque');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $filename = "Discrepancia de Embarque #{$folio}.pdf";
    //$reporte = "Reporte de Empaque #{$folio}";img/compania/asl.png
    $reporte = "DISCREPANCIA DE EMBARQUE";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    //ob_start();
    //for($ipdf = 0; $ipdf < $num_tarimas; $ipdf++)

        if($num_tarimas == 0)
        {
            ob_start();
        ?>
            <div class="row"></div>
            <table style="width:100%;">
                <tr style="width:100%;">
                    <td style="width: 10px;"></td>
                    <td style="width:340px;">
                        <table>
                            <tr>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>

                            <tr>
                                <td colspan="5" style=" text-align: center;">
                                    <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                    
                                </td>
                                <td colspan="19"></td>
                            </tr>
                            <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            <tr>
                                <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                    <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="22" style="text-align: center;"># DE TARIMA</td>
                                <td colspan="2" style=""><?php echo $ipdf; ?></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                                <tr>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                    <td colspan="8" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">PIEZAS|KG PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS EMBARCADAS</td>
                                </tr>

                                <tr>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="8" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="12"></td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                            </tr>
                           <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10px;"></td>
                </tr>
            </table>
        <?php 
            $desProducto = ob_get_clean(); 
            $pdf->AddPage(); 
            $pdf->SetAutoPageBreak(TRUE, 5); 
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
            $pdf->setMargins(0, 5, 0, 0); 
            $pdf->SetXY(5, 5); 
            $pdf->SetFont('helvetica', '', '8px', '', 'default', true); 
            $pdf->WriteHTML($desProducto, true, false, true, '');
            ob_end_clean();
            $pdf->Output($filename, 'I');
            return;
        }

    $ipdf = 0;
    
    while($row_pdf = mysqli_fetch_assoc($queryHeader))
    {
        ob_start();
        $ipdf++;
?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <td colspan="5" style=" text-align: center;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                
                            </td>
                            <td colspan="19"></td>
                        </tr>
                        <tr>
                                <td colspan="20" style="text-align: left;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo utf8_decode($row_pdf['razonsocial']); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $row_pdf['Fol_Folio']; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        //****************************************************************************************************
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                            <tr>
                                <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                <td colspan="12" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                <td colspan="2"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANTIDAD DE PIEZAS|KG</td>
                                <td colspan="6"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">COMENTARIOS</td>
                            </tr>
                        <?php 
                        //foreach($datos_Body as $rows){ 

                        $folio_pdf = $row_pdf['Fol_Folio'];
                        //$guia_pdf = $row_pdf['Guia'];

$sqlBody = "
    SELECT DISTINCT tt.Fol_Folio AS Fol_Folio, tt.cve_articulo AS codigo, ca.des_articulo AS des_articulo, 
        (SUM(tt.cantidad) - ts.Cantidad) AS Discrepancia,
        '' AS Comentarios
    FROM t_tarima tt 
    LEFT JOIN c_articulo ca ON ca.cve_articulo = tt.cve_articulo 
    LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tt.cve_articulo AND ts.fol_folio = tt.Fol_Folio AND ts.Sufijo = tt.Sufijo 
    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
    WHERE tt.Fol_Folio = '$folio_pdf' AND tt.ntarima = ch.IDContenedor AND tt.lote = ts.LOTE
    GROUP BY tt.cve_articulo, tt.lote;
";
                            
                        $queryBody = mysqli_query(\db2(), $sqlBody);
                        $discrepancia = 0;
                        while($row_td = mysqli_fetch_array($queryBody))
                        {
                        ?>
                            <tr>
                                <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['codigo']; ?></td>
                                <td colspan="12" style="font-size: 6px; border: 0.3px solid black;text-align: left;"><?php echo utf8_decode($row_td['des_articulo']); ?></td>
                                <td colspan="2"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['Discrepancia']; ?></td>
                                <td colspan="6"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['Comentarios']; ?></td>
                            </tr>
                        <?php 
                            $discrepancia += $row_td['Discrepancia'];
                        } 
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="12" style="text-align:center; font-size: 6px; font-weight: bold;">TOTAL: </td>
                            <td colspan="2" style="text-align: center; font-size: 6px;"><?php echo $discrepancia; ?></td>
                            <td colspan="6" style="text-align: center; font-size: 6px;"></td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>


                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
        <?php
          $desProducto = ob_get_clean();
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
          ob_end_clean();
    }
    

//error_reporting(0); //Don't show errors in the PDF
//ob_clean(); //Clear any previous output
//ob_start(); //Start new output buffer

    $pdf->Output($filename, 'I');
    }

} 
?>


