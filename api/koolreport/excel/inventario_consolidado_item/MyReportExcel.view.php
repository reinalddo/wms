<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Inventario";
?>
<meta charset="UTF-8">
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="Excel,HTML,CSS,XML,JavaScript">
<meta name="creator" content="John Doe">
<meta name="subject" content="subject1">
<meta name="title" content="title1">
<meta name="category" content="category1">

<div sheet-name="<?php echo $sheet1; ?>">



    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Total</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $id_inventario = $_GET['id'];
    $tipo          = $_GET['tipo'];

    $sql = "";
/*
    if($tipo == 'Físico')
        $sql = "SELECT t.cve_articulo AS clave, t.descripcion, SUM(t.Cantidad) AS Existencia_Total FROM (
SELECT DISTINCT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$id_inventario}) AS cve_almac, tp.NConteo, tp.idy_ubica, tp.cve_articulo, tp.cve_lote, '' AS ntarima, tp.Cantidad,
            a.des_articulo AS descripcion,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND cve_ubicacion = tp.idy_ubica AND Cve_Contenedor = '') AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezas WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 0 AND ID_Inventario = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezas WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 1 AND ID_Inventario = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1))) AS proveedor
                        FROM t_invpiezas tp
                        LEFT JOIN c_articulo a ON a.cve_articulo = tp.cve_articulo
                        WHERE tp.ID_Inventario = {$id_inventario} #AND tp.NConteo > 0 
                        AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo > 0)
                        AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_Inventario = {$id_inventario} AND Cantidad > 0)

                        UNION 

                        SELECT DISTINCT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$id_inventario}) AS cve_almac, tt.NConteo, tt.idy_ubica, tt.cve_articulo, tt.cve_lote, tt.ntarima, tt.existencia AS Cantidad,
                        a.des_articulo AS descripcion,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND cve_ubicacion = tt.idy_ubica AND Cve_Contenedor = (SELECT clave_contenedor FROM c_charolas WHERE IDContenedor  = tt.ntarima)) AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarima WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 0 AND ntarima = tt.ntarima AND ID_Inventario = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarima WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 1 AND ntarima = tt.ntarima AND ID_Inventario = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1))) AS proveedor
                        FROM t_invtarima tt 
                        LEFT JOIN c_articulo a ON a.cve_articulo = tt.cve_articulo
                        WHERE tt.ID_Inventario = {$id_inventario} #AND tt.NConteo > 0 
                        AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarima WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND ntarima = tt.ntarima AND ID_Inventario = {$id_inventario} AND existencia > 0)
                        ORDER BY NConteo ASC
         ) AS t  
         GROUP BY clave
         ORDER BY descripcion";

        else
            $sql = "SELECT t.cve_articulo AS clave, t.descripcion, SUM(t.Cantidad) AS Existencia_Total FROM (

            SELECT DISTINCT tp.NConteo, tp.idy_ubica, tp.cve_articulo, tp.cve_lote, '' AS ntarima, tp.Cantidad,
            a.des_articulo AS descripcion,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND cve_ubicacion = tp.idy_ubica AND Cve_Contenedor = '') AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezasciclico WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 0 AND ID_PLAN = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezas WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 1 AND ID_PLAN = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1))) AS proveedor
                        FROM t_invpiezasciclico tp
                        LEFT JOIN c_articulo a ON a.cve_articulo = tp.cve_articulo
                        WHERE tp.ID_PLAN = {$id_inventario} #AND tp.NConteo > 0 
                        AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0)
                        AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezasciclico WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_PLAN = {$id_inventario} AND Cantidad > 0)

                        UNION 

                        SELECT DISTINCT tt.NConteo, tt.idy_ubica, tt.cve_articulo, tt.cve_lote, tt.ntarima, tt.existencia AS Cantidad,
                        a.des_articulo AS descripcion,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND cve_ubicacion = tt.idy_ubica AND Cve_Contenedor = (SELECT clave_contenedor FROM c_charolas WHERE IDContenedor  = tt.ntarima)) AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarimaciclico WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 0 AND ntarima = tt.ntarima AND ID_PLAN = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarima WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 1 AND ntarima = tt.ntarima AND ID_PLAN = {$id_inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1))) AS proveedor
                        FROM t_invtarimaciclico tt 
                        LEFT JOIN c_articulo a ON a.cve_articulo = tt.cve_articulo
                        WHERE tt.ID_PLAN = {$id_inventario} #AND tt.NConteo > 0 
                        AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarimaciclico WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND ntarima = tt.ntarima AND ID_PLAN = {$id_inventario} AND existencia > 0)
                        ORDER BY NConteo ASC
         ) AS t 
         GROUP BY clave
         ORDER BY descripcion";
*/

/*      $sql = "
        SELECT clave, descripcion, SUM(Cantidad) AS Existencia_Total, Cerrar FROM (
                SELECT  
                        v.cve_ubicacion AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.Cantidad != -1) AS Cantidad
                    FROM V_ExistenciaGralProduccion v
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                        LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                    WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo 
                    #AND inv.cve_lote = v.cve_lote #and inv.NConteo > 0
                    AND inv.NConteo > 0
                    GROUP BY clave, ubicacion

          UNION

                SELECT  
                        inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote AND iv.Cantidad != -1) AS Cantidad
                    FROM t_invpiezas inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave

          UNION

                SELECT  
            inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote AND iv.existencia != -1) AS Cantidad
                    FROM t_invtarima inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave
          ORDER BY clave

         ) AS t WHERE Cerrar = 1 GROUP BY clave
      ";
    else
          $sql = "
        SELECT clave, descripcion, SUM(Cantidad) AS Existencia_Total, Cerrar FROM (
                SELECT  
                        v.cve_ubicacion AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.Cantidad != -1) AS Cantidad
                    FROM V_ExistenciaGralProduccion v
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                        LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                    WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo 
                    #AND inv.cve_lote = v.cve_lote #and inv.NConteo > 0
                    AND inv.NConteo > 0
                    GROUP BY clave, ubicacion

          UNION

                SELECT  
                        inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote AND iv.Cantidad != -1) AS Cantidad
                    FROM t_invpiezasciclico inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave

          UNION

                SELECT  
                        inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote AND iv.existencia != -1) AS Cantidad
                    FROM t_invtarimaciclico inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave
          ORDER BY clave

         ) AS t WHERE Cerrar = 1 GROUP BY clave
      ";
*/

        if($tipo == "Físico")
            $sql = "SELECT item.cve_articulo AS clave, a.des_articulo AS descripcion, SUM(item.Cantidad) AS Existencia_Total FROM (
                            SELECT  p.NConteo, 
                                    p.idy_ubica, 
                                    p.cve_articulo, 
                                    p.cve_lote, 
                                    p.Cantidad, '' AS ntarima 
                            FROM t_invpiezas p 
                            WHERE p.ID_Inventario = {$id_inventario} AND p.Cantidad > 0
                            AND p.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND cve_articulo = p.cve_articulo AND cve_lote = p.cve_lote AND idy_ubica = p.idy_ubica)

                            UNION

                            SELECT  t.NConteo, 
                                    t.idy_ubica, 
                                    t.cve_articulo, 
                                    t.cve_lote, 
                                    t.existencia AS Cantidad, 
                                    t.ntarima 
                            FROM t_invtarima t 
                            WHERE t.ID_Inventario = {$id_inventario} AND t.existencia > 0
                            AND t.NConteo = (SELECT MAX(NConteo) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND cve_articulo = t.cve_articulo AND cve_lote = t.cve_lote AND idy_ubica = t.idy_ubica AND ntarima = t.ntarima)
                            ORDER BY NConteo, cve_articulo
                    ) AS item
                     LEFT JOIN c_articulo a ON item.cve_articulo= a.cve_articulo
                     GROUP BY clave
                     ORDER BY descripcion";
        else
            $sql = "SELECT item.cve_articulo AS clave, a.des_articulo AS descripcion, SUM(item.Cantidad) AS Existencia_Total FROM (
                            SELECT  p.NConteo, 
                                    p.idy_ubica, 
                                    p.cve_articulo, 
                                    p.cve_lote, 
                                    p.Cantidad, '' AS ntarima 
                            FROM t_invpiezas p 
                            WHERE p.ID_PLAN = {$id_inventario} AND p.Cantidad > 0
                            AND p.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_PLAN = {$id_inventario} AND cve_articulo = p.cve_articulo AND cve_lote = p.cve_lote AND idy_ubica = p.idy_ubica)

                            UNION

                            SELECT  t.NConteo, 
                                    t.idy_ubica, 
                                    t.cve_articulo, 
                                    t.cve_lote, 
                                    t.existencia AS Cantidad, 
                                    t.ntarima 
                            FROM t_invtarimaciclico t 
                            WHERE t.ID_PLAN = {$id_inventario} AND t.existencia > 0
                            AND t.NConteo = (SELECT MAX(NConteo) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND cve_articulo = t.cve_articulo AND cve_lote = t.cve_lote AND idy_ubica = t.idy_ubica AND ntarima = t.ntarima)
                            ORDER BY NConteo, cve_articulo
                    ) AS item
                     LEFT JOIN c_articulo a ON item.cve_articulo= a.cve_articulo
                     GROUP BY clave
                     ORDER BY descripcion";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;
    ?>
    <?php 
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Existencia_Total; ?></div>
        <?php 
        $i++;

    }
  ?>

    <?php /* ?><div cell="B<?php echo $i+3; ?>"><?php echo $sql; ?></div><?php */ ?>

</div>