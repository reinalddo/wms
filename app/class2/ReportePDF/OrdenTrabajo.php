<?php

namespace ReportePDF;

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";

class OrdenTrabajo
{

  public function __construct()
  {
    
  }

  public function getDataExcel($folio)
    {
        $columnas = [
            utf8_decode('Clave Artículo'),
            utf8_decode('Descripción'),
            'Lote',
            'Caducidad',
            'Cantidad',
            'U. Medida'
        ];


        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT DISTINCT op.Cve_Articulo, a.des_articulo AS Descripcion, op.Cve_Lote, 
        IF(a.Caduca = 'S' AND L.Caducidad != '0000-00-00', DATE_FORMAT(L.Caducidad, '%d/%m/%Y'), '') AS Caducidad, op.Cantidad, u.des_umed AS Umedida 
        FROM td_ordenprod op 
        LEFT JOIN t_ordenprod orp ON orp.Folio_Pro = op.Folio_Pro
        LEFT JOIN c_lotes L ON L.cve_articulo = op.Cve_Articulo AND L.Lote = op.Cve_Lote
        LEFT JOIN c_articulo a ON a.cve_articulo = op.Cve_Articulo
        LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = a.cve_articulo AND art.Cve_ArtComponente = orp.Cve_Articulo
        LEFT JOIN c_unimed u ON u.cve_umed = art.cve_umed
        WHERE op.Folio_Pro = '{$folio}'";

            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

        //$data_oc = mysqli_fetch_assoc($res);
        $filename = "Orden-Frabricacion-".$folio.".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_oc as $row)
        while($row = mysqli_fetch_assoc($res))
        {
            echo $this->clear_column($row{'Cve_Articulo'}) . "\t";
            echo $this->clear_column($row{'Descripcion'}) . "\t";
            echo $this->clear_column($row{'Cve_Lote'}) . "\t";
            echo $this->clear_column($row{'Caducidad'}) . "\t";
            echo $this->clear_column($row{'Cantidad'}) . "\t";
            echo $this->clear_column($row{'Umedida'}) . "\t";
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


  public function getDataPDF($folio)
  {
/*
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
    $tipo_caja = "(SELECT cve_articulo FROM c_articulo WHERE tipo_caja = caja.cve_tipocaja) AS tipo_caja,";
    $sqlBody = "
        SELECT
            caja.fol_folio as folio,
            caja.NCaja as no_partida,
            cm.Cve_articulo,
            $tipo_caja
            t.descripcion descripcion,
            caja.Guia as guia, 
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
            (select RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio) as cliente,
            (select Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente
        FROM th_cajamixta caja
            LEFT JOIN td_cajamixta cm ON cm.Cve_CajaMix = caja.Cve_CajaMix
            LEFT JOIN c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$folio})
        GROUP BY guia, Cve_articulo;
    ";
    
    $queryBody = mysqli_query(\db2(), $sqlBody);
    $rows = array();
    while(($rowx = mysqli_fetch_array($queryBody, MYSQLI_ASSOC))) 
    {
        $rows[] = $rowx;
        
    }
    $datos_Body = $rows;

    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8');
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
*/

     $sqlHeader = "
     SELECT p.Nombre AS Proveedor, 
            DATE_FORMAT(top.FechaReg, '%d/%m/%Y') AS Fecha_Registro, 
            DATE_FORMAT(top.FechaReg, '%H:%m%p') AS Hora_Registro
FROM t_ordenprod top 
LEFT JOIN c_proveedores p ON p.ID_Proveedor = top.ID_Proveedor
WHERE top.Folio_Pro = '{$folio}' LIMIT 1";
    $queryHeader = mysqli_query(\db2(), $sqlHeader);
    $rowh = mysqli_fetch_array($queryHeader, MYSQLI_ASSOC);

    $Proveedor      = $rowh["Proveedor"];
    $Fecha_Registro = $rowh["Fecha_Registro"];
    $Hora_Registro  = $rowh["Hora_Registro"];

    $sqlDetalle = "
        SELECT DISTINCT op.Cve_Articulo, a.des_articulo AS Descripcion, op.Cve_Lote, 
        IF(a.Caduca = 'S' AND L.Caducidad != '0000-00-00', DATE_FORMAT(L.Caducidad, '%d/%m/%Y'), '') AS Caducidad, op.Cantidad, u.des_umed AS Umedida 
        FROM td_ordenprod op 
        LEFT JOIN t_ordenprod orp ON orp.Folio_Pro = op.Folio_Pro
        LEFT JOIN c_lotes L ON L.cve_articulo = op.Cve_Articulo AND L.Lote = op.Cve_Lote
        LEFT JOIN c_articulo a ON a.cve_articulo = op.Cve_Articulo
        LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = a.cve_articulo AND art.Cve_ArtComponente = orp.Cve_Articulo
        LEFT JOIN c_unimed u ON u.cve_umed = art.cve_umed
        WHERE op.Folio_Pro = '{$folio}';
    ";
    $queryDetalle = mysqli_query(\db2(), $sqlDetalle);

    $titulo_reporte = "Orden de Fabricacion";
    $tit_reporte = "Orden de Fabricación";
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , 'LETTER', true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle($tit_reporte);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $filename = "{$titulo_reporte} #{$folio}.pdf";
    $reporte = utf8_encode("{$tit_reporte} {$folio}");
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    ob_start();
    ?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 60px;"></td>
                <td style="width:640px;">
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
                            <td colspan="24" style="text-align: center;white-space:nowrap;">
                                <span style="font-size:18px; text-decoration: underline;"><?php echo $Proveedor; ?></span>
                                <?php 
                                //echo date('d-m-Y');
                                //<span style="text-align: center; font-size:18px"><?php echo utf8_decode($reporte);? > </span>
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="24"  style="text-align: right;font-size:12px;">Fecha: <?php echo $Fecha_Registro; ?></td>
                        </tr>
                        <tr>
                            <td colspan="24"  style="text-align: right;font-size:12px;">Hora: <?php echo $Hora_Registro; ?></td>
                        </tr>

                        <tr>
                            <td colspan="4" style=" text-align: right;">
                            </td>
                            <td class="mt-6" colspan="16" style="white-space:nowrap; text-align: center;">
                                <h1 style="font-size:18px;"><?php echo utf8_decode($reporte);?></h1>
                            </td>
                            <td colspan="4"  style="text-align: right;">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        /*
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
                        */
                        ?>

                        <?php 
                        /*
                        ?>
                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Área de Embarque </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Usuario </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">------------</td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">------------</td>
                        </tr>
                        <?php 
                        */
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;"># </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">Clave Artículo </td>
                            <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">Descripción </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">Lote </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">Caducidad </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">Cantidad </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; background-color: #DCDCDC; font-size: 12px;">U. Medida </td>
                        </tr>
                        <?php 
                        $i = 0;
                        while(($rowx = mysqli_fetch_array($queryDetalle, MYSQLI_ASSOC))) 
                        {
                            $i++;
                        ?>
                        <tr>
                            <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; font-size: 10px;"><?php echo $i; ?></td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: left; 
                            font-size: 10px;">&nbsp;&nbsp;<?php echo $rowx["Cve_Articulo"]; ?></td>
                            <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: left; 
                            font-size: 10px;">&nbsp;&nbsp;<?php echo $rowx["Descripcion"]; ?></td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: left; 
                            font-size: 10px;">&nbsp;&nbsp;<?php echo $rowx["Cve_Lote"]; ?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: center; font-size: 10px;"><?php echo $rowx["Caducidad"]; ?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: right; font-size: 10px;"><?php echo $rowx["Cantidad"]; ?>&nbsp;&nbsp;</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 17px; text-align: left; 
                            font-size: 10px;">&nbsp;&nbsp;<?php echo $rowx["Umedida"]; ?></td>
                        </tr>
                        <?php  
                         }
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        /*
                        ?>
                        <tr>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Pedido</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Partida</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Clave</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Tipo caja</td>
                            <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Guia</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Volumen</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Peso</td>
                        </tr>
                        <?php 
                        $folios_exist = array(); $pedidos = 0; $guias_exist = array(); $num_guias = 0;
                        //foreach($datos_Body as $rows){ ?>
                            <tr>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="6" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">....................</td>
                            </tr>
                        <?php 
                        */
                        //} 
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        /*
                        ?>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Total Pedidos </td>  
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 14px;">Total Guias </td>    
                        </tr>
                        <tr>
                            <td colspan="20"></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">___________________</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 14px;">___________________</td>
                        </tr>
                        <?php 
                        */
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>

                        <?php 
                        /*
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
                        */
                        ?>

                    </table>
                </td>
                <td style="width: 60px;"></td>
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
          'fontsize'     => 16,
          'stretchtext'  => 6
      );
      $pdf->SetAutoPageBreak(TRUE, 5);
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
      $pdf->setMargins(0, 5, 0, 0);
      $pdf->SetXY(5, 5);
      $pdf->SetFont('helvetica', '', '18px', '', 'default', true);
      $pdf->WriteHTML($desProducto, true, false, true, '');
      ob_end_clean();
      $pdf->Output($filename, 'I');
    
        
   
  }
}
?>


