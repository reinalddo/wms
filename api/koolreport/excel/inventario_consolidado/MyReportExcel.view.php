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
      $diferencia    = $_GET['diferencia_inv'];

    if($diferencia == 2 || $diferencia == 3)
    {
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ubicación</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 1</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 2</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 3</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 4</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 5</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Valor Final</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ajuste</div>
    <?php /* ?><div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Empresa</div><?php */ ?>

<?php 
    }
    else
    {
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ubicación</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Teorico</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 1</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario 1</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 2</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario 2</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 3</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario 3</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 4</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario 4</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo 5</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario 5</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Valor Final</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ajuste</div>
    <?php /* ?><div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Empresa</div><?php */ ?>

<?php 
    }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $id_inventario = $_GET['id'];
      $status        = "";//$_GET['status'];
      $cia           = "";//$_GET['comp'];
      $fecha         = "";//$_GET['fecha_inv'];
      $rack          = $_GET['rack'];
      $tipo          = $_GET['tipo'];

      $sql_rack = "";
      if($rack)
      $sql_rack = "AND ub.cve_rack = '{$rack}'";

    $sql = "";
    if($tipo == 'Físico')
    {
$sql_inicial = "";
/*
    $sql = "SELECT IFNULL(Inv_Inicial, 0) as Inv_Inicial FROM th_inventario WHERE ID_Inventario = {$id_inventario}";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $tipo_inicial = 1;
    $rowinv = mysqli_fetch_array($res);
    $tipo_inicial = $rowinv['Inv_Inicial'];

    $sql_inicial = "";
    if($tipo_inicial == 0)
      $sql_inicial = " WHERE RIGHT(tinv.Nconteo, 1) != '1' AND tinv.TeoricoPiezas != 0 ";
*/
      $sql = "
SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, CONVERT(tinv.descripcion USING utf8) as descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.Nombre_Empresa, tinv.usuario, tinv.unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote AND c_lotes.cve_articulo = v.cve_articulo
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario AND cinv.ID_Inventario = {$id_inventario}
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            #AND inv.NConteo > 0 
            {$sql_rack} 
            AND inv.Cantidad >= 0
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 

                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ext.ExistenciaTeorica AS stockTeorico,

                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                #(SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                inv.Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario AND cinv.ID_Inventario = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarima invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND inv.Cantidad >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                '1' AS TeoricoPiezas,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario AND cinv.ID_Inventario = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                LEFT JOIN t_invtarima inv_pr ON inv_pr.ID_Inventario = inv.ID_Inventario  AND inv_pr.NConteo = 0 AND inv_pr.cve_articulo = inv.cve_articulo AND inv_pr.Cve_Lote = inv.Cve_Lote AND inv_pr.ntarima = inv.ntarima  AND inv_pr.ID_Inventario = {$id_inventario}
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND inv.existencia >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                inv.ExistenciaTeorica as stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                #(SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                inv.Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
            LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario AND cinv.ID_Inventario = {$id_inventario}
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            #{$sql_rack}
            AND inv.Cantidad >= 0
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            {$sql_inicial}
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY descripcion
            ";
    }
    else
        $sql = "SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, CONVERT(tinv.descripcion USING utf8) as descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.Nombre_Empresa, tinv.usuario, tinv.unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote AND c_lotes.cve_articulo = v.cve_articulo
                LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            #AND inv.NConteo > 0 
            {$sql_rack} 
            AND inv.Cantidad >= 0
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarimaciclico invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                LEFT JOIN t_invtarima inv_pr ON inv_pr.ID_PLAN = inv.ID_PLAN  AND inv_pr.NConteo = 0 AND inv_pr.cve_articulo = inv.cve_articulo AND inv_pr.Cve_Lote = inv.Cve_Lote AND inv_pr.ntarima = inv.ntarima  AND inv_pr.ID_PLAN = {$id_inventario}
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND inv.existencia >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
            LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            #{$sql_rack}
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY descripcion
            ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);
                      //if($diferencia == 1 && $Cerrar == 1) continue;

                      $cantidad_conteoN = explode(",", $Cantidad_reg);
                      $conteosN         = explode(",", $Nconteo);
                      $NConteo_Cantidad_reg = explode(",", $NConteo_Cantidad_reg);


                      $conteo = array("BB", "BB", "BB", "BB", "BB", "BB");//$conteo2 = 0;$conteo3 = 0;$conteo4 = 0;$conteo5 = 0;
                      $usuario = array();

                      $n_cantidades = count($cantidad_conteoN);
                      //$n_conteos    = count($conteosN);

                      $val_in_i = 1;
                      $n_conteos = $conteosN[count($conteosN)-1];
                      $n_conteos++;
                      //if($NConteo_Cantidad_reg[0] != '0-0') {$val_in_i = 1; $n_conteos++;}
                      if($NConteo_Cantidad_reg[0] == '0-0')
                      {
                      array_splice($NConteo_Cantidad_reg, 0, 1); 
                      array_splice($conteosN, 0, 1); 
                      }
                      for($j = 1; $j <= count($NConteo_Cantidad_reg); $j++)
                      {
                            $conteo_cantidad = explode("-", $NConteo_Cantidad_reg[$j-$val_in_i]);
                            $conteo[$conteo_cantidad[0]] = $conteo_cantidad[1];
                            $usuario[$conteo_cantidad[0]] = $conteo_cantidad[2];
/*
                          if($j < $n_conteos)
                          {
                            $conteo_cantidad = explode("-", $NConteo_Cantidad_reg[$j-$val_in_i]);
                            //$conteo[$conteo_cantidad[0]] = $conteo_cantidad[1];

                            if($j == $conteo_cantidad[0])
                              $conteo[$j] = $conteo_cantidad[1];
                            else 
                              $conteo[$j] = '0';
                            //$conteo[$i] = $cantidad_conteoN[$i];

                          }
*/
                      }

                      $imprimir_diferencia = false;
                      if($diferencia == 1)
                      {
                            $valor = false;
                            $found = 0;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;

                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($row["Cantidad"] == $conteo[$n] && $conteo[$n] != 0 && $row["Cantidad"] != 0)// || $row["stockTeorico"] == $conteo[$n]
                                     $found++;
                              }

                                if($found >= 2)
                                    $imprimir_diferencia = false;
                                else 
                                    $imprimir_diferencia = true;
                            }
                            else 
                            {
                                $imprimir_diferencia = true; 
                            }
                      }

                       //if($diferencia == 1 && ($row["Cantidad"]-$row["stockTeorico"]) == 0) continue;
                      if($diferencia == 1 && ($imprimir_diferencia == false || $clave == "")) continue;


                      //if($n_cantidades < $n_conteos)
                        // $conteo[$n_conteos-1] = $Cantidad;

            if($diferencia == 2 || $diferencia == 3)
            {
                if($diferencia == 3)
                {
                    $found = 0; 
                    $valor = false;
                    $ajuste_val = "";
                    for($n = 1; $n < count($conteo); $n++)
                      if($conteo[$n] != "")
                        $valor = true;

                    if($valor)
                    {
                      $found = 0;
                      for($n = 1; $n < count($conteo); $n++)
                      {
                          if($Cantidad == $conteo[$n])
                             $found++;
                      }
                        if($found >= 2)
                            $ajuste_val = $Cantidad;
                        else
                             $ajuste_val = "";
                    }

                    if($conteo[1] == 'BB' || $ajuste_val != "" || $found >= 2) continue;
                }
                    ?>
                  <div cell="A<?php echo $i; ?>"><?php echo $ubicacion; ?></div>
                  <div cell="B<?php echo $i; ?>"><?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''; else echo $conteo[1]."&nbsp;&nbsp;";}//else if(1 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="C<?php echo $i; ?>"><?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''; else echo $conteo[2]."&nbsp;&nbsp;";}//else if(2 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="D<?php echo $i; ?>"><?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''; else echo $conteo[3]."&nbsp;&nbsp;";}//else if(3 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="E<?php echo $i; ?>"><?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''; else echo $conteo[4]."&nbsp;&nbsp;";}//else if(4 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="F<?php echo $i; ?>"><?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''; else echo $conteo[5]."&nbsp;&nbsp;";}//else if(5 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="G<?php echo $i; ?>">
                        <?php
                            $found = 0; 
                            $valor = false;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;
                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($Cantidad == $conteo[$n])
                                     $found++;
                              }
                                if($found >= 2)
                                    echo $Cantidad;
                                else
                                    echo "";
                            }
                        ?>  
                      </div>
                      <div cell="H<?php echo $i; ?>">
                        <?php if($found >= 2) echo ($Cantidad-$stockTeorico); ?></div>
                  <?php /* ?><div cell="I<?php echo $i; ?>"><?php echo utf8_decode($Nombre_Empresa); ?></div><?php */ ?>
        <?php 
            }
            else
            {
                if($diferencia == 1 && ($conteo[1] == "BB" || ($Cantidad-$stockTeorico) == 0)) continue;
                    ?>
                  <div cell="A<?php echo $i; ?>"><?php echo $LP; ?></div>
                  <div cell="B<?php echo $i; ?>"><?php echo $clave; ?></div>
                  <div cell="C<?php echo $i; ?>"><?php echo utf8_decode($descripcion); ?></div>
                  <div cell="D<?php echo $i; ?>"><?php echo $lote; ?></div>
                  <div cell="E<?php echo $i; ?>"><?php echo $ubicacion; ?></div>
                  <div cell="F<?php echo $i; ?>"><?php echo $stockTeorico; ?></div>
                  <div cell="G<?php echo $i; ?>"><?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''; else echo $conteo[1]."&nbsp;&nbsp;";}//else if(1 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="H<?php echo $i; ?>"><?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''; else echo $usuario[1]."&nbsp;&nbsp;";}//else if(1 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="I<?php echo $i; ?>"><?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''; else echo $conteo[2]."&nbsp;&nbsp;";}//else if(2 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="J<?php echo $i; ?>"><?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''; else echo $usuario[2]."&nbsp;&nbsp;";}//else if(2 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="K<?php echo $i; ?>"><?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''; else echo $conteo[3]."&nbsp;&nbsp;";}//else if(3 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="L<?php echo $i; ?>"><?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''; else echo $usuario[3]."&nbsp;&nbsp;";}//else if(3 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="M<?php echo $i; ?>"><?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''; else echo $conteo[4]."&nbsp;&nbsp;";}//else if(4 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="N<?php echo $i; ?>"><?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''; else echo $usuario[4]."&nbsp;&nbsp;";}//else if(4 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="O<?php echo $i; ?>"><?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''; else echo $conteo[5]."&nbsp;&nbsp;";}//else if(5 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="P<?php echo $i; ?>"><?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''; else echo $usuario[5]."&nbsp;&nbsp;";}//else if(5 <= $Max_Conteo) echo "0"; ?>  </div>
                  <div cell="Q<?php echo $i; ?>">
                        <?php
                            $found = 0; 
                            $valor = false;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;
                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($Cantidad == $conteo[$n])
                                     $found++;
                              }
                                if($found >= 2)
                                    echo $Cantidad;
                                else
                                {
                                    if($diferencia == 1) $found = 20;
                                    echo "";
                                }
                            }
                        ?>  
                      </div>
                      <div cell="R<?php echo $i; ?>">
                        <?php if($found == 20){echo (0-$stockTeorico-$Cantidad);}else{if($found >= 2) echo ($Cantidad-$stockTeorico);} ?></div>
                  <?php /* ?><div cell="N<?php echo $i; ?>"><?php echo utf8_decode($Nombre_Empresa); ?></div><?php */ ?>
        <?php 
            }
            $i++;

    }

    ?>
          <?php /* ?><div cell="B<?php echo $i+3; ?>"><?php echo $sql; ?></div><?php */ ?>

    
</div>