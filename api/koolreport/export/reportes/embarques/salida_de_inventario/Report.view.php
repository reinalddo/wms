<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
/*
    $category_amount = array(
        array("category"=>"Books","sale"=>32000,"cost"=>20000,"profit"=>12000),
        array("category"=>"Accessories","sale"=>43000,"cost"=>36000,"profit"=>7000),
        array("category"=>"Phones","sale"=>54000,"cost"=>39000,"profit"=>15000),
        array("category"=>"Movies","sale"=>23000,"cost"=>18000,"profit"=>5000),
        array("category"=>"Others","sale"=>12000,"cost"=>6000,"profit"=>6000),
    );

    $category_sale_month = array(
        array("category"=>"Books","January"=>32000,"February"=>20000,"March"=>12000),
        array("category"=>"Accessories","January"=>43000,"February"=>36000,"March"=>7000),
        array("category"=>"Phones","January"=>54000,"February"=>39000,"March"=>15000),
        array("category"=>"Others","January"=>12000,"February"=>6000,"March"=>6000),
    );
    */
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Entrega Programada</title>

<style>
    .bloque
    {
        font-size: 14pt; 
        font-weight: bolder !important;
        color: #2675B4 !important;
    }

    .texto_tabla
    {
        font-size: 14pt; 
        font-weight: bolder !important;
        color: #2675B4 !important;
    }

    .texto
    {
        font-size: 14pt; 
    }

</style>

</head>
<body style="margin: 30px 0;">
<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    //$folio = $_GET['id'];

    $folio = "";
    if(isset($_GET['id']))
      $folio = $_GET['id'];

    $folio_pedido = "";
    if(isset($_GET['folio_pedido']))
      $folio_pedido = $_GET['folio_pedido'];

    $count_pedidos = 1;
    if($folio_pedido == "")
    {
        $sql = "SELECT * FROM td_ordenembarque WHERE ID_OEmbarque = {$folio}";

        if (!($res_ord = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }
        $count_pedidos = mysqli_num_rows($res_ord);
    }
    else
    {
        $sql = "SELECT Fol_folio FROM td_ordenembarque WHERE Fol_folio = '{$folio_pedido}'";

        if (!($res_ord = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }
        $count_pedidos = mysqli_num_rows($res_ord);
    }


    //$count_pedidos = 2;

/*
    $sql = "SELECT * FROM th_ordenembarque WHERE ID_OEmbarque = {$folio}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);
*/
    //for($i_pedido = 0; $i_pedido < $count_pedidos; $i_pedido++)
    while($row_ord = mysqli_fetch_array($res_ord))
    {
        extract($row_ord);
?>
<div class="report-content" >
    <?php 
    $sql = "SELECT * FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);


    $sql_cte = "SELECT * FROM c_cliente WHERE Cve_clte = (SELECT Cve_clte FROM th_pedido WHERE Fol_Folio = '{$Fol_folio}')";

    if (!($res_cte = mysqli_query($conn, $sql_cte))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
        $row_cte = mysqli_fetch_array($res_cte);
    ?>
    <div class="row">
        <div class="col-xs-3">

            <img src="<?php echo $imagen; ?>" height='120' style="margin-left: 50px;">

        </div>

        <div class="col-xs-9 text-left" style="font-size:18pt; color: #2675B4 !important;">

            <b>SALIDA DE INVENTARIO - <span><?php echo strtoupper($des_cia); ?></span></b>
            <br><br>
            <div class="row text-center">
                <div class="col-xs-6" style="font-size: 10pt !important;">Consecutivo: <?php echo $Fol_folio; ?></div>
                <div class="col-xs-6" style="font-size: 10pt !important;">Fecha y Hora: <?php echo $fecha_envio; ?></div>
            </div>

        </div>

    </div>
    <br>
    <div class="row">
    <br>
        <div class="col-xs-12">
                <div class="text-center">
                  
                    <h1><span class="bloque">DATOS DEL CLIENTE</span></h1>
                    <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
                </div>
        <div class="row"  style="padding: 10px 100px;">
            <br>
            <div class="col-xs-3 text-right">
                <div><span class="bloque">NOMBRE</span></div>
                <div><span class="bloque">DIRECCIÓN</span></div>
            </div>

            <div class="col-xs-4">
                <div><span class="texto"><?php echo trim($row_cte["RazonSocial"]); ?></span></div>
                <div><span class="texto"><?php echo trim($row_cte["CalleNumero"]); ?></span></div>
            </div>

            <div class="col-xs-2 text-right">
                <div><span class="bloque">NIT</span></div>
                <div><span class="bloque">TELEFONO</span></div>
            </div>

            <div class="col-xs-3">
                <div><span class="texto"><?php echo trim($row_cte["RFC"]); ?></span></div>
                <div><span class="texto"><?php echo trim($row_cte["Telefono1"]); ?></span></div>
            </div>
        </div>

        </div>
    </div>

<br><br>
    <div class="row">
    <br>
        <div class="col-xs-12">
                <div class="text-center">

<?php 
        $sql_retiro = "SELECT IF(IFNULL(UPPER(tho.guia_transporte), '') = '', '-', UPPER(tho.guia_transporte)) AS guia_transporte, 
                              IF(IFNULL(th.Pick_Num, '') = '', '-', IFNULL(th.Pick_Num, '')) AS orden_compra, 
                              IF(IFNULL(th.Observaciones, '') = '', '-', th.Observaciones) as Observaciones, 
                              IF(tdo.status='T', 'ENVIADA', 'ENTREGADA') AS nota_entrega, IF(th.Fec_Pedido < th.Fec_Entrega, th.Fec_Pedido, th.Fec_Entrega) AS Fecha_Solicitud, 
                              th.Fol_folio AS orden_despacho, 
                              IFNULL(CONCAT(ct.nombre, ' ', ct.apellido), '-') AS contacto_atn, 
                              IF(IFNULL(d.direccion, '') = '', '-', CONCAT(d.razonsocial,'<br>',d.direccion )) as direccion, 
                              IF(IFNULL(CONCAT(d.contacto, d.telefono), '') = '', '-', CONCAT(d.contacto, '<br>', d.telefono)) AS contacto_so,
                              IF(IFNULL(p.Nombre, '') = '', '-', p.Nombre) AS transportadora, 
                              IF(IFNULL(tho.chofer, '') = '', '-', tho.chofer) AS conductor, 
                              IF(IFNULL(tho.id_chofer, '') = '', '-', tho.id_chofer) AS id_chofer, 
                              IF(IFNULL(p.RUT, '') = '', '-', p.RUT) AS nit, 
                              IF(IFNULL(t.Placas, '') = '', '-', t.Placas) as Placas, 
                              UPPER(u.nombre_completo) as usuario_entrega,
                              u.cve_usuario as cve_usuario_entrega,
                              CONCAT(IFNULL(c2.des_direcc, ''), ' Usuario: ',IFNULL(u.cve_usuario, ''), ' Fecha: ', tho.Fecha) AS footer

                        FROM td_ordenembarque tdo
                        LEFT JOIN th_ordenembarque tho ON tho.ID_OEmbarque = tdo.ID_OEmbarque
                        LEFT JOIN th_pedido th ON th.Fol_folio = tdo.Fol_folio
                        LEFT JOIN c_contactos ct ON ct.id = th.contacto_id
                        LEFT JOIN Rel_PedidoDest pd ON pd.Fol_Folio = tdo.Fol_folio
                        LEFT JOIN c_destinatarios d ON d.id_destinatario = pd.Id_Destinatario
                        LEFT JOIN t_transporte t ON t.id = tho.ID_Transporte
                        LEFT JOIN c_proveedores p ON p.ID_Proveedor = t.cve_cia 
                        LEFT JOIN c_compania c ON c.cve_cia = t.cve_cia
                        LEFT JOIN c_usuario u ON u.cve_usuario = tho.cve_usuario
                        LEFT JOIN c_compania c2 ON c2.cve_cia = u.cve_cia
                        WHERE tdo.Fol_folio = '$Fol_folio'";

        if (!($res_retiro = mysqli_query($conn, $sql_retiro))){
            echo "Falló la preparación(2): (" . mysqli_error($conn). ") ";
        }
        $row_retiro = mysqli_fetch_array($res_retiro);

?>
                    <h1><span class="bloque">DATOS GENERALES - RETIRO DE MERCANCIA</span></h1>
                    <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
                </div>
        <div class="row"  style="padding: 10px 100px;">
            <br>
            <div class="col-xs-3 text-right">
                <div><span class="bloque">GUIA DE TRANSPORTE</span></div>
                <div><span class="bloque">ORDEN DE COMPRA</span></div>
                <div><span class="bloque">NOTA DE ENTREGA</span></div>
                <div><span class="bloque">CONTACTO / TEL ATN</span></div>
                <div><span class="bloque">ORDEN DE DESPACHO </span></div>
            </div>

            <div class="col-xs-3">
                <div><span class="texto"><?php echo $row_retiro["guia_transporte"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["orden_compra"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["nota_entrega"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["contacto_atn"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["orden_despacho"]; ?></span></div>
            </div>

            <div class="col-xs-3 text-right">
                <div><span class="bloque">FECHA DE SOLICITUD</span></div>
                <div><span class="bloque">DESTINO/DIRECCION</span></div><br><br>
                <div><span class="bloque">CONTACTO / TEL SO</span></div>
            </div>

            <div class="col-xs-3">
                <div><span class="texto"><?php echo $row_retiro["Fecha_Solicitud"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["direccion"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["contacto_so"]; ?></span></div>
            </div>
        </div>

        </div>
    </div>


<br><br>
    <div class="row">
    <br>
        <div class="col-xs-12">
                <div class="text-center">
                  
                    <h1><span class="bloque">DATOS DEL TRANSPORTADOR</span></h1>
                    <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
                </div>
        <div class="row"  style="padding: 10px 100px;">
            <br>
            <div class="col-xs-3 text-right">
                <div><span class="bloque">TRANSPORTADORA</span></div>
                <div><span class="bloque">CONDUCTOR</span></div>
            </div>

            <div class="col-xs-3">
                <div><span class="texto"><?php echo $row_retiro["transportadora"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["conductor"]; ?></span></div>
            </div>

            <div class="col-xs-3 text-right">
                <div><span class="bloque">NIT</span></div>
                <div><span class="bloque">PLACA</span></div>
            </div>

            <div class="col-xs-3">
                <div><span class="texto"><?php echo $row_retiro["nit"]; ?></span></div>
                <div><span class="texto"><?php echo $row_retiro["Placas"]; ?></span></div>
            </div>
        </div>

        </div>
    </div>
<br><br>
<div style="padding: 10px 100px;">

<?php 
    $sql_items = "SELECT DISTINCT CONCAT(IFNULL(a.cve_articulo, ''), '<br>', IFNULL(a.cve_alt, '')) AS cve_articulo, a.des_articulo, um.des_umed as um, IF(IFNULL(ts.LOTE, '')='', 'N/A', ts.LOTE) as LOTE, ts.Cantidad AS cantidad
                  FROM td_ordenembarque tdo
                  LEFT JOIN td_pedido td ON td.Fol_folio = tdo.Fol_folio
                  LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                  LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                  LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = tdo.Fol_folio AND ts.Cve_articulo = td.Cve_articulo
                  INNER JOIN t_cardex k ON k.cve_articulo = ts.Cve_articulo AND k.cve_lote = ts.LOTE AND ts.fol_folio = k.destino AND k.id_TipoMovimiento = 8
                  WHERE tdo.Fol_folio = '{$Fol_folio}'";

    if (!($res_items = mysqli_query($conn, $sql_items))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res_items);
$n_pagina = 8;
$registros = 0;
$cant = 0;
$i = 1;
$primera_pagina = true;
while($count > $registros)
{
?>
<table class="table table-bordered">
  <thead>
    <tr>
      <th class="texto_tabla" scope="col">#</th>
      <th class="texto_tabla" scope="col">REFERENCIA/ALTERNA</th>
      <th class="texto_tabla" scope="col">DESCRIPCIÓN</th>
      <th class="texto_tabla" scope="col">UM</th>
      <th class="texto_tabla" scope="col">SERIALES|LOTES</th>
      <th class="texto_tabla" scope="col">UDS</th>
      <th class="texto_tabla" scope="col">NOTA|Kg</th>
    </tr>
  </thead>
  <tbody>
  <?php 


    $sql_items = "SELECT DISTINCT CONCAT(IFNULL(a.cve_articulo, ''), '<br>', IFNULL(a.cve_alt, '')) AS cve_articulo, a.des_articulo, um.des_umed as um, IF(IFNULL(ts.LOTE, '')='', 'N/A', ts.LOTE) as LOTE, ts.Cantidad AS cantidad
                  FROM td_ordenembarque tdo
                  LEFT JOIN td_pedido td ON td.Fol_folio = tdo.Fol_folio
                  LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                  LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                  LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = tdo.Fol_folio AND ts.Cve_articulo = td.Cve_articulo
                  INNER JOIN t_cardex k ON k.cve_articulo = ts.Cve_articulo AND k.cve_lote = ts.LOTE AND ts.fol_folio = k.destino AND k.id_TipoMovimiento = 8
                  WHERE tdo.Fol_folio = '{$Fol_folio}'
                  LIMIT $registros, $n_pagina";

    if (!($res_items = mysqli_query($conn, $sql_items))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    //$cant = 0;
    $registros += $n_pagina;
    if($primera_pagina == true){$n_pagina = $n_pagina*2;$primera_pagina = false;}
    //$i = 1;
    while($row_items = mysqli_fetch_array($res_items))
    {
        extract($row_items);
  ?>
    <tr>
      <th align="center"><?php echo $i; ?></th>
      <td align="center"><?php echo $cve_articulo; ?></td>
      <td align="center"><?php echo $des_articulo; ?></td>
      <td align="center"><?php echo $um; ?></td>
      <td align="center"><?php echo $LOTE; ?></td>
      <td align="center"><?php echo $cantidad; ?></td>
      <td align="center"><?php echo ''; ?></td>
    </tr>
    <?php 
    $i++;
    $cant += $cantidad;
    }
    ?>
  </tbody>
</table>
<div class="page-break"></div>
<?php 
$n_pagina = 16;
}
?>
</div>

<br><br>
    <div class="row">
    <br>
        <div class="col-xs-12">
                <div class="text-center">
                  
                    <h1><span class="bloque">INFORMACIÓN GENERAL</span></h1>
                    <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
                </div>
        <div class="row">
            <br>
            <div class="col-xs-3 text-right">
                <div><span class="bloque">TOTAL UNIDADES</span></div>
            </div>
            <div class="col-xs-9">
                <div><span class="texto"><?php echo $cant; ?></span></div>
            </div>
        </div>
        <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
        <div class="row">
            <br>
            <div class="col-xs-3 text-right">
                <div><span class="bloque">OBSERVACIONES</span></div>
            </div>

            <div class="col-xs-9">
                <div><span class="texto"><?php echo $row_retiro["Observaciones"]; ?></span></div>
            </div>
        </div>
        <hr style="margin: 0 50px !important; height: 0.3px !important; background-color: #2675B4 !important;">
        </div>
    </div>


<br><br><br><br><br><br><br><br><br><br><br><br>
    <div class="row">
        <div class="col-xs-1">&nbsp;</div>
        <div class="col-xs-5 text-left">
            <span class="bloque">ENTREGADO POR: </span>
            <br><br><br><br>
            <span class="bloque">___________________________________________________</span><br>
            <span class="bloque"><?php echo $row_retiro["usuario_entrega"]; ?></span><br>
            <span class="bloque">C.C: <?php echo $row_retiro["cve_usuario_entrega"]; ?></span><br>
        </div>

        <div class="col-xs-5 text-left">
            <span class="bloque">RECIBIDO POR: </span>
            <br><br><br><br>
            <span class="bloque">___________________________________________________</span><br>
            <span class="bloque"><?php echo $row_retiro["conductor"]; ?></span><br>
            <span class="bloque">C.C: <?php echo $row_retiro["id_chofer"]; ?></span><br>
        </div>
        <div class="col-xs-1">&nbsp;</div>

    </div>

    <div class="page-footer" style="text-align:center;">
        <hr style="border-top: 1px solid #2675B4;">
        <?php echo $row_retiro["footer"]; ?>
    </div>

</div>
<?php 
    if($count_pedidos > 1)
    {
    ?>
    <div class="page-break"></div>
    <?php 
    }

}
?>


</body>
</html>

