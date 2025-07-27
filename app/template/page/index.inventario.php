<?php
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
//    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
//    $charset = mysqli_fetch_array($res_charset)['charset'];
//    mysqli_set_charset($conn , $charset);
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

$cliente_almacen_style = ""; $cve_proveedor = ""; $sql_proveedor = ""; $sql_proveedor2 = ""; $sql_proveedor3 = "";
if($_SESSION['es_cliente'] == 2) 
{
    $cliente_almacen_style = "style='display: none;'";
    $cve_proveedor  = $_SESSION['cve_proveedor'];
    $sql_proveedor  = " AND V_ExistenciaGralProduccion.Id_Proveedor = {$cve_proveedor} ";
    $sql_proveedor2 = " AND vp.Id_Proveedor = {$cve_proveedor} ";
    $sql_proveedor3 = " AND ts_existenciapiezas.ID_Proveedor = {$cve_proveedor} ";
}

if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}
?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

<?php
$almacenSeleccionado = isset($_GET['almacen']) ? $_GET['almacen'] : $_SESSION['cve_almacen'];

$almacenSeleccionadoId = "";
if($almacenSeleccionado){
    $almacenSeleccionadoId = \db()->prepare("SELECT id from c_almacenp WHERE Activo = 1 AND clave = '$almacenSeleccionado'");
    $almacenSeleccionadoId->execute();
    $almacenSeleccionadoId = $almacenSeleccionadoId->fetch()['id'];
}

$id_user = $_SESSION['id_user'];
$almacenesSql = \db()->prepare("SELECT clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac  WHERE Activo = 1 AND id_user = $id_user");
$almacenesSql->execute();
$almacenesDisponibles = $almacenesSql->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT
            ca.cve_articulo,
            ca.des_articulo,
            (SELECT COUNT(DISTINCT Fol_folio) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) AS cantidadPedidos,
            (SELECT SUM(Num_Cantidad) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) AS Total
        FROM
            c_articulo ca
        LEFT JOIN Rel_Articulo_Almacen ral ON ral.Cve_Articulo = ca.cve_articulo
        LEFT JOIN c_lotes on ca.cve_articulo=c_lotes.cve_articulo
        WHERE 1 AND (SELECT COUNT(DISTINCT Fol_folio) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) > 0
            ";
            #YEAR(STR_TO_DATE(c_lotes.CADUCIDAD,'%d-%m-%Y')) = YEAR (CURDATE())
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND ral.Cve_Almac = '$almacenSeleccionadoId' ";
}

$sql .= "
        GROUP BY
            ca.cve_articulo
        ORDER BY
            cantidadPedidos DESC
        LIMIT 10";
$productoMayorMovimientoSql = \db()->prepare($sql);
$productoMayorMovimientoSql->execute();
$productoMayorMovimiento = $productoMayorMovimientoSql->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT
            ca.cve_articulo,
            ca.des_articulo,
            (SELECT COUNT(DISTINCT Fol_folio) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) AS cantidadPedidos,
            (SELECT SUM(Num_Cantidad) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) AS Total
        FROM
            c_articulo ca
        LEFT JOIN Rel_Articulo_Almacen ral ON ral.Cve_Articulo = ca.cve_articulo
        LEFT JOIN c_lotes on ca.cve_articulo=c_lotes.cve_articulo
        WHERE 1 AND (SELECT COUNT(DISTINCT Fol_folio) FROM td_pedido WHERE Cve_articulo = ca.cve_articulo) > 0
            ";
            #YEAR(STR_TO_DATE(c_lotes.CADUCIDAD,'%d-%m-%Y')) = YEAR (CURDATE())
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND ral.Cve_Almac = '$almacenSeleccionadoId' ";
}

$sql .= "
        GROUP BY
            ca.cve_articulo
        ORDER BY
            cantidadPedidos ASC
        LIMIT 10";
$productoMenorMovimientoSql = \db()->prepare($sql);
$productoMenorMovimientoSql->execute();
$productoMenorMovimiento = $productoMenorMovimientoSql->fetchAll(PDO::FETCH_ASSOC);

$almacenLote = (!empty($almacenSeleccionadoId)) ? " AND l.cve_articulo IN (SELECT cve_articulo FROM c_articulo WHERE cve_almac = '$almacenSeleccionadoId')" : "";

$sql = "
    SELECT *, concat('Articulos: ',total,' |',' Piezas: ',piezas) as texto from (SELECT
		sum(x.total) as total,
		sum(x.piezas) as piezas,
		sum(x.activo) as activo,
		sum(x.inactivo) as inactivo
		from
		(
		SELECT 
			COUNT(distinct(a.cve_articulo)) AS total,
			if(a.Activo = 1, 1,0) AS activo,
			if(a.Activo = 0, 1,0) AS inactivo,
			truncate(SUM(V_ExistenciaGralProduccion.Existencia),4) as piezas
		FROM c_articulo a
			inner join V_ExistenciaGralProduccion on V_ExistenciaGralProduccion.cve_articulo = a.cve_articulo
			inner join c_almacenp on c_almacenp.id = V_ExistenciaGralProduccion.cve_almac
			where c_almacenp.clave = '".$almacenSeleccionado."'
			and V_ExistenciaGralProduccion.Existencia > 0
            and V_ExistenciaGralProduccion.tipo = 'ubicacion'
            {$sql_proveedor}
            group by a.cve_articulo
		) x)y;
";

$lotesTotalesSql = \db()->prepare($sql);
$lotesTotalesSql->execute();
$lotesTotales = $lotesTotalesSql->fetch()['piezas'];

$left_lotes = "";
$and_lotes = "";
if($_SESSION['es_cliente'] == 2 && $cve_proveedor) 
{
    $left_lotes = "LEFT JOIN rel_articulo_proveedor r ON r.Cve_Articulo = vp.cve_articulo";
    $and_lotes = "AND r.Id_Proveedor = {$cve_proveedor}";
}
$sql = "
    SELECT 
        CONCAT('Articulos: ',x.cuenta,' |',' Piezas: ',x.suma) AS texto,
        x.cuenta,
        x.suma
    FROM(
      SELECT 
          COUNT(DISTINCT (vp.cve_articulo)) AS cuenta,
          SUM(DISTINCT vp.Existencia) AS suma
      FROM V_ExistenciaGralProduccion vp
          LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
          LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
      LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
      {$left_lotes}
      WHERE c_lotes.Caducidad < CURDATE()
      {$and_lotes}
      AND DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
      AND Existencia > 0
      {$sql_proveedor2}
      AND c_almacenp.clave = '$almacenSeleccionado'
      AND c_ubicacion.CodigoCSD != ''
    ) as x
";
/*if (!($res_charset = mysqli_query($conn, $sql)))
    echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";

$lotes = mysqli_fetch_array($res_charset);
*/
$lotesVencidosSql = \db()->prepare($sql);
$lotesVencidosSql->execute();
$res_lotes = $lotesVencidosSql->fetchAll(PDO::FETCH_ASSOC);
//echo $sql;
//var_dump($res_lotes);
$lotesVencidosTexto = $res_lotes["texto"];
$lotesVencidos = $res_lotes["suma"];
$lotesVencidosPorcen = 0;
if($lotesTotales > 0)
    $lotesVencidosPorcen = $lotesVencidos != 0 ? $lotesVencidos  * 100 / $lotesTotales : 0;

$left_lotes = "";
$and_lotes = "";
if($_SESSION['es_cliente'] == 2 && $cve_proveedor) 
{
    $left_lotes = "LEFT JOIN rel_articulo_proveedor r ON r.Cve_Articulo = ts_existenciapiezas.cve_articulo";
    $and_lotes = "AND r.Id_Proveedor = {$cve_proveedor}";
}

$sql = "
    SELECT  
        concat('Articulos: ',x.cuenta,' |',' Piezas: ',x.suma) as texto,
        x.cuenta,
        x.suma
    from(
        SELECT 
            count(DISTINCT(ts_existenciapiezas.cve_articulo)) as cuenta,
            sum(DISTINCT ts_existenciapiezas.Existencia) as suma
        FROM ts_existenciapiezas
            inner join c_lotes on c_lotes.LOTE = ts_existenciapiezas.cve_lote and c_lotes.cve_articulo = ts_existenciapiezas.cve_articulo
            inner join c_almacenp on c_almacenp.id = ts_existenciapiezas.cve_almac
            {$left_lotes}
        where c_lotes.Caducidad > CURDATE()
        {$and_lotes}
            and DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') <= DATE_ADD(NOW(), INTERVAL 12 MONTH)
            and DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
            and Existencia > 0
            {$sql_proveedor3}
            and c_almacenp.clave = '$almacenSeleccionado'
    )x
";

if (!($res_charset = mysqli_query($conn, $sql)))
    echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
$lote = mysqli_fetch_array($res_charset);

//$lotesVencerSql = \db()->prepare($sql);
//$lotesVencerSql->execute();
//$lote = $lotesVencerSql->fetch();
$lotesVencerTexto = $lote['texto'];
$lotesVencer = $lote['suma'];
$lotesVencerPorcen = $lotesVencer != 0 ? $lotesVencer * 100 / $lotesTotales : 0;

$enlaceLotes = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."lotes/lists?m=10";

?>
<style type="text/css">
    ul.block li{
        display: block;
    }
    /*, #grid-tablePV */
    .#grid-tableLV {
      max-width: 100%;
    }
</style>
<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <?php
    $cliente_almacen_style = ""; 
    if($_SESSION['es_cliente'] == 2) $cliente_almacen_style = "style='display: none;'";
    ?>
    <div class="row" <?php echo $cliente_almacen_style; ?>>
        <div class="col-md-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Almacén</h5>
                </div>
                <div class="ibox-content">
                    <div class="form-group">
                        <?php if($almacenSeleccionado): ?>
                            <label>Filtrando datos del almacen: </label>
                        <?php else: ?>
                            <label>Seleccione almacén</label>
                        <?php endif; ?>
                        <select name="almacen" id="almacen" class="form-control">
                            <option value="">Seleccione</option>
                            <?php foreach($almacenesDisponibles as $almacen): ?>
                                <option <?php echo ($almacenSeleccionado && $almacenSeleccionado === $almacen['clave']) ? 'selected' : '' ?> value="<?php echo $almacen['clave'] ?>"><?php echo "(".$almacen['clave'].") - ".$almacen['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="almacen" value="<?php echo $almacenSeleccionadoId?>">
                    </div>
                </div>
            </div>
        </div>

        <?php 
        if(isset($_GET['m']))
        {
            if($_GET['m'] != 'cli')
            {
        ?>
        <div class="col-md-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right" <?php if(!isset($_GET['m'])){ ?> style="display: none;" <?php } ?>>
					   <?php if($_SESSION['es_cliente'] != 2){ ?><a style="color: white;" href="/articulos/lists?almacen=<?php echo $_GET["almacen"]; ?>">Resumen</a><?php } ?>
					</span>
                    <h5 id="name">Inventario:</h5>
                </div>
                <div class="ibox-content">
                    <h3 class="no-margins" id="quantity">0</h3>
                    <div class="stat-percent font-bold text-danger" id="inactive">0 <i class="fa fa-thumbs-down"></i></div>
                    <small class="text-success" id="active">0 <i class="fa fa-thumbs-up"></i></small>
                </div>
            </div>
        </div>
        <?php 
            }
        }
        ?>
        <div class="col-md-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                <span class="label label-info pull-right">
                  Total
                </span>
                    <h5>
                        Lotes vencidos 
                    </h5>
                </div>
                <div class="ibox-content">
                    <h3 class="no-margins">
                        <?php echo !$almacenSeleccionado ? 0 :  $lotesVencidosTexto; ?>
                    </h3>
                    <div class="stat-percent font-bold text-success">
                        <?php echo !$almacenSeleccionado ? 0 :  number_format($lotesVencidosPorcen, 2, ',', '.') ?>%
                    </div>
                    <small>
                        Último trimestre (<a id="vencidos" href="#">Ver más</a>)
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                <span class="label label-info pull-right">
                  Total
                </span>
                    <h5>
                        Lotes por vencer
                    </h5>
                </div>
                <div class="ibox-content">
                    <h3 class="no-margins">
                        <?php echo !$almacenSeleccionado ? 0 :  $lotesVencerTexto?>
                    </h3>
                    <div class="stat-percent font-bold text-success">
                        <?php echo !$almacenSeleccionado ? 0 :  number_format($lotesVencerPorcen, 2, ',', '.') ?>%
                    </div>
                    <small>
                        Próximo trimestre (<a id="porvencer" href="#">Ver más</a>)
                    </small>
                </div>
            </div>
        </div>
    </div>

        <?php 
        if(isset($_GET['m']))
        {
            if($_GET['m'] != 'cli')
            {
        ?>


<?php 
/*
$islas = array(
  "Embarcando" => 3,
  "Embarque Completo" => 5,
  "Libres" => 4,
  "Total de Guias" => 1,
);
$total_pedidos = array("Total de Pedidos" => 45);
*/
/*
SELECT DISTINCT tipo.clave_ttransporte, COUNT(e.ID_OEmbarque) AS n_embarques
FROM tipo_transporte tipo
LEFT JOIN t_transporte t ON tipo.clave_ttransporte = t.tipo_transporte
LEFT JOIN th_ordenembarque e ON t.id = e.ID_Transporte
WHERE e.Cve_Almac = $almacenSeleccionadoId 
GROUP BY tipo.clave_ttransporte
ORDER BY tipo.clave_ttransporte
*/
$sql = "
    SELECT DISTINCT t.ID_Transporte, COUNT(e.ID_OEmbarque) AS n_embarques
FROM t_transporte t
LEFT JOIN th_ordenembarque e ON t.id = e.ID_Transporte
WHERE e.Cve_Almac = '{$almacenSeleccionadoId}' AND DATE_FORMAT(e.fecha, '%m-%Y') = DATE_FORMAT(CURDATE(), '%m-%Y')
GROUP BY ID_Transporte
ORDER BY ID_Transporte
";

@$tipo_transporte_query_grafica = \db()->prepare($sql);
@$tipo_transporte_query_grafica->execute();

$titulos_grafica_tipo_transporte = array();
$valores_grafica_tipo_transporte = array();
$colors_grafica_tipo_transporte = array();
$borders_grafica_tipo_transporte = array();

while($res = $tipo_transporte_query_grafica->fetch())
{
    $titulos_grafica_tipo_transporte[] = '"'.$res["ID_Transporte"].'"';
    $valores_grafica_tipo_transporte[] = $res["n_embarques"];
        //$colors_grafica_tipo_transporte[] = '"rgba(255, 0, 50, 0.7)"';
        //$borders_grafica_tipo_transporte[] = '"rgba(255, 0, 50, 1)"';

        //$colors_grafica_tipo_transporte[] = '"rgba(255, 230, 0, 0.7)"';
        //$borders_grafica_tipo_transporte[] = '"rgba(255, 230, 0, 1)"';
    $borders_grafica_tipo_transporte[] = '"rgba(0, 128, 64, 1)"';
        
}

$colors_grafica_tipo_transporte = array('"rgb(0, 204, 102)"', '"rgb(153, 255, 102)"', '"rgb(204, 51, 0)"', '"rgb(0, 102, 204)"', '"rgb(102, 102, 153)"', '"rgb(255, 255, 102)"', '"rgb(0, 255, 0)"', '"rgb(255, 153, 0)"', '"rgb(102, 51, 0)"', '"rgb(102, 0, 102)"', '"rgb(0, 153, 153)"', '"rgb(51, 102, 204)"', '"rgb(255, 0, 0)"', '"rgb(153, 102, 0)"', '"rgb(102, 102, 51)"', '"rgb(51, 204, 204)"', '"rgb(204, 153, 255)"', '"rgb(51, 153, 102)"', '"rgb(102, 204, 255)"', '"rgb(255, 179, 179)"', '"rgb(153, 204, 0)"', '"rgb(204, 204, 255)"');
/*
if(count($valores_grafica_tipo_transporte) == 0){
    $titulos_grafica_tipo_transporte[] = 0;
    $valores_grafica_tipo_transporte[] = 0;
    $colors_grafica_tipo_transporte[] = '';
    $borders_grafica_tipo_transporte[] = '';
}
*/

?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Embarques Realizados por tipo de Transporte del Mes.</h5>
                        </div>
                        <div class="ibox-content">
                            <div><canvas id="chartIslas" height="200"></canvas></div>
                            <!--cambiar a los status-->
                            <ul class="list-unstyled" style="display: none;">
                                <?php /* foreach($islas as $ordenTitle => $ordenValue): ?>
                                    <li><b><?php echo $ordenTitle ?>: </b><?php echo  $ordenValue ?></li>
                                <?php endforeach; ?>
                                <?php  foreach($total_pedidos as $ordenTitle => $ordenValue): ?>
                                    <li><b><?php echo $ordenTitle ?>: </b><?php echo  $ordenValue ?></li>
                                <?php endforeach;*/ ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Productos de mayor movimiento por Cantidad</h5>
                </div>
                <div class="ibox-content">
                    <?php if(count($productoMayorMovimiento) <= 0 || !$almacenSeleccionado): ?>
                        <div class="alert alert-success">No hay registros para mostrar</div>
                    <?php else: ?>
                        <table class="table table-hover no-margins">
                            <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cantidad Pedidos</th>
                                <th>Cantidad</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($productoMayorMovimiento as $producto): ?>
                                <tr>
                                    <td><span class="label label-success pull-left"><?php echo $producto["cve_articulo"]?></span></td>
                                    <td><?php echo $producto["des_articulo"]?></td>
                                    <td align="right"><?php echo $producto["cantidadPedidos"]?></td>
                                    <td align="right"><?php echo number_format($producto["Total"], 2)?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Productos de menor movimiento por Cantidad</h5>
                </div>
                <div class="ibox-content">
                    <?php if(count($productoMenorMovimiento) <= 0 || !$almacenSeleccionado): ?>
                        <div class="alert alert-success">No hay registros para mostrar</div>
                    <?php else: ?>
                        <table class="table table-hover no-margins">
                            <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cantidad Pedidos</th>
                                <th>Cantidad</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($productoMenorMovimiento as $producto): ?>
                                <tr>
                                    <td><span class="label label-warning pull-left"><?php echo $producto["cve_articulo"]?></span></td>
                                    <td><?php echo $producto["des_articulo"]?></td>
                                    <td align="right"><?php echo $producto["cantidadPedidos"]?></td>
                                    <td align="right"><?php echo number_format($producto["Total"], 2);?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php 
        }
    }
?>
</div>

<div class="modal fade" id="modalLV" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" >
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Lotes Vencidos</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar Articulo...">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtLote1" placeholder="Buscar Lote...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGridLV()">
                                    <button type="submit" id="buscarR" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-tableLV"></table>
                            <div id="grid-pagerLV"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPV" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" >
            <div class="modal-content" style="width: fit-content !important;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Lotes por vencer</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio2" placeholder="Buscar Articulo...">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtLote2" placeholder="Buscar Lote...">  
                            </div>
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGridPV()">
                                    <button type="submit" id="buscarR" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-tablePV"></table>
                            <div id="grid-pagerPV"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 40px">
    <div class="col-md-12">
        <div class="footer">
            <div>
                <strong>Copyright </strong>  AssistPro ADL ®  Todos los derechos reservados. &copy; 2017
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Flot -->
<script src="/js/plugins/flot/jquery.flot.js"></script>
<script src="/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="/js/plugins/flot/jquery.flot.spline.js"></script>
<script src="/js/plugins/flot/jquery.flot.resize.js"></script>
<script src="/js/plugins/flot/jquery.flot.pie.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- GITTER -->
<script src="/js/plugins/gritter/jquery.gritter.min.js"></script>

<!-- Sparkline -->
<script src="/js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="/js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="/js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr -->
<script src="/js/plugins/toastr/toastr.min.js"></script>

<!-- Morris -->
<script src="/js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="/js/plugins/morris/morris.js"></script>

<!-- d3 and c3 charts -->
<script src="/js/plugins/d3/d3.min.js"></script>
<script src="/js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<script>

    $( document ).ready(function() {
    	<?php if($almacenSeleccionado): ?>
        	request();
       	<?php endif; ?>
    });


</script>

<script type="text/javascript">
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){

        var element = document.getElementById('almacen'),
            valueActual = element.value;

        if(valueActual === ""){ 
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/almacenPredeterminado/index.php',
                success: function(data) {
                    if (data.success == true) {
                        element.value = data.codigo.clave;
                        $('#almacen').trigger('change');
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
    }
    almacenPrede();
//Grid Lotes vencidos

$(function($) {
        var grid_selector = "#grid-tableLV";
        var pager_selector = "#grid-pagerLV";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/lotes/lista/vencidos.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: $("#almacen").val()
            },
            mtype: 'POST',
            colNames:['id','Clave de Artículo','Descripción','Lote','Caducidad','Ubicacion','Cantidad','Unidad de medida'],
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable:false,hidden:true},
                {name:'articulo',index:'articulo',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion',width:100, editable:false, sortable:false},
                {name:'cantidad',index:'cantidad',width:100, editable:false, sortable:false, align:"right"},
                {name:'um',index:'um',width:130, editable:false, sortable:false},

            ],
            rowNum:10,
            rowList:[10,20,30, 40, 50, 70, 100],
            pager: pager_selector,
            sortname: 'cve_articulo',
            viewrecords: true,
            sortorder: "desc",
            responsive: true,
            loadComplete: function(data){console.log("DONE", data);},
            loadError: function(data){console.log("FAIL", data);}
        });

        // Setup buttons
        $("#grid-tableLV").jqGrid('navGrid', '#grid-pagerLV',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            var id = rowObject[0];

            //$("#hiddenClave").val(cve_articulo);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+id+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });

    });

$(function($) {
        var grid_selector = "#grid-tablePV";
        var pager_selector = "#grid-pagerPV";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            //$(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/lotes/lista/porvencer.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: $("#almacen").val()
            },
            mtype: 'POST',
            colNames:['id','Clave de Artículo','Descripción','Lote','Caducidad','Ubicacion','Cantidad','Unidad de medida'],
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable:false,hidden:true},
                {name:'articulo',index:'articulo',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion',width:100, editable:false, sortable:false},
                {name:'cantidad',index:'cantidad',width:100, editable:false, sortable:false, align:"right"},
                {name:'um',index:'um',width:130, editable:false, sortable:false},

            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_articulo',
            viewrecords: true,
            responsive: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-tablePV").jqGrid('navGrid', '#grid-pagerPV',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            var id = rowObject[0];

            //$("#hiddenClave").val(cve_articulo);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+id+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function request(){
        var almacen = $("#almacen").val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "load",
                cve_proveedor: $("#cve_proveedor").val(),
                almacen: almacen
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/resumenejecutivo/update/index.php',
            success: function(data) {
                console.log("success = ", data);
                if (data.success == true) {
                    $("#quantity").text(data.texto.toLocaleString().replace(",","."));
                   $("#active").text("Activos: "+data.activo.toLocaleString().replace(",","."));
                   $("#inactive").text("Inactivos: "+data.inactivo.toLocaleString().replace(",","."));
                    $("#name").text(data.articulo);
                }
            }, error: function(data)
            {
                console.log("success = ", data);
            }
        });
    }

</script>
<script>
    $('select[name="almacen"]').on('change', function(e){
        var url = new URL(window.location.href),
            params = new URLSearchParams(url.search);
        if(!params.has('almacen')){
            params.append('almacen', e.target.value);
        }else{
            params.set('almacen', e.target.value)
        }
        url.search = params.toString();
        window.location.href = url;
    });

                  $("#porvencer").on("click", function(){
            $modal0 = $("#modalPV");
            $modal0.modal('show');
        });
  

        $("#modalLV").on('shown.bs.modal', function(){
            $("#grid-tableLV").jqGrid( 'setGridWidth', $("#modalLV .modal-body").width() - 100 );
        });

        $("#modalPV").on('shown.bs.modal', function(){
            $("#grid-tableLV").jqGrid( 'setGridWidth', $("#modalPV .modal-body").width() - 100 );
        });


        $("#almacen option[value='']").hide();
        $("#vencidos").on("click", function(){
            $("#modalLV").modal('show');
            
        });
    

</script>
<script>
    function ReloadGridLV()
    {

        console.log("almacen = ", $("#almacen").val());
        console.log("buscarR = ", $("#txtCriterio1").val());
        console.log("buscarL = ", $("#txtLote1").val());
        $('#grid-tableLV').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacen: $("#almacen").val(),
            buscarR: $("#txtCriterio1").val(),
            buscarL: $("#txtLote1").val(),
        },datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    }
    function ReloadGridPV()
    {
        $('#grid-tablePV').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            search: $("#txtCriterio2").val(),
            searchL: $("#txtLote2").val(),
        },datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    }
  
          var islas_cantidades = {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(",", $titulos_grafica_tipo_transporte); ?>],
                //labels: ["Label1", "Label2", "Label3"],
                datasets: [{
                    data: [<?php echo implode(",", $valores_grafica_tipo_transporte); ?>], 
                    //data: [34, 12, 45], 
                  
                    backgroundColor: [<?php echo implode(",", $colors_grafica_tipo_transporte); ?>],
                    borderColor: [<?php echo implode(",", $borders_grafica_tipo_transporte); ?>],
                    borderWidth: 1
                }]
            }/*,
            options: {
                legend: {
                    display: false
                 },     
                scales: {

                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }     
            }*/
        };
      
        


        //var ctx = document.getElementById("barChart").getContext("2d");
        //var myNewChart = new Chart(ctx, ordenes); //
      
        var grafica = document.getElementById("chartIslas").getContext("2d");
        var myNewChart = new Chart(grafica, islas_cantidades); //


</script>