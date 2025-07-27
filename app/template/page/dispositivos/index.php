<?php
//$almacenSeleccionado = isset($_GET['almacen']) && !empty($_GET['almacen']) ? $_GET['almacen'] : false;
$almacenSeleccionado = $_SESSION['cve_almacen'];
if($almacenSeleccionado){
    $almacenSeleccionadoId = \db()->prepare("SELECT id from c_almacenp WHERE Activo = 1 AND clave = '$almacenSeleccionado'");
    $almacenSeleccionadoId->execute();
    $almacenSeleccionadoId = $almacenSeleccionadoId->fetch()['id'];
}

$licencias = \db()->prepare("SELECT IFNULL(FROM_BASE64(L_Web), 0) L_Web, IFNULL(FROM_BASE64(L_Mobile), 0) L_Mobile FROM t_license");
$licencias->execute();
$licencias = $licencias->fetch();
$licencias_web = $licencias['L_Web'];
$licencias_apk = $licencias['L_Mobile'];

$licencias_almacen_apk = \db()->prepare("SELECT IFNULL(No_Licencias, 0) as num_lic_apk FROM c_almacenp WHERE id = '$almacenSeleccionadoId'");
$licencias_almacen_apk->execute();
$licencias_almacen_apk = $licencias_almacen_apk->fetch();
$licencias_apk_almacen = $licencias_almacen_apk['num_lic_apk'];


//$almacenesSql = \db()->prepare("SELECT clave, nombre FROM c_almacenp WHERE Activo = 1");
$id_user = $_SESSION['id_user'];
$almacenesSql = \db()->prepare("SELECT clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac  WHERE Activo = 1 AND id_user = $id_user");
$almacenesSql->execute();
$almacenesDisponibles = $almacenesSql->fetchAll(PDO::FETCH_ASSOC);

$almacen_supedido = (!empty($almacenSeleccionadoId) && $almacenSeleccionadoId) ? " WHERE th_subpedido.cve_almac = '$almacenSeleccionadoId' AND DATE_FORMAT(th_subpedido.Hora_inicio, '%d-%m-%Y') = DATE_FORMAT(CURDATE(), '%d-%m-%Y')" : "";

$almacen_pedido = (!empty($almacenSeleccionadoId) && $almacenSeleccionadoId) ? " WHERE p.cve_almac = '$almacenSeleccionadoId'" : "";
/*
$productividadSql = \db()->prepare("
    SELECT 
        IFNULL(c_usuario.nombre_completo, '--') AS usuario, 
        td_subpedido.fol_folio AS pedido, 
        IFNULL(DATE_FORMAT(th_subpedido.Hora_inicio,'%d-%m-%Y %H:%i:%s'), ' -- ') AS inicio, 
        IFNULL(DATE_FORMAT(th_subpedido.Hora_Final,'%d-%m-%Y %H:%i:%s'), ' -- ') AS final, 
        TIMEDIFF(th_subpedido.Hora_Final, th_subpedido.Hora_inicio) AS tiempototal 
    FROM th_subpedido 
    LEFT JOIN td_subpedido ON th_subpedido.fol_folio = td_subpedido.fol_folio 
    LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_subpedido.Cve_articulo 
    LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_subpedido.cve_usuario  {$almacen_supedido} 
    GROUP BY th_subpedido.cve_usuario, th_subpedido.fol_folio 
    ORDER BY th_subpedido.fol_folio DESC, c_usuario.des_usuario ASC 
    LIMIT 0, 10;");
*/
$usuarios_onlineSql = \db()->prepare("
    SELECT DISTINCT c.id_user, c.cve_usuario, c.nombre_completo, MAX(DATE_FORMAT(t.last_updated, '%d-%m-%Y %H:%m:%i')) AS Fecha
    FROM c_usuario c, users_online t
    WHERE c.id_user = t.id_usuario AND (c.cve_usuario != 'wmsmaster' OR c.cve_usuario != 'jlopez') AND DATE_FORMAT(t.last_updated, '%Y-%m-%d') = CURDATE()
    GROUP BY t.id_usuario");

$usuarios_onlineSql->execute();
$usuarios_online = $usuarios_onlineSql->fetchAll(PDO::FETCH_ASSOC);

$productividadSql = \db()->prepare("
    SELECT c.cve_usuario, c.nombre_completo, DATE_FORMAT(t.Fecha, '%d-%m-%Y %H:%m:%i') as Fecha, t.IMEI 
    FROM c_usuario c, t_eda_sessions t
    WHERE c.cve_usuario = t.Usuario");

$productividadSql->execute();
$productividad = $productividadSql->fetchAll(PDO::FETCH_ASSOC);

/*
$embarqueSql = \db()->prepare("
    SELECT 
        #IFNULL(DATE_FORMAT(th_ordenembarque.fecha,'%d-%m-%Y %H:%i:%s'),' -- ') AS fecha,
        IFNULL(DATE_FORMAT(sb.HIE,'%d-%m-%Y %H:%i:%s'),' -- ') AS fecha,
        #IFNULL(DATE_FORMAT(th_ordenembarque.FechaEnvio,'%d-%m-%Y %H:%i:%s'),' -- ') AS envio,
        IFNULL(DATE_FORMAT(sb.HFE,'%d-%m-%Y %H:%i:%s'),' -- ') AS envio,
        th_ordenembarque.destino AS destino, 
        IFNULL(c_usuario.nombre_completo, '--') AS usuario, 
        td_ordenembarque.Fol_folio AS pedido 
    FROM th_ordenembarque 
    LEFT JOIN td_ordenembarque ON th_ordenembarque.ID_OEmbarque = td_ordenembarque.ID_OEmbarque 
    LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_ordenembarque.cve_usuario 
    LEFT JOIN th_subpedido sb ON sb.fol_folio = td_ordenembarque.Fol_folio AND (sb.status = 'L' OR sb.status = 'C') 
    ORDER BY sb.HIE DESC
    LIMIT 0, 10;");
*/
$embarqueSql = \db()->prepare("
SELECT c.cve_usuario, c.nombre_completo, DATE_FORMAT(u.fecha_inicio, '%d-%m-%Y %H:%m:%i') AS inicio_sesion, DATE_FORMAT(u.fecha_cierre, '%d-%m-%Y %H:%m:%i') AS cierre_sesion, u.IP_Address
FROM users_bitacora u, c_usuario c
WHERE u.cve_usuario = c.cve_usuario AND u.cve_almacen = '$almacenSeleccionado' ORDER BY u.id DESC");
/*
if(isset($almacenSeleccionado) && !empty($almacenSeleccionado)){
    $sql = "
    SELECT 
        #IFNULL(DATE_FORMAT(th_ordenembarque.fecha,'%d-%m-%Y %H:%i:%s'),' -- ') AS fecha,
        IFNULL(DATE_FORMAT(sb.HIE,'%d-%m-%Y %H:%i:%s'),' -- ') AS fecha,
        #IFNULL(DATE_FORMAT(th_ordenembarque.FechaEnvio,'%d-%m-%Y %H:%i:%s'),' -- ') AS envio,
        IFNULL(DATE_FORMAT(sb.HFE,'%d-%m-%Y %H:%i:%s'),' -- ') AS envio,
        th_ordenembarque.destino AS destino, 
        IFNULL(c_usuario.nombre_completo, '--') AS usuario, 
        td_ordenembarque.Fol_folio AS pedido 
    FROM th_ordenembarque 
    LEFT JOIN td_ordenembarque ON th_ordenembarque.ID_OEmbarque = td_ordenembarque.ID_OEmbarque 
    LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_ordenembarque.cve_usuario 
    LEFT JOIN th_subpedido sb ON sb.fol_folio = td_ordenembarque.Fol_folio AND (sb.status = 'L' OR sb.status = 'C') 
    WHERE c_usuario.cve_usuario IN (SELECT cve_usuario FROM trel_us_alm WHERE cve_almac = '$almacenSeleccionado') 
    ORDER BY sb.HIE DESC
    LIMIT 0, 10";
    $embarqueSql = \db()->prepare($sql);
}
*/
$embarqueSql->execute();
$embarques = $embarqueSql->fetchAll(PDO::FETCH_ASSOC);

?>


<style>
    #toast-container > div.toast {
    background-image: none !important;
    background-color: #1ab394;
}
</style>
<?php /* ?>, #grid-table, #grid-pager <?php */ /* ?>
<style type="text/css">
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable{
        width: 100% !important;
        max-width: 100% !important;
    }
</style>
<?php */ ?>

<div class="wrapper wrapper-content" style="padding-bottom: 10px">    
    <div class="wrapper wrapper-content">
        <div class="row">
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
                                    <option <?php echo ($almacenSeleccionado && $almacenSeleccionado === $almacen['clave']) ? 'selected' : '' ?> value="<?php echo $almacen['clave'] ?>"><?php echo "(".$almacen["clave"].") - ".$almacen['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        <div class="col-md-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">

                    <?php 
                    /*
                    ?>
                    <span class="label label-success pull-right">
                       <a style="color: white;" href="/articulos/lists?almacen=<?php echo $_GET["almacen"]; ?>">Resumen</a>
                    </span>
                    <?php 
                    */
                    ?>
                <span class="label label-info pull-right">
                  Total
                </span>
                    <h5 id="name">Licencias Web:</h5>
                </div>
                <div class="ibox-content" style="padding: 30px;">
                    <?php /* ?><div class="stat-percent font-bold text-danger" id="inactive">0 <i class="fa fa-thumbs-down"></i></div>
                    <small class="text-success" id="active">0 <i class="fa fa-thumbs-up"></i></small><?php */ 
                    if($_SESSION['cve_usuario'] != 'wmsmaster')
                    {
                    ?>
                    <h3 class="no-margins pull-right" id="quantity"><?php echo $licencias_web; ?></h3>
                    <?php 
                    }
                    else
                    {
                    ?>
                    <div style="display: inline-block;">
                        <input type="button" class="btn btn-primary" name="cambiar_licencia_web" value="Cambiar Número Licencias" onclick="ConfirmarCambio('web')" style="display: inline-block;font-size: 13px;">
                        <input type="number" name="cantidad_licencias_web" id="cantidad_licencias_web" class="input-sm" style="display: inline-block; width: 60px;" value="<?php echo $licencias_web; ?>">
                    </div>
                    <?php 
                    }
                    ?>

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
                        Licencias Móviles
                    </h5>
                </div>
                <div class="ibox-content"  style="padding: 30px;">
                    <?php 
                    /*
                    ?>
                    <h3 class="no-margins">
                        <?php echo !$almacenSeleccionado ? 0 :  $lotesVencidosTexto?>
                    </h3>
                    <div class="stat-percent font-bold text-success">
                        <?php echo !$almacenSeleccionado ? 0 :  number_format($lotesVencidosPorcen, 2, ',', '.') ?>%
                    </div>
                    <small>
                        Último trimestre (<a id="vencidos" href="#">Ver más</a>)
                    </small>
                    <?php 
                    */
                     ?>
                    <?php  
                        if($_SESSION['cve_usuario'] != 'wmsmaster')
                        {
                        ?>
                        <h3 class="no-margins pull-right" id="quantity"><?php echo $licencias_apk_almacen; ?></h3>
                        <?php 
                        }
                        else
                        {
                        ?>
                        <div style="display: inline-block;">
                            <input type="button" class="btn btn-primary" name="cambiar_licencia_apk" value="Cambiar Número Licencias" onclick="ConfirmarCambio('apk')" style="display: inline-block;font-size: 13px;">
                            <input type="number" name="cantidad_licencias_apk" id="cantidad_licencias_apk" class="input-sm" style="display: inline-block; width: 60px;" value="<?php echo $licencias_apk_almacen; ?>">
                        </div>
                        <?php 
                        }
                    ?>
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
                        Impresoras
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
        /*
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Usuarios Administrativos</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                     <div class="ibox-content" id="grid">
                        <?php if(!$almacenSeleccionado): ?>
                            <div class="alert alert-success">No hay registros para mostrar</div>
                        <?php else: ?>
                              <div class="table-responsive">
                                <div class="jqGrid_wrapper" style="min-width: 1000px;">
                                  <table id="grid-table"></table>
                                  <div id="grid-pager"></div>
                                </div>  
                              </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        */
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Dispositivos Móviles</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content table-responsive">
                        <?php if(count($productividad) <= 0 || !$almacenSeleccionado): ?>
                            <div class="alert alert-success">No hay registros para mostrar</div>
                        <?php else: ?>
                            <table class="table table-striped no-margins">
                                <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <!--<th>Dirección IP</th>-->
                                    <th>Inicio de sesión</th>
                                    <!--<th>Fin de Sesión</th>-->
                                    <th>Dispositivo</th>
                                    <!--<th>No. Licencia</th>-->
                                    <!--<th>Fecha Emisión</th>
                                    <th>Fecha Expiración</th>-->
                                    <th>Liberar</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($productividad as $prod): ?>
                                    <tr id="user_id_disp<?php echo $prod["cve_usuario"];?>">
                                        <td><?php echo $prod["cve_usuario"]?></td>
                                        <td><?php echo $prod["nombre_completo"]?></td>
                                        <?php /* ?><td><span class="label label-info"><?php echo $prod["pedido"]?></span></td><?php */ ?>
                                        <td><?php echo $prod["Fecha"]?></td>
                                        <?php /* ?><td><?php echo $prod["final"]?></td><?php */ ?>
                                        <td><?php echo $prod["IMEI"]?></td>
                                        <!--<td></td>
                                        <td></td>
                                        <td></td>-->
                                        <?php //if(($_SESSION['cve_usuario'] == 'wmsmaster' || $_SESSION['cve_usuario'] == 'jlopez' || $_SESSION['cve_usuario'] == 'jvillanueva' || $_SESSION['cve_usuario'] == 'JTeran') && $_SESSION['cve_usuario'] != $user_o['cve_usuario']){ ?><td><input type="button" class="btn btn-primary btn-small btn_liberar_dispositivo permiso_eliminar" data-usuario="<?php echo $prod["cve_usuario"]; ?>" value="Liberar"></td><?php //} ?>
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
            <div class="col-lg-12" style="display: none;">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Usuarios APK</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content table-responsive">
                        <?php if(count($embarques) <= 0 || !$almacenSeleccionado): ?>
                            <div class="alert alert-success">No hay registros para mostrar</div>
                        <?php else: ?>
<?php 
/*
?>
                            <table class="table table-striped no-margins">
                                <thead>
                                <tr>
                                    <th>Clave Usuario</th>
                                    <th>Usuario</th>
                                    <th>Inicio de sesión</th>
                                    <th>Cierre de sesión</th>
                                    <th>Dirección IP</th>
                                    <th><!--Dispositivo--></th>
                                    <th><!--No. Licencia--></th>
                                    <th><!--Fecha Emisión--></th>
                                    <th><!--Fecha Expiración--></th>
                                </tr>
                                </thead>
                                <tbody>

                                foreach($embarques as $prod): ?>
                                    <tr>
                                        <td><?php echo $prod["cve_usuario"]?></td>
                                        <td><?php echo $prod["nombre_completo"]?></td>
                                        <td><?php echo $prod["inicio_sesion"]?></td>
                                        <td><?php echo $prod["cierre_sesion"]?></td>
                                        <td><?php echo $prod["IP_Address"]?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
<?php 
*/
?>
                              <div class="table-responsive">
                                <div class="jqGrid_wrapper" >
                                  <table id="grid-table"></table>
                                  <div id="grid-pager"></div>
                                </div>  
                              </div>


                        <?php endif; ?>
                    </div>
                </div>
            </div>



            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Usuarios WEB</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content table-responsive">
                            <table class="table table-striped no-margins">
                                <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Inicio de sesión</th>
                                    <?php //if($_SESSION['cve_usuario'] == 'wmsmaster' || $_SESSION['cve_usuario'] == 'jlopez' || $_SESSION['cve_usuario'] == 'JTeran'){ ?><th class="permiso_eliminar">Liberar</th><?php //} ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($usuarios_online as $user_o): ?>
                                    <tr id="user_id<?php echo $user_o["id_user"];?>">
                                        <td><?php echo $user_o["cve_usuario"];?></td>
                                        <td><?php echo $user_o["nombre_completo"];?></td>
                                        <td><?php echo $user_o["Fecha"];?></td>
                                        <?php //if(($_SESSION['cve_usuario'] == 'wmsmaster' || $_SESSION['cve_usuario'] == 'jlopez' || $_SESSION['cve_usuario'] == 'JTeran') && $_SESSION['cve_usuario'] != $user_o['cve_usuario']){ 
                                            ?><td><input type="button" class="btn btn-primary btn-small btn_liberar permiso_eliminar" data-usuario="<?php echo $user_o['id_user']; ?>" value="Liberar"></td><?php //} ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="theme-config" id="cogs">
    <div class="theme-config-box show">
        <div class="spin-icon">
            <i class="fa fa-cogs fa-spin"></i>
        </div>
        <div class="skin-settings">
            <div class="title">
            <small style="text-transform: none;font-weight: 400">
                .
            </small></div>
            <div class="setings-item">
            </div>                  
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="js/jquery-2.1.1.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<?php 
/*
  <div id='toast'>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
  <script src="js/plugins/toastr/toastr.min.js"></script>


  <script>
    //$(document).ready(function() {

    $(function() {

        toastr.success('Bienvenido a WMS.', 'Bienvenido', {
            timeOut: 5000,
            extendedTimeout: 5000,
            closeButton: true,
            progressBar: true,
            showDuration: 1000,
            hideDuration: 400,
            preventDuplicates: true
        });
        
                setTimeout("$('#cogs').fadeOut()", 5000);

    });

  </script>
</div>

*/
?>

<div class="row">
    <div class="col-lg-12">
        <div class="footer">
            <div>
                <strong>Copyright </strong>  AssistPro ADL ®  Todos los derechos reservados. &copy; 2017
            </div>
        </div>
    </div>
</div>


<!-- Flot -->
<script src="js/plugins/flot/jquery.flot.js"></script>
<script src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="js/plugins/flot/jquery.flot.spline.js"></script>
<script src="js/plugins/flot/jquery.flot.resize.js"></script>
<script src="js/plugins/flot/jquery.flot.pie.js"></script>

<!-- Peity -->
<script src="js/plugins/peity/jquery.peity.min.js"></script>
<script src="js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="js/inspinia.js"></script>
<script src="js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- GITTER -->
<script src="js/plugins/gritter/jquery.gritter.min.js"></script>

<!-- Sparkline -->
<script src="js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr -->

<!-- Morris -->
<script src="js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="js/plugins/morris/morris.js"></script>

<!-- d3 and c3 charts -->
<script src="js/plugins/d3/d3.min.js"></script>
<script src="js/plugins/c3/c3.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>


<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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
</script>
<script type="text/javascript">
    <?php if($almacenSeleccionado): ?>

        $(function() {
            var grid_selector = "#grid-table";
            var pager_selector = "#grid-pager";

            //resize to fit page size
            /*
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
            */
            $(grid_selector).jqGrid({
                //url:'/api/administradorpedidos/lista/index.php',
                url:'/api/usuarios/lista/index.php',
                datatype: "json",
                shrinkToFit: true,
                width: 'auto',
                height:'auto',
                mtype: 'POST',
                postData: {
                    action: 'loadUsuariosBitacora',
                    almacen: '<?php echo $almacenSeleccionado; ?>'
                },
                colNames:["Clave Usuario", "Usuario", "Inicio de Sesión", "Cierre de Sesión", "Dirección IP", "Liberar"],
                colModel:[
                    {name:'cve_usuario',index:'cve_usuario',editable:false, sortable:false, width: 150},
                    {name:'usuario',index:'usuario',editable:false, sortable:false, width: 200},
                    {name:'inicio_sesion',index:'inicio_sesion',editable:false, sortable:false, width: 150},
                    {name:'cierre_sesion',index:'cierre_sesion',editable:false, sortable:false, width: 150},
                    {name:'direccion_ip',index:'direccion_ip',editable:false, sortable:false, width: 130},
                    {name: 'liberar',index: '',width: 110,fixed: true,sortable: false,resize: false,formatter: imageFormat,align: "center"  }, 
                ],
                rowNum:10,
                rowList:[10,30,40,50],
                pager: pager_selector,
                sortname: 'cve_usuario',
                viewrecords: true,
                sortorder: "desc",
                loadError: function(data){
                    console.log("ERROR: ", data);
                }
                //autowidth:true
            });

            // Setup buttons
            $(grid_selector).jqGrid('navGrid', pager_selector,
                {edit: false, add: false, del: false, search: false},
                {height: 200, reloadAfterSubmit: true}
            );

            function imageFormat(cellvalue, options, rowObject) {
                var folio = rowObject.orden;
                var html = '';
                var usuario = rowObject[0];

                console.log("usuario liberar x= ", usuario);

                html = '<input type="button" class="btn btn-primary btn-small" onclick="btn_liberar_apk(\'' + usuario + '\')" value="Liberar">';


                return html;
            }

            $(window).triggerHandler('resize.jqGrid');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });


/*
        toastr.success('Bienvenido a WMS.', 'Bienvenido', {
            timeOut: 5000,
            extendedTimeout: 5000,
            closeButton: true,
            progressBar: true,
            showDuration: 1000,
            hideDuration: 400,
            preventDuplicates: true
        });
        
                setTimeout("$('#cogs').fadeOut()", 5000);
*/

        });



        $(".btn_liberar").click(function()
        {
            console.log("usuario liberar = ", $(this).data('usuario'));
            var id_u = $(this).data('usuario');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    usuario: $(this).data('usuario'),
                    action: 'liberar_usuario'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/usuarios/lista/index.php',
                success: function(data) {
                    console.log("SUCCESS = ", data, id_u);
                    $("#user_id"+id_u).remove();//css("display", "none");
                },
                error: function(res){
                    console.log("ERROR ABC",res);
                }
            });

        });

        $(".btn_liberar_dispositivo").click(function()
        {
            console.log("usuario liberar = ", $(this).data('usuario'));
            var id_u = $(this).data('usuario');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    usuario: $(this).data('usuario'),
                    action: 'liberar_usuario_disp'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/usuarios/lista/index.php',
                success: function(data) {
                    console.log("SUCCESS = ", data, id_u);
                    $("#user_id_disp"+id_u).remove();//css("display", "none");
                },
                error: function(res){
                    console.log("ERROR ABC",res);
                }
            });

        });

        function ConfirmarCambio(tipo)
        {
                swal({
                    title: "¿Está seguro que desea cambiar el número de licencias en "+tipo+"?",
                    text: "",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Si",
                    cancelButtonText: "Cancelar",
                    closeOnConfirm: false
                    },
                    function(){
                        
                        ActualizarLicencias(tipo);
                           
                });
        }

        function ActualizarLicencias(tipo_licencia)
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    tipo: tipo_licencia,
                    almacen: $("#almacen").val(),
                    cantidadweb: $("#cantidad_licencias_web").val(),
                    cantidadapk: $("#cantidad_licencias_apk").val(),
                    action: 'actualizar_licencia'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/configuraciongeneral/update/index.php',
                success: function(data) {
                    swal("Éxito", "Número de licencias "+tipo_licencia+" cambiado con éxito", "success");
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }

        function btn_liberar_apk(cve_usuario)
        {
            console.log("usuario liberar apk = ", cve_usuario);
            //var id_u = $(this).data('usuario');
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    usuario: cve_usuario,
                    action: 'liberar_usuario_apk'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/usuarios/lista/index.php',
                success: function(data) {
                    console.log("data = ", data);
                    window.location.reload();
                },
                error: function(res){
                    console.log("ERROR ABC",res);
                }
            });

        }

        function ReloadGrid() {
            console.log("ReloadGrid()");
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: {
                    action: 'loadUsuariosBitacora',
                    almacen: '<?php echo $almacenSeleccionado; ?>'
                }, datatype: 'json', page : 1})
                .trigger('reloadGrid',[{current:true}]);


            //resizeGrid();
        }
        function resizeGrid(){
            $("#grid-table").jqGrid("setGridWidth", $("#grid").width(), true);
        }
        (function($){
            //setTimeout(resizeGrid, 100)
            //setInterval(ReloadGrid, 10000);
            //ReloadGrid();
            //console.log("permiso_consultar = ", $("#permiso_consultar").val());
            //console.log("permiso_registrar = ", $("#permiso_registrar").val());
            //console.log("permiso_editar = ", $("#permiso_editar").val());
            //console.log("permiso_eliminar = ", $("#permiso_eliminar").val());
        })(jQuery);
    <?php endif; ?>
</script>