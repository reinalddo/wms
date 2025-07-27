<?php
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

$almacenSeleccionado = isset($_GET['almacen']) && !empty($_GET['almacen']) ? $_GET['almacen'] : false;

if($almacenSeleccionado)
{
    $almacenSeleccionadoId = \db()->prepare("SELECT id from c_almacenp WHERE Activo = 1 AND clave = '$almacenSeleccionado'");
    $almacenSeleccionadoId->execute();
    $almacenSeleccionadoId = $almacenSeleccionadoId->fetch()['id'];
}
$id_user = $_SESSION['id_user'];
$almacenesSql = \db()->prepare("SELECT clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac  WHERE Activo = 1 AND id_user = $id_user");
$almacenesSql->execute();
$almacenesDisponibles = $almacenesSql->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT COUNT(o.fol_folio) cuenta
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = 'A'";
//$sql = "SELECT ((SELECT COUNT(o.fol_folio) FROM th_backorder o WHERE o.Status = 'A') + (SELECT COUNT(o.fol_folio) num_pedidos_bo FROM th_backorder o WHERE o.Status = 'A')) AS cuenta FROM DUAL";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND o.cve_almac = '$almacenSeleccionadoId'";
}
$pedidosSql = \db()->prepare($sql);
$pedidosSql->execute();
$pedidos = $almacenSeleccionado ? $pedidosSql->fetch()['cuenta'] : 0;

$sql = "SELECT count(*) as cuenta FROM `th_pedido` WHERE YEAR(Fec_Pedido) = YEAR(CURDATE())";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionadoId'";
}
$lastPedidosSql = \db()->prepare($sql);
$lastPedidosSql->execute();
$lastPedidos = $almacenSeleccionado ? $lastPedidosSql->fetch()['cuenta'] : 0;
if($pedidos)
$lastPedidos = $lastPedidos != 0 ? $lastPedidos * 100 / $pedidos : 0;

$sql = "SELECT count(*) as cuenta FROM th_ordenembarque WHERE Activo = '1'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND cve_almac = '$almacenSeleccionadoId'";
}
$embarquesSql = \db()->prepare($sql);
$embarquesSql->execute();
$embarques = $embarquesSql->fetch()['cuenta'];

//Tabla 1
$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1 AND status = 'C'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesPendientesSql = \db()->prepare($sql);
$ordenesPendientesSql->execute();
$ordenesPendientes = $ordenesPendientesSql->fetch()['cuenta'];

$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1 AND status = 'I'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesProcesoSql = \db()->prepare($sql);
$ordenesProcesoSql->execute();
$ordenesProceso = $ordenesProcesoSql->fetch()['cuenta'];

$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1 AND status = 'T'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesCerradasSql = \db()->prepare($sql);
$ordenesCerradasSql->execute();
$ordenesCerradas = $ordenesCerradasSql->fetch()['cuenta'];

$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1 AND status = 'A'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesEditandoSql = \db()->prepare($sql);
$ordenesEditandoSql->execute();
$ordenesEditando = $ordenesEditandoSql->fetch()['cuenta'];


$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1 AND status = 'K'";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesCanceladasSql = \db()->prepare($sql);
$ordenesCanceladasSql->execute();
$ordenesCanceladas = $ordenesCanceladasSql->fetch()['cuenta'];

$sql = "SELECT count(*) as cuenta from th_aduana Where Activo = 1";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND Cve_Almac = '$almacenSeleccionado'";
}
$ordenesTotalSql = \db()->prepare($sql);
$ordenesTotalSql->execute();
$ordenesTotal = $ordenesTotalSql->fetch()['cuenta'];

$ordenes = array(
    "Cerradas" => $ordenesCerradas,
    "Pendientes" => $ordenesPendientes,
    "En Proceso" => $ordenesProceso,
    "Editando"   => $ordenesEditando,
    "Canceladas" => $ordenesCanceladas,
    "Total" => ($ordenesCerradas+$ordenesPendientes+$ordenesProceso+$ordenesEditando+$ordenesCanceladas)//$ordenesTotal
);

$usersOnlineSql = \db()->prepare("SELECT COUNT(DISTINCT id_usuario) AS cuenta, IFNULL(id_usuario, 0) AS id FROM `users_online` WHERE `last_updated` > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND id_usuario != (SELECT id_user FROM c_usuario WHERE cve_usuario = 'wmsmaster');");
$usersOnlineSql->execute();
$usersOnlineData = $usersOnlineSql->fetch();
$usersOnline = $usersOnlineData['cuenta'];

$usersOfflineSql = \db()->prepare("SELECT COUNT(`id_user`) AS cuenta FROM `c_usuario` WHERE `id_user` <> ".$usersOnlineData['id'].";");
$usersOfflineSql->execute();
$usersOffline = $usersOfflineSql->fetch()['cuenta'];

$usersOnlinePorcen = $usersOffline != "0" ? ($usersOnline * 100 / $usersOffline) : 100;
$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];
$capacidad_ocupado = 1;
if($instancia != 'dicoisa')
{
$sql_capacidad_ocupado = "
    SELECT 
        #TRUNCATE(SUM(((IFNULL(c_articulo.alto, 0))*(IFNULL(c_articulo.fondo, 0))*(IFNULL(c_articulo.ancho, 0)))*V_ExistenciaGralProduccion.Existencia),4) AS volumen_ocupado
    IFNULL(TRUNCATE(SUM(((IFNULL(c_articulo.alto, 0)/1000)*(IFNULL(c_articulo.fondo, 0)/1000)*(IFNULL(c_articulo.ancho, 0)/1000))*V_ExistenciaGralProduccion.Existencia),4), 0) AS volumen_ocupado
    #TRUNCATE((SUM(IFNULL(c_ubicacion.num_ancho, 0)))*(SUM(IFNULL(c_ubicacion.num_largo, 0)))*(SUM(IFNULL(c_ubicacion.num_alto, 0))),4) AS volumen_ocupado
    from V_ExistenciaGralProduccion 
    left join c_ubicacion on c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion 
    left join c_articulo on c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo
    left join c_almacenp on c_almacenp.id = V_ExistenciaGralProduccion.cve_almac
    where c_almacenp.clave = '$almacenSeleccionado'
";

/*
$sql_capacidad_ocupado = "SELECT SUM(e.volumen_ocupado) AS volumen_ocupado FROM (
SELECT 
        TRUNCATE(SUM(((alto/1000)*(fondo/1000)*(ancho/1000))*p.Existencia),4) AS volumen_ocupado
    FROM ts_existenciapiezas p
    INNER JOIN c_ubicacion ON c_ubicacion.idy_ubica = p.idy_ubica
    INNER JOIN c_articulo ON c_articulo.cve_articulo = p.cve_articulo
    INNER JOIN c_almacen ON c_almacen.cve_almac = c_ubicacion.cve_almac 
    INNER JOIN c_almacenp ON c_almacenp.id = c_almacen.cve_almacenp
    WHERE c_almacenp.clave = '$almacenSeleccionado'
    UNION 
SELECT 
        TRUNCATE(SUM(((alto/1000)*(fondo/1000)*(ancho/1000))*t.Existencia),4) AS volumen_ocupado
    FROM ts_existenciatarima t
    INNER JOIN c_ubicacion ON c_ubicacion.idy_ubica = t.idy_ubica
    INNER JOIN c_articulo ON c_articulo.cve_articulo = t.cve_articulo
    INNER JOIN c_almacen ON c_almacen.cve_almac = c_ubicacion.cve_almac 
    INNER JOIN c_almacenp ON c_almacenp.id = c_almacen.cve_almacenp
    WHERE c_almacenp.clave = '$almacenSeleccionado'
    ) AS e";
*/
$capacidadOSql = \db()->prepare($sql_capacidad_ocupado);
$capacidadOSql->execute();
$capacidad_ocupado = $capacidadOSql->fetch()["volumen_ocupado"];
}
$capacidad_total = 1;
if($instancia != 'dicoisa')
{
$sql_capacidad_total = "
        SELECT 
            #TRUNCATE(sum((num_ancho/1000)*(num_largo/1000)*(num_alto/1000)),4) as total_volumen
        TRUNCATE(SUM((num_ancho/1000)*(num_largo/1000)*(num_alto/1000)),4) AS total_volumen
        #TRUNCATE((SUM(IFNULL(c_ubicacion.num_ancho, 0)))*(SUM(IFNULL(c_ubicacion.num_largo, 0)))*(SUM(IFNULL(c_ubicacion.num_alto, 0))),4) AS total_volumen
        from c_ubicacion
        inner join c_almacen   on c_almacen.cve_almac = c_ubicacion.cve_almac
        inner join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
        where c_almacenp.clave = '$almacenSeleccionado'
";
$capacidadTSql = \db()->prepare($sql_capacidad_total);
$capacidadTSql->execute();
$capacidad_total = $capacidadTSql->fetch()["total_volumen"];
}

$capacidad_libre = ($capacidad_total != 0)?$capacidad_total - $capacidad_ocupado:0;
$capacidad_libre_p = ($capacidad_total != 0)?($capacidad_libre/$capacidad_total)*100:0;
$capacidad_ocupado_p = ($capacidad_total != 0)?($capacidad_ocupado/$capacidad_total)*100:0;
//ocupadas = 2 // cerradas = 3 // libres =1

$islas = array(
  "Embarcando" => $islas["Ocupadas"],
  "Embarque Completo" => $islas["Cerradas"],
  "Libres" => $islas["Libres"],
  "Total de Guias" => $islas["Total"],
);

$sql1 = "
    SELECT 
        count(*) as Cantidad 
    FROM t_ubicacionembarque 
    where t_ubicacionembarque.status = 1 
        and t_ubicacionembarque.cve_almac = '".$almacenSeleccionadoId."'
        and t_ubicacionembarque.Activo = 1;
";
$islas_libres = \db()->prepare($sql1);
$islas_libres->execute();
$res_libres = $islas_libres->fetch();

$sql2 = "
    SELECT 
        count(*) as Cantidad 
    FROM t_ubicacionembarque 
    where t_ubicacionembarque.status != 3 
        and ( 
            select count(*) 
            from rel_uembarquepedido 
            where rel_uembarquepedido.cve_ubicacion = t_ubicacionembarque.ID_Embarque 
        ) > 0
        and t_ubicacionembarque.cve_almac = '".$almacenSeleccionadoId."'
        and t_ubicacionembarque.Activo = 1;
";
$islas_embarcado = \db()->prepare($sql2);
$islas_embarcado->execute();
$res_embarcado = $islas_embarcado->fetch();

$sql3 = "
    SELECT 
        count(*) as Cantidad 
    FROM t_ubicacionembarque 
    where t_ubicacionembarque.status = 3 
        and ( 
            select count(*) 
            from rel_uembarquepedido 
            where rel_uembarquepedido.cve_ubicacion = t_ubicacionembarque.ID_Embarque 
        ) > 0
        and t_ubicacionembarque.cve_almac = '".$almacenSeleccionadoId."'
        and t_ubicacionembarque.Activo = 1;
";
$islas_completo = \db()->prepare($sql3);
$islas_completo->execute();
$res_completo = $islas_completo->fetch();

$sqlguia = "
    select 
        count(*) as Cantidad 
    from rel_uembarquepedido 
        inner join t_ubicacionembarque on t_ubicacionembarque.ID_Embarque = rel_uembarquepedido.cve_ubicacion
        inner join th_cajamixta on th_cajamixta.fol_folio = rel_uembarquepedido.fol_folio 
    where rel_uembarquepedido.cve_almac = '".$almacenSeleccionadoId."'
        and t_ubicacionembarque.Activo = 1;
";
$islas_guia = \db()->prepare($sqlguia);
$islas_guia->execute();
$res_guia = $islas_guia->fetch();

$sqlpedidos = "
    select 
        count(*) as pedidos 
    from rel_uembarquepedido 
        inner join t_ubicacionembarque on t_ubicacionembarque.ID_Embarque = rel_uembarquepedido.cve_ubicacion
    where rel_uembarquepedido.cve_almac = '".$almacenSeleccionadoId."'
        and t_ubicacionembarque.Activo = 1;
";
$pedidos_query = \db()->prepare($sqlpedidos);
$pedidos_query->execute();
$respedidos = $pedidos_query->fetch();

$islas["Libres"]=$res_libres["Cantidad"];
$islas["Embarcando"]=$res_embarcado["Cantidad"];
$islas["Embarque Completo"]=$res_completo["Cantidad"];
$islas["Total de Guias"] = $res_guia["Cantidad"];
$total_pedidos = array("Total de Pedidos" => $respedidos["pedidos"]);

$sql = "
    SELECT * FROM (
        SELECT 
            t_ubicacionembarque.descripcion, 
            t_ubicacionembarque.status,
            count(th_cajamixta.Guia) as total_guias,
            count(rel_uembarquepedido.fol_folio) as pedidos
        FROM `t_ubicacionembarque` 
            left join rel_uembarquepedido on t_ubicacionembarque.ID_Embarque = rel_uembarquepedido.cve_ubicacion
            left join td_pedido on rel_uembarquepedido.fol_folio = td_pedido.Fol_folio
            left join th_cajamixta on th_cajamixta.fol_folio = rel_uembarquepedido.fol_folio
        GROUP BY descripcion
        ORDER BY FIELD (t_ubicacionembarque.status,2,3,1),ID_Embarque) AS X
        WHERE pedidos != 0;
";
$islas_query_grafica = \db()->prepare($sql);
$islas_query_grafica->execute();

$titulos_grafica_islas = array();
$valores_grafica_islas = array();
$colors_grafica_islas = array();
$borders_grafica_islas = array();

while($res = $islas_query_grafica->fetch())
{
    $titulos_grafica_islas[] = '"'.$res["descripcion"].'"';
    $valores_grafica_islas[] = $res["total_guias"];
    if ($res["status"] == 3)
    {
        $colors_grafica_islas[] = '"rgba(255, 0, 50, 0.7)"';
        $borders_grafica_islas[] = '"rgba(255, 0, 50, 1)"';
    }
    if ($res["status"] == 2)
    {
        $colors_grafica_islas[] = '"rgba(255, 230, 0, 0.7)"';
        $borders_grafica_islas[] = '"rgba(255, 230, 0, 1)"';
    }
}

/*Ubicaciones Almacenaje*/
if($almacenSeleccionadoId && !empty($almacenSeleccionadoId) && $instancia != 'dicoisa')
{
    $sql_alamcenaje = "
        SELECT 
            COUNT(u.idy_ubica) AS total_ubicaciones,
            COALESCE((
                select count(DISTINCT(cve_ubicacion)) 
                from V_ExistenciaGralProduccion
                where V_ExistenciaGralProduccion.cve_ubicacion in (
                    select idy_ubica 
                    from c_ubicacion 
                    where c_ubicacion.cve_almac in (select cve_almac from c_almacen where cve_almacenp = {$almacenSeleccionadoId})
                    and IFNULL(c_ubicacion.picking, 'N') = 'N' AND c_ubicacion.TECNOLOGIA != 'PTL'
                )
            ), 0) AS total_ocupadas
        FROM    c_ubicacion u
        WHERE  u.picking = 'N' AND u.TECNOLOGIA != 'PTL'
            AND u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$almacenSeleccionadoId})
    ";

    $almacenajeSQL = \db()->prepare($sql_alamcenaje);
    $almacenajeSQL->execute();
    $almacenajeresult = $almacenajeSQL->fetch();
    $almacenajeTotal = $almacenajeresult["total_ubicaciones"];
    $almacenajeOcupado = $almacenajeresult["total_ocupadas"];
    $almacenajeLibre = $almacenajeTotal - $almacenajeOcupado;
} 
else
{
    $almacenajeTotal = 0;
    $almacenajeOcupado = 0;
}
$almacenajeOcupado_p = ($almacenajeTotal != 0)?($almacenajeOcupado*100)/$almacenajeTotal:0;
$almacenajeLibre_p = ($almacenajeTotal != 0)?($almacenajeLibre*100)/$almacenajeTotal:0;

/*Ubicaciones Picking*/
if($almacenSeleccionadoId && !empty($almacenSeleccionadoId) && $instancia != 'dicoisa'){
    $sqlPicking = "
        SELECT 
            COUNT(u.idy_ubica) AS total_ubicaciones,
            COALESCE((
                select count(DISTINCT(cve_ubicacion)) 
                from V_ExistenciaGralProduccion
                where V_ExistenciaGralProduccion.cve_ubicacion in (
                    select idy_ubica 
                    from c_ubicacion 
                    where c_ubicacion.cve_almac in (select cve_almac from c_almacen where cve_almacenp = {$almacenSeleccionadoId})
                    and c_ubicacion.picking = 'S' AND c_ubicacion.TECNOLOGIA != 'PTL'
                )
            ), 0) AS total_ocupadas
        FROM    c_ubicacion u
        WHERE  u.picking = 'S' AND u.TECNOLOGIA != 'PTL'
            AND u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$almacenSeleccionadoId});
    ";
    $pickingSql = \db()->prepare($sqlPicking);
    $pickingSql->execute();
    $pickingresult = $pickingSql->fetch();
    $pickingTotal = $pickingresult['total_ubicaciones'];
    $pickingOcupado = $pickingresult['total_ocupadas'];
    $pickingLibre = $pickingTotal - $pickingOcupado;
} 
else
{
    $pickingTotal = 0;
    $pickingOcupado = 0;    
    $pickingLibre = 0;
}

$pickingLibre_p = ($pickingTotal != 0)?($pickingLibre*100)/$pickingTotal:0;;
$pickingOcupado_p = ($pickingTotal != 0)?($pickingOcupado*100)/$pickingTotal:0;
/*
$ubicacionesPickingDesocupadas = $ubicacionesPickingVolumen - $ubicacionesPickingOcupadas;
$ubicacionesPickingOcupadasPorcentaje = $ubicacionesPickingOcupadas != 0 ? $ubicacionesPickingOcupadas * 100 / $ubicacionesPickingVolumen : 0;
$ubicacionesPickingDesocupadasPorcentaje = $ubicacionesPickingDesocupadas != 0 ? $ubicacionesPickingDesocupadas * 100 / $ubicacionesPickingVolumen : 0;
*/
/*Ubicaciones PTL*/
if($almacenSeleccionadoId && !empty($almacenSeleccionadoId) && $instancia != 'dicoisa'){
    $sqlPTL = "
        SELECT 
            COUNT(u.idy_ubica) AS total_ubicaciones,
            COALESCE(TRUNCATE(SUM((u.num_largo / 1000) * (u.num_alto / 1000) * (u.num_ancho / 1000)),2), 0) AS volumen_ubicaciones,
            (
                select count(DISTINCT(cve_ubicacion)) 
                from V_ExistenciaGralProduccion
                where V_ExistenciaGralProduccion.cve_ubicacion in (
                    select idy_ubica 
                    from c_ubicacion 
                    where c_ubicacion.cve_almac in (select cve_almac from c_almacen where cve_almacenp = {$almacenSeleccionadoId})
                    and c_ubicacion.picking = 'S' AND c_ubicacion.TECNOLOGIA = 'PTL'
                )
            ) AS total_ocupadas
        FROM    c_ubicacion u
        WHERE  u.picking = 'S' AND u.TECNOLOGIA = 'PTL'
        AND u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$almacenSeleccionadoId})
    ";
    $ptlSql = \db()->prepare($sqlPTL);
    $ptlSql->execute();
    $ptlresult = $ptlSql->fetch();
    $ptlTotal = $ptlresult['total_ubicaciones'];
    $ptlOcupado = $ptlresult['total_ocupadas'];
    $ptlLibre = $ptlTotal - $ptlOcupado;
} 
else
{
    $ptlTotal = 0;
    $ptlOcupado = 0;    
    $ptlLibre = 0;
}

$ptlLibre_p = ($ptlTotal != 0)?($ptlLibre*100)/$ptlTotal:0;
$ptlOcupado_p = ($ptlTotal != 0)?($ptlOcupado*100)/$ptlTotal:0;
?>

<div class="modal fade" id="modalZA" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Zonas de almacenaje</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGridZA()">
                                    <button type="submit" id="buscarR" class="btn btn-sm btn-primary">Buscar</button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-tableZA"></table>
                            <div id="grid-pagerZA"></div>
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

<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    <?php
    $cliente_almacen_style = ""; 
    if($_SESSION['es_cliente'] == 1) $cliente_almacen_style = "style='display: none;'";
    ?>
    <div class="row" <?php echo $cliente_almacen_style; ?>>
        <div class="col-lg-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Almacén</h5>
                </div>
                <div class="ibox-content">
                    <div class="form-group">
                        <?php if($almacenSeleccionado): ?>
                            <label>Filtrando datos del almacen: </label>
                        <?php else: ?>
                            <label>Seleccione</label>
                        <?php endif; ?>
                        <select name="almacen" id="almacen" class="form-control">
                            <option value="">Seleccione</option>
                            <?php foreach($almacenesDisponibles as $almacen): ?>
                                <option <?php echo ($almacenSeleccionado && $almacenSeleccionado === $almacen['clave']) ? 'selected' : '' ?> value="<?php echo $almacen['clave'] ?>"><?php echo "(".$almacen['clave'].") - ".$almacen['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">Mensual</span>
                    <h5>Ordenes de Compra</h5>
                </div>
                <div class="ibox-content">
                    <div>
                        <canvas id="barChart" height="200"></canvas>
                    </div>
                    <ul class="list-unstyled">
                        <?php  foreach($ordenes as $ordenTitle => $ordenValue): ?>
                            <li><b><?php echo $ordenTitle ?>: </b><?php echo !$almacenSeleccionado ? 0 : number_format($ordenValue, 0, ",", ".") ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Monitoreo Áreas de Embarque.</h5>
                        </div>
                        <div class="ibox-content">
                            <div><canvas id="chartIslas" height="200"></canvas></div>
                            <!--cambiar a los status-->
                            <ul class="list-unstyled">
                                <?php  foreach($islas as $ordenTitle => $ordenValue): ?>
                                    <li><b><?php echo $ordenTitle ?>: </b><?php echo  $ordenValue ?></li>
                                <?php endforeach; ?>
                                <?php  foreach($total_pedidos as $ordenTitle => $ordenValue): ?>
                                    <li><b><?php echo $ordenTitle ?>: </b><?php echo  $ordenValue ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">
                                <a style="color: white;" href="/administradorpedidos/lists">Resumen</a>
                            </span>
                            <h5>Pedidos Listos por Asignar</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo !$almacenSeleccionado ? 0 : $pedidos;//number_format($pedidos, 0, ',', '.') ?>
                            </h1>
                            <div class="stat-percent font-bold text-navy">
                                <?php echo !$almacenSeleccionado ? 0 : number_format($lastPedidos, 2, ',', '.') ?>% (<?php echo Date('Y') ?>)
                                <i class="fa fa-level-up"></i>
                            </div>
                            <small>Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">Total</span>
                            <h5>Embarques efectuados</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo !$almacenSeleccionado ? 0 : number_format($embarques, 0, ',', '.') ?>
                            </h1>
                            <small>Total</small>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">Resumen</span>
                            <h5>Operadores en Línea</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo !$almacenSeleccionado ? 0 : number_format($usersOnline, 0, ',', '.') ?>
                            </h1>
                            <div class="stat-percent font-bold text-navy">
                                <?php echo !$almacenSeleccionado ? 0 : number_format($usersOnlinePorcen, 2, ',', '.') ?>% (<?php echo !$almacenSeleccionado ? 0 : $usersOffline ?>)
                                <i class="fa fa-level-up"></i>
                            </div>
                            <small>Total</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">
                            <a style="color: white;" id="ZA" href="#">Resumen</a>
                            </span><h5 style="font-size: 0.75em">Capacidad de almacenamiento</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_total, 2, '.', ',')."m3" ?>
                            </h1>
                            <small>Total</small>
                            <div><canvas id="doughnutChartAlmacenes" height="200"></canvas></div>
                            <ul class="stat-list">
                                <li>
                                    <h4 class="no-margins "><?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_ocupado, 2, '.', ',')."m3" ?></h4>
                                    <small>Ocupados</small>
                                    <div class="stat-percent"><?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_ocupado_p, 2, '.', ',') ?> <i class="fa fa-level-down text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_ocupado_p, 0, '.', ',') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h4 class="no-margins "><?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_libre, 2, '.', ',')."m3" ?></h4>
                                    <small>Vacíos</small>
                                    <div class="stat-percent"><?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_libre_p, 2, '.', ',') ?>% <i class="fa fa-bolt text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo !$almacenSeleccionado ? 0 : number_format($capacidad_libre_p, 0, '.', ',') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">
                                <a style="color: white;" href="/ubicacionalmacenaje/lists">Resumen</a>
                            </span>
                            <h5 style="font-size: 0.89em">Ubicaciones de Almacenaje</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo number_format($almacenajeTotal, 0, ',', '.') ?> 
                            </h1>
                            <small>Total</small>
                            <div><canvas id="doughnutChartUbicaciones" height="200"></canvas></div>
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($almacenajeOcupado, 2, '.', ',') ?></h2>
                                    <small>Ocupadas</small>
                                    <div class="stat-percent"><?php echo number_format($almacenajeOcupado_p, 2, '.', ',') ?> <i class="fa fa-level-down text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($almacenajeOcupado_p, 2, '.', ',') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($almacenajeLibre, 2, '.', ',') ?></h2>
                                    <small>Vacías</small>
                                    <div class="stat-percent"><?php echo number_format($almacenajeLibre_p, 2, '.', ',') ?>% <i class="fa fa-bolt text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($almacenajeLibre_p, 0, ',', '.') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>        
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">
                              <a style="color: white;" href="/ubicacionalmacenaje/lists">Resumen</a>
                            </span>
                            <h5 style="font-size: 0.89em">Ubicaciones de Picking</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo number_format($pickingTotal, 0, ',', '.') ?> 
                            </h1>
                            <small>Total</small>
                            <div>
                                <canvas id="doughnutChartUbicacionesPicking" height="200"></canvas>
                            </div>
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($pickingOcupado, 2, '.', ',') ?></h2>
                                    <small>Ocupadas</small>
                                    <div class="stat-percent"><?php echo number_format($pickingOcupado_p, 2, '.', ',') ?> <i class="fa fa-level-down text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($pickingOcupado_p, 2, '.', ',') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($pickingLibre, 2, '.', ',') ?></h2>
                                    <small>Vacías</small>
                                    <div class="stat-percent"><?php echo number_format($pickingLibre_p, 2, '.', ',') ?>% <i class="fa fa-bolt text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($pickingLibre_p, 0, ',', '.') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> 
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                        <span class="label label-info pull-right">
                          <a style="color: white;" href="/ubicacionalmacenaje/lists">Resumen</a>
                        </span>
                            <h5 style="font-size: 0.89em">Ubicaciones de PTL</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">
                                <?php echo number_format($ptlTotal, 0, ',', '.') ?> 
                            </h1>
                            <small>Total</small>
                            <div><canvas id="doughnutChartUbicacionesPTL" height="200"></canvas></div>
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($ptlOcupado, 2, '.', ',') ?></h2>
                                    <small>Ocupadas</small>
                                    <div class="stat-percent"><?php echo number_format($ptlOcupado_p , 2, '.', ',') ?> <i class="fa fa-level-down text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($ptlOcupado_p , 2, '.', ',') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins "><?php echo number_format($ptlLibre, 2, '.', ',') ?></h2>
                                    <small>Vacías</small>
                                    <div class="stat-percent"><?php echo number_format($ptlLibre_p, 2, '.', ',') ?>% <i class="fa fa-bolt text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: <?php echo number_format($ptlLibre_p, 0, ',', '.') ?>%;" class="progress-bar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 40px">
    <div class="col-lg-12">
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
    $(function($) {
        var grid_selector = "#grid-tableZA";
        var pager_selector = "#grid-pagerZA";

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
            url:'/api/almacen/lista/index2.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: $("#almacen").val()
            },
            mtype: 'POST',
            colNames:['ID','Clave','Almacén','Descripción'],
            colModel:[
                {name:'cve_almac',index:'cve_almac',width:110, editable:false, hidden:true,  sortable:false},
                {name:'clave_almacen',index:'clave_almacen',width:110, editable:false,  sortable:false},
                {name:'nombre',index:'nombre',width:150, editable:false, sortable:false},
                {name:'des_almac',index:'des_almac',width:200, editable:false, sortable:false}
            ],             
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_almac',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-tableZA").jqGrid('navGrid', '#grid-pagerZA',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var serie = rowObject[0];
            var html = '';
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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


    $(document).ready(function() {

        var almacenes = {
            type: 'doughnut',
            data: {
                labels: ['Ocupados', 'Vacíos'],
                datasets: [{
                    data: [<?php echo $capacidad_ocupado ?>, <?php echo $capacidad_libre ?>],
                    backgroundColor: [
                        'rgba(26, 179, 148, 0.7)',
                        'rgba(255, 230, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(26, 179, 148, 1)',
                        'rgba(255, 230, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        };

        var ubicaciones = {
            type: 'doughnut',
            data: {
                labels: ['Ocupadas', 'Vacíos'],
                datasets: [{
                    data: [<?php echo $almacenajeOcupado | 0 ?>, <?php echo $almacenajeLibre | 0 ?>],
                    backgroundColor: [
                        'rgba(26, 179, 148, 0.7)',
                        'rgba(255, 230, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(26, 179, 148, 1)',
                        'rgba(255, 230, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        };

        var ubicacionesPicking = {
            type: 'doughnut',
            data: {
                labels: ['Ocupadas', 'Vacíos'],
                datasets: [{
                    data: [<?php echo $pickingOcupado | 0 ?>, <?php echo $pickingLibre | 0 ?>],
                    backgroundColor: [
                        'rgba(26, 179, 148, 0.7)',
                        'rgba(255, 230, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(26, 179, 148, 1)',
                        'rgba(255, 230, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        };

        var ubicacionesPTL = {
            type: 'doughnut',
            data: {
                labels: ['Ocupadas', 'Vacíos'],
                datasets: [{
                    data: [<?php echo $ptlOcupado | 0 ?>, <?php echo $ptlLibre | 0 ?>],
                    backgroundColor: [
                        'rgba(26, 179, 148, 0.7)',
                        'rgba(255, 230, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(26, 179, 148, 1)',
                        'rgba(255, 230, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        };


        var ctx = document.getElementById("doughnutChartAlmacenes").getContext("2d");
        var ctx2 = document.getElementById("doughnutChartUbicaciones").getContext("2d");
        var ctx3 = document.getElementById("doughnutChartUbicacionesPicking").getContext("2d");
        var ctx4 = document.getElementById("doughnutChartUbicacionesPTL").getContext("2d");
        var myNewChart = new Chart(ctx, almacenes);
        var myNewChart2 = new Chart(ctx2, ubicaciones);
        var myNewChart3 = new Chart(ctx3, ubicacionesPicking);
        var myNewChart4 = new Chart(ctx4, ubicacionesPTL);

        var ordenes = {
            type: 'bar',
            data: {
                labels: ["En Proceso", "Pendientes", "Editando", "Canceladas", "Cerradas",],
                datasets: [{
                    data: [<?php echo !$almacenSeleccionado ? 0 : $ordenes['En Proceso'] ?>, 
                           <?php echo !$almacenSeleccionado ? 0 : $ordenes['Pendientes'] ?>, 
                           <?php echo !$almacenSeleccionado ? 0 : $ordenes['Editando'] ?>, 
                           <?php echo !$almacenSeleccionado ? 0 : $ordenes['Canceladas'] ?>, 
                           <?php echo !$almacenSeleccionado ? 0 : $ordenes['Cerradas'] ?>],
                    backgroundColor: [
                        'rgba(255, 230, 0, 0.7)',
                        'rgba(255, 0, 50, 0.7)',
                        'rgba(0, 0, 100, 0.7)',
                        'rgba(204, 204, 204)',
                        'rgba(26, 179, 148, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 230, 0, 1)',
                        'rgba(255, 0, 50, 1)',
                        'rgba(0, 0, 100, 1)',
                        'rgba(204, 204, 204)',
                        'rgba(26, 179, 148, 1)'
                    ],
                    borderWidth: 1
                }]
            },
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
            }
        };
      
       //Chart.defaults.global.defaultColor = "rgba(0,0,100,0.5)";
      
        var islas_cantidades = {
            type: 'bar',
            data: {
                labels: [<?php echo implode(",", $titulos_grafica_islas); ?>],
                datasets: [{
                    data: [<?php echo implode(",", $valores_grafica_islas); ?>], 
                  
                    backgroundColor: [<?php echo implode(",", $colors_grafica_islas); ?>],
                    borderColor: [<?php echo implode(",", $borders_grafica_islas); ?>],
                    borderWidth: 1
                }]
            },
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
            }
        };
      
        


        var ctx = document.getElementById("barChart").getContext("2d");
        var myNewChart = new Chart(ctx, ordenes); //
      
        var grafica = document.getElementById("chartIslas").getContext("2d");
        var myNewChart = new Chart(grafica, islas_cantidades); //

    });

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

    $("#almacen option[value='']").hide();
              $("#ZA").on("click", function(){
            $modal0 = $("#modalZA");
            $modal0.modal('show');
        });
</script>
