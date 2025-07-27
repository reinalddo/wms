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
<title>Reporte General de Conteos</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

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
                  
                    <h1><span lang="th">
                        Reporte General de Conteos
                        </span></h1>
                    <h1><span lang="th">
                        Inventario: <span lang="th"><?php echo $id; ?></span>
                        </span></h1>

                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th scope="col"># BLs</th>
      <th scope="col"># Num Productos</th>
      <th scope="col"># BLs Contadas</th>
      <th scope="col"># Bls Vacíos</th>
      <th scope="col">Diferencia</th>
      <th scope="col">Fiabilidad %</th>
      <th scope="col">Nro Conteo</th>
    </tr>
  </thead>
  <tbody>
  <?php 

  //Físico
    $sql = "SELECT 

                    IF(inv.status = 'T',IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (SELECT MAX(t_invpiezas.NConteo) FROM t_invpiezas WHERE t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--'),0) AS diferencia,
                    
                    IF(inv.status = 'T',ROUND(
                      IFNULL((
                        SELECT 
                        ((SUM(ExistenciaTeorica) - (IF(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/SUM(ExistenciaTeorica))*100 AS Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (SELECT MAX(t_invpiezas.NConteo) FROM t_invpiezas WHERE t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2),0) AS porcentaje,

                    ((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario)) AS num_bls,

                    (
                        (
                        SELECT COUNT(*) FROM 
                        (
                          SELECT DISTINCT v.ID_Inventario, v.cve_articulo FROM
                           t_invpiezas v 
                           WHERE IFNULL(Cantidad, 0) > 0 AND v.NConteo > 0 AND 
                           v.cve_articulo NOT IN (SELECT cve_articulo FROM t_invtarima WHERE ID_Inventario = v.ID_Inventario)
                        ) AS i WHERE i.ID_Inventario = inv.ID_Inventario
                        ) 
                        + 
                        (
                        SELECT COUNT(*) FROM 
                        (
                          SELECT DISTINCT v.ID_Inventario, v.cve_articulo FROM
                           t_invtarima v 
                           WHERE IFNULL(existencia, 0) > 0 AND v.NConteo > 0 
                        ) AS i WHERE i.ID_Inventario = inv.ID_Inventario
                        ) 

                    ) AS num_productos,


                    ((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) AS num_bls_cont,

                    ((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo = 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0) AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo = 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0) AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0))) AS num_bls_vacios,

                    IFNULL((
                    IF((SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario) > (SELECT MAX(NConteo) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario), (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario), (SELECT MAX(NConteo) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario))
                    ), 0) AS n_inventario  
 

                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.ID_Inventario = {$id}
                GROUP BY inv.ID_Inventario";
    
    if($tipo == 'Cíclico')
        $sql = "SELECT
                    IF(d.status = 'T',IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                        
                    ), '--'),0) AS diferencia,
                    
                  IF(d.status = 'T',ROUND(
                    IFNULL((
                        SELECT 
                        ((SUM(ExistenciaTeorica) - (IF(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/SUM(ExistenciaTeorica))*100 AS Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                    ), '--'), 2),0) AS porcentaje,

                    (SELECT COUNT(DISTINCT num_bl.idy_ubica) FROM (SELECT idy_ubica, ID_PLAN FROM t_invpiezasciclico UNION SELECT idy_ubica, ID_PLAN FROM t_invtarimaciclico ) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_bls,
                    (SELECT COUNT(DISTINCT num_bl.cve_articulo) FROM (SELECT cve_articulo, ID_PLAN FROM t_invpiezasciclico UNION SELECT cve_articulo, ID_PLAN FROM t_invtarimaciclico ) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_productos,
                    (SELECT COUNT(DISTINCT num_bl.idy_ubica) FROM (SELECT idy_ubica, ID_PLAN FROM t_invpiezasciclico WHERE NConteo > 0 UNION SELECT idy_ubica, ID_PLAN FROM t_invtarimaciclico WHERE NConteo > 0) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_bls_cont,

                    '' AS num_bls_vacios,


                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezasciclico
                        WHERE ID_PLAN = cab.ID_PLAN
                            GROUP BY ID_PLAN
                        
                    ), 0) AS n_inventario
                FROM det_planifica_inventario d
                    LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                    LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN
                    LEFT JOIN c_almacenp ap ON cab.id_almacen = ap.id 
                WHERE d.ID_PLAN = {$id}
    ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
        extract($row);
  ?>
    <tr>
      <td align="right"><?php echo $num_bls; ?></td>
      <td align="right"><?php echo $num_productos; ?></td>
      <td align="right"><?php echo $num_bls_cont; ?></td>
      <td align="right"><?php echo $num_bls_vacios; ?></td>
      <td align="right"><?php echo $diferencia; ?></td>
      <td align="right"><?php echo $porcentaje; ?></td>
      <td align="right"><?php echo $n_inventario; ?></td>
    </tr>

  </tbody>
</table>
</div>

</div>
</body>
</html>

