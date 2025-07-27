<?php
$almacenSeleccionado = isset($_GET['almacen']) && !empty($_GET['almacen']) ? $_GET['almacen'] : false;

if($almacenSeleccionado){
    $almacenSeleccionadoId = \db()->prepare("SELECT id from c_almacenp WHERE Activo = 1 AND clave = '$almacenSeleccionado'");
    $almacenSeleccionadoId->execute();
    $almacenSeleccionadoId = $almacenSeleccionadoId->fetch()['id'];
}

$sql_charset = \db()->prepare("SET NAMES UTF8MB4");
$sql_charset->execute();

$id_user = $_SESSION['id_user'];
$almacenesSql = \db()->prepare("SELECT clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac  WHERE Activo = 1 AND id_user = $id_user");
$almacenesSql->execute();
$almacenesDisponibles = $almacenesSql->fetchAll(PDO::FETCH_ASSOC);

$almacen_supedido = (!empty($almacenSeleccionadoId) && $almacenSeleccionadoId) ? " WHERE th_subpedido.cve_almac = '$almacenSeleccionadoId' AND DATE_FORMAT(th_subpedido.Hora_inicio, '%d-%m-%Y') = DATE_FORMAT(CURDATE(), '%d-%m-%Y')" : "";

$almacen_pedido = (!empty($almacenSeleccionadoId) && $almacenSeleccionadoId) ? " WHERE p.cve_almac = '$almacenSeleccionadoId'" : "";

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
$productividadSql->execute();
$productividad = $productividadSql->fetchAll(PDO::FETCH_ASSOC);


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
$embarqueSql->execute();
$embarques = $embarqueSql->fetchAll(PDO::FETCH_ASSOC);

?>

<style>
    #toast-container > div.toast {
    background-image: none !important;
    background-color: #1ab394;
}
</style>
<style type="text/css">
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-pager{
        width: 100% !important;
        max-width: 100% !important;
    }
</style>

<div class="wrapper wrapper-content" style="padding-bottom: 10px">    
    <div class="wrapper wrapper-content">
        <div class="row">
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
                                <label>Seleccione almacén</label>
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
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Visor de Pedidos</h5>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Productividad</h5>
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
                                    <th>Almacenista</th>
                                    <th>Pedido</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Final</th>
                                    <th>Tiempo Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($productividad as $prod): ?>
                                    <tr>
                                        <td><?php echo $prod["usuario"]?></td>
                                        <td><span class="label label-info"><?php echo $prod["pedido"]?></span></td>
                                        <td><?php echo $prod["inicio"]?></td>
                                        <td><?php echo $prod["final"]?></td>
                                        <td><?php echo $prod["tiempototal"]?></td>
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
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Embarques</h5>
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
                            <table class="table table-striped no-margins">
                                <thead>
                                <tr>
                                    <th>Número Pedido</th>
                                    <th>Destino</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Final</th>
                                    <th>Almacenista</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($embarques as $embarque): ?>
                                    <tr>
                                        <td><span class="label label-info"><?php echo $embarque["pedido"]?></span></td>
                                        <td><?php echo $embarque["destino"]?></td>
                                        <td><?php echo $embarque["fecha"]?></td>
                                        <td><?php echo $embarque["envio"]?></td>
                                        <td><?php echo $embarque["usuario"]?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
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
                url:'/api/administradorpedidos/lista/index.php',
                datatype: "json",
                shrinkToFit: true,
                height:'auto',
                mtype: 'POST',
                postData: {
                    action: 'loadPedidosDashboard',
                    almacen: <?php echo $almacenSeleccionadoId ?>
                },
                colNames:["Folio", "Usuario", "Fecha Inicio", "Fecha Compromiso", "Cantidad Solicitada", "Cantidad Surtida", "Porcentaje Surtido"],
                colModel:[
                    {name:'folio',index:'folio',editable:false, sortable:false, width: 100},
                    {name:'usuario',index:'usuario',editable:false, sortable:false, width: 100},
                    {name:'hora_inicio',index:'hora_inicio',editable:false, sortable:false, width: 100, align: 'center'},
                    {name:'hora_fin',index:'hora_fin',editable:false, sortable:false, width: 120, align: 'center'},
                    {name:'cantidad_solicitada',index:'cantidad_solicitada',editable:false, sortable:false, width: 100, align: 'right'},
                    {name:'cantidad_surtida',index:'cantidad_surtida',editable:false, sortable:false, width: 100, align: 'right'},
                    {name:'porcentaje_surtido',index:'porcentaje_surtido',editable:false, sortable:false, width: 100, align: 'right'},
                ],
                rowNum:30,
                rowList:[30,40,50],
                pager: pager_selector,
                sortname: 'ID_Pedido',
                viewrecords: true,
                sortorder: "desc",
                loadComplete: function(data){console.log("OK data", data);}
                //autowidth:true
            });

            // Setup buttons
            $(grid_selector).jqGrid('navGrid', pager_selector,
                {edit: false, add: false, del: false, search: false},
                {height: 200, reloadAfterSubmit: true}
            );


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
$("#almacen option[value='']").hide();

        function ReloadGrid() {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: {
                    action: 'loadPedidosDashboard',
                    almacen: <?php echo $almacenSeleccionadoId ?>
                }, datatype: 'json', page : 1})
                .trigger('reloadGrid',[{current:true}]);
            resizeGrid();
        }
        function resizeGrid(){
            $("#grid-table").jqGrid("setGridWidth", $("#grid").width(), true);
        }
        (function($){
            setTimeout(resizeGrid, 100)
            //setInterval(ReloadGrid, 10000);
        })(jQuery);
    <?php endif; ?>
</script>