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
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ubicación</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Teorico</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Inventariado</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Diferencia</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario</div>
    <?php /* ?><div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Empresa</div><?php */ ?>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $id_inventario = $_GET['id'];
    $status        = "";//$_GET['status'];
    $cia           = "";//$_GET['comp'];
    $usuario       = $_GET['usuario'];
    $conteo        = $_GET['conteo_usuario'];
    $fecha         = "";//$_GET['fecha_inv'];
    $ubicacion     = $_GET['ubicacion_inv'];
    $codigo_csd    = $_GET['ubicacion_text_inv'];
    $codigo_rack   = $_GET['ubicacion_rack'];
    $tipo          = $_GET['tipo'];

      $sql_usuario = "";
      if($usuario) $sql_usuario = "AND inv.cve_usuario = '{$usuario}'";

      $sql_rack = "";
      if($codigo_rack) $sql_rack = "AND ub.cve_rack = '{$codigo_rack}'";

      $sql_ubicacion = "AND inv.idy_ubica = v.cve_ubicacion";
      if($ubicacion) $sql_ubicacion = "AND inv.idy_ubica = '{$ubicacion}'";

      $sql_ubicacion2 = "";
      if($ubicacion) $sql_ubicacion2 = "AND inv.idy_ubica = '{$ubicacion}'";

//*************************************************************************************************

    $usuarios = "";
    if($usuario)
    {
      $sql = "SELECT DISTINCT cve_usuario, nombre_completo FROM c_usuario WHERE cve_usuario = '$usuario'";
        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }
        $rowusuario = mysqli_fetch_array($res);
        $usuarios = "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo'];
    
    }
    else 
    {
      $sql = "";
      if($tipo == 'Físico')
      $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
              SELECT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              UNION
              SELECT cve_usuario FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              )
              ";
        else
            $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
              SELECT cve_usuario FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo}
              UNION
              SELECT cve_usuario FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo}
              )
              ";
        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }

        while ($rowusuario = mysqli_fetch_array($res)) {
            $usuarios .= "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo']." ";
        }
    }
//*************************************************************************************************

    $sql = "";

    if($tipo == 'Físico')
    {
          $sql = "SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote
  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.ExistenciaTeorica AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            ub.CodigoCSD AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            #AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} 
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
    }
    else
    {
      $sql = "SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote

  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
    }

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;
?>

<?php
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $ubicacion; ?></div>
        <div cell="C<?php echo $i; ?>" excelFormat="text"><?php echo $clave; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_decode($descripcion); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $stockTeorico; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Inventariado; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo ($Inventariado-$stockTeorico); ?></div>
        <div cell="I<?php echo $i; ?>"><?php if($usuario) echo $usuario; else echo $usuarios; ?></div>
        <?php /* ?><div cell="J<?php echo $i; ?>"><?php echo utf8_decode($Nombre_Empresa); ?></div><?php */ ?>
        <?php 
        $i++;

    }
  ?>

    
</div>