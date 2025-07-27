<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';
    $id_inventario = $_GET['id'];
    $tipo = $_GET['tipo'];

    $sheet1 = "Inventario ".$id_inventario;
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
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Conteo</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>BL</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Valor Final</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$sql = "";
    if($tipo == 'Físico')
    {
        $sql = "SELECT IFNULL(Inv_Inicial, 0) as Inv_Inicial FROM th_inventario WHERE ID_Inventario = {$id_inventario}";
        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }

        $tipo_inicial = 1;
        $rowinv = mysqli_fetch_array($res);
        $tipo_inicial = $rowinv['Inv_Inicial'];

        $valor = 0;
        if($tipo_inicial == 0)
            $valor = 1;

      $sql = "SELECT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$id_inventario}) AS cve_almac, tp.NConteo, u.CodigoCSD, tp.cve_articulo, tp.cve_lote, '' AS ntarima, tp.Cantidad 
            FROM t_invpiezas tp
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tp.idy_ubica
            WHERE tp.ID_Inventario = {$id_inventario} AND tp.NConteo > {$valor} 
            #AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo > 0)
            AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_Inventario = {$id_inventario} AND Cantidad >= 0 )
            AND IFNULL(tp.cve_articulo, '') != ''

            UNION 

            SELECT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$id_inventario}) AS cve_almac, tt.NConteo, u.CodigoCSD, tt.cve_articulo, tt.cve_lote, ch.CveLP AS ntarima, tt.existencia AS Cantidad 
            FROM t_invtarima tt 
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tt.idy_ubica
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
            WHERE tt.ID_Inventario = {$id_inventario} AND tt.NConteo > {$valor} 
            AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarima WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND ntarima = tt.ntarima AND ID_Inventario = {$id_inventario} AND existencia >= 0 )
            AND IFNULL(tt.cve_articulo, '') != ''
            
            ORDER BY NConteo ASC
    ";
    }
    else
      $sql = "SELECT (SELECT p.id FROM cab_planifica_inventario i LEFT JOIN c_almacenp p ON p.clave = i.id_almacen WHERE i.ID_PLAN = {$id_inventario}) AS cve_almac, tp.NConteo, u.CodigoCSD, tp.cve_articulo, tp.cve_lote, '' AS ntarima, tp.Cantidad 
            FROM t_invpiezasciclico tp
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tp.idy_ubica
            WHERE tp.ID_PLAN = {$id_inventario} AND tp.NConteo > 0 
            AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0)
            AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezasciclico WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_PLAN = {$id_inventario} AND Cantidad >= 0 )
            AND IFNULL(tp.cve_articulo, '') != ''

            UNION 

            SELECT (SELECT p.id FROM cab_planifica_inventario i LEFT JOIN c_almacenp p ON p.clave = i.id_almacen WHERE i.ID_PLAN = {$id_inventario}) AS cve_almac, tt.NConteo, u.CodigoCSD, tt.cve_articulo, tt.cve_lote, ch.CveLP AS ntarima, tt.existencia AS Cantidad 
            FROM t_invtarimaciclico tt 
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tt.idy_ubica
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
            WHERE tt.ID_PLAN = {$id_inventario} AND tt.NConteo > 0 
            AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarimaciclico WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND ntarima = tt.ntarima AND ID_PLAN = {$id_inventario} AND existencia >= 0)
            AND IFNULL(tt.cve_articulo, '') != ''
            ORDER BY NConteo ASC
    ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $NConteo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $ntarima; ?></div>
        <div cell="C<?php echo $i; ?>" excelFormat="text"><?php echo $CodigoCSD; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo " ".utf8_decode($cve_lote); ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Cantidad; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>