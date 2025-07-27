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
<title>Reporte de <?php if(isset($_GET['tipomovimiento'])){echo "Movimientos del Almacén";}else echo "Kardex"; ?></title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//if(!isset($_GET['reporte']))
//{
    $kardex_consolidado = 0;
    if(isset($_GET['kardex_consolidado']))
      $kardex_consolidado = $_GET['kardex_consolidado'];

    $cve_cia = $_GET['cve_cia'];
    $fecha_inicio= $_GET['fechaI'];
    $fecha_final= $_GET['fechaF'];
    $id_proveedor = $_GET['cve_proveedor'];
    $OCBusq = $_GET['OCBusq'];


    $sql = "SELECT imagen, DATE_FORMAT(CURDATE(), '%d-%m-%Y') as fecha_hoy FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">Reporte <?php if(isset($_GET['tipomovimiento'])){echo "Movimientos del Almacén";}else echo "Kardex"; ?> <?php 

                    if($fecha_inicio && $fecha_final) echo " | Desde: ".$fecha_inicio." Hasta: ".$fecha_final;
                    else if($fecha_inicio) echo " | Desde: ".$fecha_inicio." Hasta: ".$fecha_hoy;
                    else if($fecha_final) echo " | Hasta el día: ".$fecha_final;
                     ?></span></h1>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <?php 
    if($kardex_consolidado == 0)
    {
    ?>
    <tr>
      <th>Fecha</th>
      <th>Clave</th>
      <?php /* ?><th>Articulo</th><?php */ ?>
      <th>Lote|Serie<br>Caducidad</th>
      <?php /* ?><th width="300">Caducidad</th><?php */ ?>
      <th>Pallet<br>LP</th>
      <?php /* ?><th width="300">(LP)</th><?php */ ?>
      <th>Movimiento</th>
      <th>Origen</th>
      <th>Destino</th>
      <th>Stock Inicial</th>
      <th>Stock Final</th>
      <th>Ajuste</th>
      <th>Usuario</th>
    </tr>
    <?php 
    }
    else
    {
    ?>
    <tr>
      <th>Clave</th>
      <th>Descripción</th>
      <th>Movimiento</th>
      <th>Almacén Origen</th>
      <th>Almacén Destino</th>
      <th>Pallet/Contenedor</th>
      <th>QTY UNITS</th>
      <th>QTY CAJAS</th>
      <th>Usuario</th>
    </tr>
    <?php 
    }
    ?>
  </thead>
  <tbody>
  <?php 

    $almacen= $_GET['almacen'];
    $lote= $_GET['lote'];
    $cve_articulo= $_GET['cve_articulo'];

    $tipomovimiento = $_GET['tipomovimiento']; $SQLMovimientos = "";
    if(isset($_GET['tipomovimiento']) && !isset($_GET['cajas']))
    {
        $tipomovimiento= $_GET['tipomovimiento'];
        $SQLMovimientos = " AND k.id_TipoMovimiento IN (2, 12) ";
    }

    if($tipomovimiento)
    {
        $SQLMovimientos = " AND k.id_TipoMovimiento = $tipomovimiento ";
        if($tipomovimiento == 6 || $tipomovimiento == 12)
            $SQLMovimientos = " AND k.id_TipoMovimiento IN (6,12) ";
    }
    
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_GET['criterio'];

    $SQLCriterio = "";
    $SQLCriterioCajas = "";
    if($_criterio)
    {
        $SQLCriterio = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR uo.CodigoCSD LIKE '%".$_criterio."%' OR ud.CodigoCSD LIKE '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%' OR rd.desc_ubicacion LIKE '%".$_criterio."%' OR m.nombre LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%' OR rd.cve_ubicacion LIKE '%".$_criterio."%' OR k.origen LIKE '%".$_criterio."%' OR k.destino LIKE '%".$_criterio."%') ";

        $SQLCriterioCajas = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR u_or.CodigoCSD LIKE '%".$_criterio."%' OR u_dest.CodigoCSD LIKE '%".$_criterio."%' OR lp.clave_contenedor LIKE '%".$_criterio."%' OR lp.CveLP LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%') ";

    }

    $SQLOC = "";
    if($OCBusq)
        $SQLOC = " AND (oc.num_pedimento LIKE '%$OCBusq%' OR oc.Factura LIKE '%$OCBusq%') ";

    $SQLArticulo = "";
    if($cve_articulo)
    {
        $SQLArticulo = " AND k.cve_articulo = '".$cve_articulo."' ";
    }

    $SQLLote = "";
    if($lote)
    {
        $SQLLote = " AND k.cve_lote = '".$lote."' ";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $SQLFecha = "";
    if($fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha BETWEEN STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y') AND STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
        if($fecha_inicio == $fecha_final)
        $SQLFecha = " AND DATE_FORMAT(k.fecha, '%Y-%m-%d') = STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }
    else if(!$fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha <= STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if($fecha_inicio && !$fecha_final)
    {
        $SQLFecha = " AND k.fecha >= STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }

    if(!$SQLArticulo && !$SQLLote && !$SQLFecha && !$_criterio && !$OCBusq) //&& !$id_proveedor
    {
        //$SQLFecha = " AND DATE_FORMAT(k.fecha, '%d-%m-%Y') = DATE_FORMAT((SELECT MAX(fecha) FROM t_cardex), '%d-%m-%Y') ";
        $SQLFecha = " AND k.fecha >= (SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL) ";
    }

    $sqlSesionEmpresa = "";

    if($id_proveedor)
    {
        $sqlSesionEmpresa = "
        LEFT JOIN c_cliente c ON c.Cve_Clte IN (th_orig.Cve_clte, th_dest.Cve_clte)
        INNER JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor AND IFNULL(c.ID_Proveedor, 0) != 0 AND p.ID_Proveedor IN (ent_orig.Cve_Proveedor, ent_dest.Cve_Proveedor, c.ID_Proveedor) AND p.ID_Proveedor = {$id_proveedor}
        ";
    }

    $sql = "SELECT DISTINCT 
    #IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(SELECT COUNT(*) FROM ts_existenciacajas exc WHERE exc.idy_ubica = k.destino AND k.cve_articulo = exc.cve_articulo AND IFNULL(exc.cve_lote, '') = IFNULL(k.cve_lote, '') AND k.cve_almac = exc.cve_almac AND k.id_TipoMovimiento IN (2, 12, 6)), ')'), '') AS cajas, 
    COUNT(DISTINCT cj.id) AS cajas,
    GROUP_CONCAT(IFNULL(IFNULL(CONCAT(IFNULL(ent_ocompra.num_pedimento, ''), IF(IFNULL(ent_ocompra.Factura, '') != '', ' | ', ''), IFNULL(ent_ocompra.Factura, '')), ''), CONCAT(IFNULL(oc.num_pedimento, ''), IF(IFNULL(oc.Factura, '') != '', ' | ', ''), IFNULL(oc.Factura, ''))) SEPARATOR ' ; ') AS oc,
IFNULL(IF(k.origen LIKE 'TR2%', alm_orig.clave, tr_origen.clave), IFNULL(ent_alm_orig.clave,  al.clave)) AS Almacen_Origen,
IFNULL(IF(k.destino LIKE 'TR2%', alm_dest.clave, tr_destino.clave), IFNULL(ent_alm_dest.clave, al.clave)) AS Almacen_Destino,
k.cve_articulo AS id_articulo, a.des_articulo AS des_articulo, k.cve_lote AS cve_lote, 
                IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(k.cve_lote, '') != '', IFNULL(IF(DATE_FORMAT(lote.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d'),DATE_FORMAT(lote.Caducidad, '%d-%m-%Y'), DATE_FORMAT(lote.Caducidad, '%d-%m-%Y')),''), '') AS Caducidad, 
                DATE_FORMAT(k.fecha, '%d-%m-%Y | %H:%m:%i') AS fecha, 
                #IF(LEFT(k.origen, 4) != 'Inv_',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_', k.origen, 'RTM')), k.origen) AS origen, 
                #IF(k.id_TipoMovimiento NOT IN (20,1, 21, 22, 25, 26, 27) AND LEFT(k.origen, 4) != 'Inv_' AND k.origen != 'Inventario Inicial',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_' OR LEFT(k.origen, 2) = 'OT', k.origen, IF(k.id_TipoMovimiento IN (20,1, 21, 22, 25, 26, 27), k.origen, 'RTM'))), k.origen) AS origen,
                CASE 
                WHEN m.nombre = 'Salida' OR UPPER(m.nombre) LIKE '%TRASLADO%' OR m.nombre = 'Traspaso' THEN uo.CodigoCSD
                ELSE k.origen
                END as origen,
                IFNULL(ch.clave_contenedor,'') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(uo.CodigoCSD, '') AS bl, IFNULL(k.ajuste, '') as ajuste, IFNULL(k.stockinicial, '') as stockinicial, 
                #IFNULL(ud.CodigoCSD, IFNULL(rd.desc_ubicacion, k.destino)) AS destino, 
                IFNULL(ud.CodigoCSD, IFNULL(rd.desc_ubicacion, IF(k.id_TipoMovimiento = 8, CONCAT(k.destino, IF(IFNULL(th_dest.Pick_Num, '') = '', '', ' | '), IFNULL(th_dest.Pick_Num, '')),k.destino))) AS destino, 
                IF(m.nombre LIKE 'Salida%', CONCAT('<span style=\'color:red;\'>-',k.cantidad, '</span>'), k.cantidad) as cantidad,
                m.nombre AS movimiento, 
                k.cve_usuario, al.nombre AS almacen 
                FROM t_cardex k 
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                LEFT JOIN c_almacenp al ON al.id = k.Cve_Almac

                LEFT JOIN th_pedido th_orig ON th_orig.Fol_folio = k.origen
                LEFT JOIN th_pedido th_dest ON th_dest.Fol_folio = REPLACE(k.destino, '-1', '')
                LEFT JOIN c_almacenp alm_orig ON alm_orig.id = th_orig.cve_almac
                LEFT JOIN c_almacenp alm_dest ON alm_dest.id = th_dest.statusaurora
        
                LEFT JOIN th_entalmacen ent_orig ON ent_orig.Fol_Folio = k.origen
                LEFT JOIN th_entalmacen ent_dest ON ent_dest.Fol_Folio = k.destino
                LEFT JOIN c_almacenp ent_alm_orig ON ent_alm_orig.clave = ent_orig.cve_almac
                LEFT JOIN c_almacenp ent_alm_dest ON ent_alm_dest.clave = ent_dest.cve_almac

                LEFT JOIN t_MovCharolas mch ON k.id = mch.id_kardex #OR (k.origen = mch.Origen AND k.destino = mch.Destino AND k.id_TipoMovimiento = mch.Id_TipoMovimiento AND k.cve_usuario = mch.Cve_Usuario)
                LEFT JOIN c_charolas ch ON ch.IDContenedor = mch.ID_Contenedor 
                LEFT JOIN c_ubicacion uo ON uo.idy_ubica = k.origen 
                LEFT JOIN tubicacionesretencion ro ON ro.cve_ubicacion = k.origen 
                LEFT JOIN c_ubicacion ud ON ud.idy_ubica = k.destino 

                LEFT JOIN c_almacen tr_ori ON tr_ori.cve_almac = uo.cve_almac
                LEFT JOIN c_almacenp tr_origen ON tr_origen.id = tr_ori.cve_almacenp
                
                LEFT JOIN c_almacen tr_des ON tr_des.cve_almac = ud.cve_almac
                LEFT JOIN c_almacenp tr_destino ON tr_destino.id = tr_des.cve_almacenp

                LEFT JOIN tubicacionesretencion rd ON rd.cve_ubicacion = k.destino 
                LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
                LEFT JOIN c_lotes lote ON lote.cve_articulo = k.cve_articulo AND lote.Lote = k.cve_lote

                 {$sqlSesionEmpresa} 

                LEFT JOIN th_aduana oc ON ent_orig.id_ocompra = oc.num_pedimento 
                LEFT JOIN td_entalmacen ent_oc ON ent_oc.cve_articulo = k.cve_articulo AND ent_oc.cve_lote = k.cve_lote AND IFNULL(ent_oc.num_orden, 0) != 0
                LEFT JOIN th_aduana ent_ocompra ON ent_ocompra.num_pedimento = ent_oc.num_orden
                #LEFT JOIN ts_existenciacajas ec ON ec.idy_ubica = k.destino AND k.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(k.cve_lote, '')  AND k.cve_almac = ec.cve_almac AND k.id_TipoMovimiento IN (2, 12, 6)
                LEFT JOIN t_MovCharolas cj ON cj.id_kardex = k.id AND cj.EsCaja = 'S'
                #WHERE k.Cve_Almac = {$almacen} {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha}
                WHERE (k.Cve_Almac IN ({$almacen}, IF(tr_destino.id = tr_origen.id, {$almacen}, IF(tr_destino.id = {$almacen} OR tr_origen.id = {$almacen}, IF(tr_origen.id = {$almacen}, tr_destino.id, tr_origen.id), 0)))) {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha} {$SQLMovimientos}  {$SQLOC} 
                #GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, CveLP, movimiento
                #GROUP BY ext.ID_Contenedor
                GROUP BY k.id
                ORDER BY DATE(k.fecha) DESC, k.id DESC
            ";

    if($kardex_consolidado == 1)
    {
        $sql_0 = $sql;

/*        $sql = "SELECT * FROM (SELECT kar.id_articulo, kar.des_articulo, 
                       GROUP_CONCAT(DISTINCT kar.movimiento SEPARATOR ', ') AS movimiento, kar.cve_usuario, 
                       SUM(kar.cajas) AS cajas, kar.almacen, 
                       '' AS oc, '' AS cve_lote, '' AS Caducidad, '' AS fecha, kar.origen, '' AS clave_contenedor, 
                       '' AS CveLP, '' AS bl, '' AS ajuste, '' AS stockinicial, kar.destino, '' AS cantidad
                FROM (".$sql_0.") AS kar
                GROUP BY kar.id_articulo
            ) as cjs #WHERE cjs.cajas > 0
        ";
*/
        $sql = "SELECT  kar.fecha, kar.Almacen_Origen, kar.Almacen_Destino, kar.id_articulo, kar.des_articulo, 
                                       'Traslado' AS movimiento, kar.cve_usuario, 
                                       COUNT(DISTINCT kar.cajas) AS cajas, SUM(kar.num_unidades) AS num_unidades, 

                                       kar.clave_contenedor AS clave_contenedor, 
                                       kar.CveLP AS CveLP
                                FROM (

                                SELECT alp_or.id as al_id_or, alp_dest.id as al_id_dest, k.fecha, alp_or.clave as Almacen_Origen, alp_dest.clave as Almacen_Destino, a.cve_articulo as id_articulo, a.des_articulo as des_articulo, 
                                       'Traslado' AS movimiento, k.cve_usuario, 
                                       ec.Id_Caja AS cajas, k.cantidad as num_unidades,
                                       al_dest.des_almac as almacen, lp.IDContenedor as ntarima, mch.ID_Contenedor as ntarima2,
                                       '' AS oc, '' AS cve_lote, '' AS Caducidad, k.origen as origen, lp.Clave_Contenedor AS clave_contenedor,  
                                       lp.CveLP AS CveLP, u_dest.CodigoCSD AS bl, '' AS ajuste, '' AS stockinicial, k.destino, '' AS cantidad
                                FROM ts_existenciacajas ec 
                                LEFT JOIN t_MovCharolas mch ON mch.ID_Contenedor = ec.Id_Caja AND IFNULL(mch.EsCaja, '') = 'S'
                                LEFT JOIN t_cardex k ON mch.id_kardex = k.id AND ec.cve_articulo = k.cve_articulo and IFNULL(ec.cve_lote, '') = IFNULL(k.cve_lote, '')
                                LEFT JOIN c_ubicacion u_or ON u_or.idy_ubica = mch.Origen
                                LEFT JOIN c_ubicacion u_dest ON u_dest.idy_ubica = mch.Destino
                                LEFT JOIN c_almacen al_or ON al_or.cve_almac = u_or.cve_almac
                                LEFT JOIN c_almacen al_dest ON al_dest.cve_almac = u_dest.cve_almac
                                LEFT JOIN c_almacenp alp_or ON alp_or.id = al_or.cve_almacenp
                                LEFT JOIN c_almacenp alp_dest ON alp_dest.id = al_dest.cve_almacenp
                                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                                LEFT JOIN c_charolas cj ON cj.IDContenedor = ec.Id_Caja
                                LEFT JOIN c_charolas lp ON lp.IDContenedor = ec.nTarima
                                WHERE k.id_TipoMovimiento IN (6, 12) AND mch.Id_TipoMovimiento IN (6, 12) AND ec.Id_Caja is not null AND alp_dest.id != alp_or.id
                                AND (alp_or.id= $almacen OR alp_dest.id = $almacen)
                                AND IFNULL(lp.CveLP, '') != ''
                                {$SQLArticulo} {$SQLLote} {$SQLCriterioCajas} {$SQLFecha}
                  ) AS kar
                where (kar.al_id_or = $almacen OR kar.al_id_dest = $almacen) 
                GROUP BY kar.clave_contenedor, kar.id_articulo
                ORDER BY kar.clave_contenedor
                ";
    }

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $tot_cajas = 0; $tot_num_unidades = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);
// align="right"
//<th scope="row"></th>
        if($kardex_consolidado == 0)
        {
  ?>
    <tr>
      <th><?php echo $fecha; ?></td>
      <td><?php echo $id_articulo; ?></td>
      <?php /* ?><td><?php echo utf8_decode($des_articulo); ?></td><?php */ ?>
      <?php if($kardex_consolidado == 0){ ?><td><?php echo utf8_decode($cve_lote); ?><br><?php echo $Caducidad; ?></td><?php } ?>
      <?php /* ?><td width="300"><?php echo $Caducidad; ?></td><?php */ ?>
      <?php if($kardex_consolidado == 0){ ?><td><?php echo utf8_decode($clave_contenedor); ?><br><?php echo utf8_decode($CveLP); ?></td><?php } ?>
      <?php /* ?><td width="300"><?php echo utf8_decode($CveLP); ?></td><?php */ ?>
      <td><?php echo utf8_decode($movimiento); ?></td>
      <td><?php echo $origen; ?></td>
      <td><?php echo $destino; ?></td>
      <?php if($kardex_consolidado == 0){ ?><td align="right"><?php echo $stockinicial; ?></td><?php } ?>
      <?php if($kardex_consolidado == 0){ ?><td align="right"><?php echo $cantidad; ?></td><?php } ?>
      <?php if($kardex_consolidado == 0){ ?><td align="right"><?php echo $ajuste; ?></td><?php } ?>
      <td><?php echo $cve_usuario; ?></td>
    </tr>
    <?php 
        }
        else
        {
      ?>
        <tr>
          <td><?php echo $id_articulo; ?></td>
          <td><?php echo utf8_decode($des_articulo); ?></td>
          <td><?php echo utf8_decode($movimiento); ?></td>
          <td><?php echo $Almacen_Origen; ?></td>
          <td><?php echo $Almacen_Destino; ?></td>
          <td><?php echo utf8_decode($clave_contenedor); ?></td>
          <td align="right"><?php echo $num_unidades; ?></td>
          <td align="right"><?php echo $cajas; ?></td>
          <td><?php echo $cve_usuario; ?></td>
        </tr>
        <?php 
        $tot_cajas += $cajas; $tot_num_unidades += $num_unidades;
        }
    }

        if($kardex_consolidado == 1)
        {
      ?>
        <tr>
          <th><div style="font-size:20px;">Total</div></th>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <th><div style="font-size:20px;text-align: right !important;"><?php echo $tot_num_unidades; ?></div></th>
          <th><div style="font-size:20px;text-align: right !important;"><?php echo $tot_cajas; ?></div></th>
          <td></td>
        </tr>
        <?php /* ?><tr><td colspan="8"><?php echo $sql; ?></td></tr><?php */ ?>
        <?php 
        }

    ?>
  </tbody>
</table>
</div>
<?php 
/*
}
else
{
    $reporte = json_decode($_GET['reporte'], true);
    $cve_cia = $_GET['cve_cia'];
    $sql = "SELECT imagen, DATE_FORMAT(CURDATE(), '%d-%m-%Y') as fecha_hoy FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">Reporte <?php if(isset($_GET['tipomovimiento'])){echo "Movimientos del Almacén";}else echo "Kardex"; ?> <?php 

                    if($fecha_inicio && $fecha_final) echo " | Desde: ".$fecha_inicio." Hasta: ".$fecha_final;
                    else if($fecha_inicio) echo " | Desde: ".$fecha_inicio." Hasta: ".$fecha_hoy;
                    else if($fecha_final) echo " | Hasta el día: ".$fecha_final;
                     ?></span></h1>
                </div>
        </div>
    </div>
<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th>Fecha</th>
      <th>Clave</th>
      <th>Articulo</th>
      <th>Pallet</th>
      <th>LP</th>
      <th>Movimiento</th>
      <th>Almacen Origen</th>
      <th>Origen</th>
      <th>Almacen Destino</th>
      <th>Destino</th>
      <th>Cantidad</th>
      <th>Cajas</th>
      <th>Usuario</th>
    </tr>
  </thead>
  <tbody>
  <?php 
    foreach($reporte as $rep)
    {
  ?>
    <tr>
      <th><?php echo utf8_decode($rep["fecha"]); ?></th>
      <td><?php echo utf8_decode($rep["cve_articulo"]); ?></td>
      <td><?php echo utf8_decode($rep["descripcion"]); ?></td>
      <td><?php echo utf8_decode($rep["pallet"]); ?></td>
      <td><?php echo utf8_decode($rep["lp"]); ?></td>
      <td><?php echo utf8_decode($rep["movimiento"]); ?></td>
      <td><?php echo utf8_decode($rep["alm_origen"]); ?></td>
      <td><?php echo utf8_decode($rep["blorigen"]); ?></td>
      <td><?php echo utf8_decode($rep["alm_destino"]); ?></td>
      <td><?php echo utf8_decode($rep["bldestino"]); ?></td>
      <td><?php echo utf8_decode($rep["cantidad"]); ?></td>
      <td><?php echo utf8_decode($rep["cajas"]); ?></td>
      <td><?php echo utf8_decode($rep["usuario"]); ?></td>

    </tr>
    <?php 
    }
    ?>
  </tbody>
</table>
</div>
<?php 
}
*/
?>
</div>
</body>
</html>

