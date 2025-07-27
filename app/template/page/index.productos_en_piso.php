<?php
$almacenSeleccionado = isset($_GET['almacen']) ? $_GET['almacen'] : false;
$zonaSeleccionada = isset($_GET['zona']) ? $_GET['zona'] : false;

if($almacenSeleccionado){
    $almacenSeleccionadoId = \db()->prepare("SELECT id from c_almacenp WHERE Activo = 1 AND clave = '$almacenSeleccionado'");
    $almacenSeleccionadoId->execute();
    $almacenSeleccionadoId = $almacenSeleccionadoId->fetch()['id'];
}


$sqlZonas = "SELECT 
    cve_almac AS clave,
    des_almac AS descripcion
 FROM c_almacen
WHERE cve_almacenp = '{$almacenSeleccionado}' AND cve_almac IN 
    (SELECT DISTINCT cve_almac 
        FROM c_ubicacion WHERE idy_ubica IN 
            (SELECT DISTINCT cve_ubicacion FROM V_ExistenciaGralProduccion WHERE Existencia > 0))";
                
if($zonaSeleccionada){
    $zonaSeleccionadaId = \db()->prepare($sqlZonas);
    $zonaSeleccionadaId->execute();
    $zonaSeleccionadaId = $zonaSeleccionadaId->fetch()['clave'];
}

$id_user = $_SESSION['id_user'];

$sql = \db()->prepare("SELECT id, clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac  WHERE Activo = 1 AND id_user = $id_user ");
$sql->execute();
$almacenesDisponibles = $sql->fetchAll(PDO::FETCH_ASSOC);


$sql = \db()->prepare($sqlZonas);
$sql->execute();
$zonasDisponibles = $sql->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT
            ca.cve_articulo,
            ca.des_articulo,
            SUM(ta.CantidadRecibida) AS Total
        FROM
            c_articulo ca
        INNER JOIN td_entalmacen ta ON ca.cve_articulo = ta.cve_articulo
        LEFT JOIN c_lotes on ca.cve_articulo=c_lotes.cve_articulo
        WHERE
            YEAR(STR_TO_DATE(c_lotes.CADUCIDAD,'%d-%m-%Y')) = YEAR (CURDATE())";

if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND ca.cve_almac = '$almacenSeleccionadoId'";
}

$sql .= "
        GROUP BY
            ca.cve_articulo
        ORDER BY
            Total DESC
        LIMIT 10";
$productoMayorMovimientoSql = \db()->prepare($sql);
$productoMayorMovimientoSql->execute();
$productoMayorMovimiento = $productoMayorMovimientoSql->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT
            ca.cve_articulo,
            ca.des_articulo,
            SUM(ta.CantidadRecibida) AS Total
        FROM
            c_articulo ca
        INNER JOIN td_entalmacen ta ON ca.cve_articulo = ta.cve_articulo
        LEFT JOIN c_lotes on ca.cve_articulo=c_lotes.cve_articulo
        WHERE
            YEAR(STR_TO_DATE(c_lotes.CADUCIDAD,'%d-%m-%Y')) = YEAR (CURDATE())";
if($almacenSeleccionado && !empty($almacenSeleccionado)){
    $sql .= " AND ca.cve_almac = '$almacenSeleccionadoId'";
}

$sql .= "
        GROUP BY
            ca.cve_articulo
        ORDER BY
            Total ASC
        LIMIT 10";
$productoMenorMovimientoSql = \db()->prepare($sql);
$productoMenorMovimientoSql->execute();
$productoMenorMovimiento = $productoMenorMovimientoSql->fetchAll(PDO::FETCH_ASSOC);

$almacenLote = (!empty($almacenSeleccionadoId)) ? " AND l.cve_articulo IN (SELECT cve_articulo FROM c_articulo WHERE cve_almac = '$almacenSeleccionadoId')" : "";

$sql = "SELECT 
                COUNT(l.LOTE) as cuenta    
        FROM c_lotes l
        where l.Activo = '1' {$almacenLote}";

$lotesTotalesSql = \db()->prepare($sql);
$lotesTotalesSql->execute();
$lotesTotales = $lotesTotalesSql->fetch()['cuenta'];

$sql = "SELECT 
            COUNT(l.LOTE) as cuenta
        FROM c_lotes l
        where l.Activo = '1' AND STR_TO_DATE(l.CADUCIDAD,'%d-%m-%Y') < NOW() {$almacenLote}";

$lotesVencidosSql = \db()->prepare($sql);
$lotesVencidosSql->execute();
$lotesVencidos = $lotesVencidosSql->fetch()['cuenta'];
$lotesVencidosPorcen = $lotesVencidos != 0 ? $lotesVencidos  * 100 / $lotesTotales : 0;

$sql = "SELECT 
            COUNT(l.LOTE) as cuenta
        FROM c_lotes l
        where l.Activo = '1' AND STR_TO_DATE(l.CADUCIDAD,'%d-%m-%Y') > NOW() {$almacenLote}";

$lotesVencerSql = \db()->prepare($sql);
$lotesVencerSql->execute();
$lotesVencer = $lotesVencerSql->fetch()['cuenta'];
$lotesVencerPorcen = $lotesVencer != 0 ? $lotesVencer * 100 / $lotesTotales : 0;

$enlaceLotes = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."lotes/lists?m=10";

?>

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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>



<div class="wrapper wrapper-content" style="padding-bottom: 10px">
    
    <div class="row">
        <div class="col-md-6 col-lg-4">
            <div class="ibox">
                <div class="ibox-title"><h5>Almacén</h5></div>
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
                                <option <?php echo ($almacenSeleccionado && $almacenSeleccionado === $almacen['id']) ? 'selected' : '' ?> value="<?php echo $almacen['id'] ?>"><?php echo "(".$almacen['clave'].") - ".$almacen['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="almacen" value="<?php echo $almacenSeleccionadoId?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="ibox">
                <div class="ibox-title"><h5>Zona de alacenamiento</h5></div>
                <div class="ibox-content">
                    <div class="form-group">
                    <?php if($almacenSeleccionado): ?>
                            <label>Filtrando datos del zona: </label>
                        <?php else: ?>
                            <label>Seleccione zona</label>
                        <?php endif; ?>                        
                        <select name="zona" id="zona" class="form-control">
                            <option value="">Seleccione</option>
                            <?php foreach($zonasDisponibles as $value): ?>
                                <option <?php echo ($zonaSeleccionada && $zonaSeleccionada === $value['clave']) ? 'selected' : '' ?> value="<?php echo $value['clave'] ?>"><?php echo $value['clave'].' '.$value['descripcion'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div><!--./row-->

    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">
					   <a style="color: white;" href="#"></a>
					</span>
                    <h5 id="name">Días en Patio</h5>
                </div>
                <div class="ibox-content">
                    
                                             
                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                   
                    
                    <!--<div class="stat-percent font-bold text-danger" id="inactive">0 <i class="fa fa-thumbs-down"></i></div>
                    <small class="text-success" id="active">0 <i class="fa fa-thumbs-up"></i></small>-->
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">Total</span>
                    <h5>Valor de Órdenes</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                <span class="label label-info pull-right">Total</span>
                    <h5>Órdenes disponibles</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Racks Disponibles</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Órdenes liberadas para carga</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Administración de Ubicaciones</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Ubicaciones exactas de toda la orden</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" id="quantity">0</h1>
                </div>
            </div>
        </div>


    </div>
</div>

<script>
    $(function($) { 
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
            url:'/api/dashboard/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            postData: {
                action : 'diasEnPatio',
                almacen : $("#almacen").val(),
                zona : $("#zona").val(),
            },
            mtype: 'GET',
            colNames:['Clave','Artículos','Días'],
            colModel:[
                {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'articulo',index:'articulo',width:400, editable:false, sortable:false},
                {name:'dias',index:'dias',width:100, editable:false, sortable:false, resizable: false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'articulo',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
       

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });

        //ReloadGrid();

    });

    function ReloadGrid() {
        $('#grid-table')
            .jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    almacen : $("#almacen").val(),
                    articulo : $("#articulo").val(),
                    zona : $("#zona").val(),
                }, datatype: 'json',
                page : 1
            })
            .trigger('reloadGrid',[{current:true}]);
    }


</script>

<script>

    $( document ).ready(function() {
    	<?php if($almacenSeleccionado): ?>
        	request();
       	<?php endif; ?>
    });

    $(document).ready(function(){
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
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
                        element.value = data.codigo.id;
                        $('#almacen').trigger('change');
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
    }
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
            colNames:['id','Clave de Artículo','Descripción','Lote','Caducidad','Ubicacion','Cantidad'],
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable:false,hidden:true},
               
                {name:'cve_articulo',index:'LOTE',width:150, editable:false, sortable:false},
                {name:'des_articulo',index:'des_articulo',width:250, editable:false, sortable:false},
                 {name:'LOTE',index:'LOTE',width:150, editable:false, sortable:false},
                {name:'CADUCIDAD',index:'CADUCIDAD',width:100, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion',width:100, editable:false, sortable:false},
                {name:'existencia',index:'existencia',width:100, editable:false, sortable:false},

            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_articulo',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: almacenPrede()
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
            url:'/api/lotes/lista/porvencer.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height:'auto',
            postData: {
                almacen: $("#almacen").val()
            },
            mtype: 'POST',
         colNames:['id','Clave de Artículo','Descripción','Lote','Caducidad','Ubicacion','Cantidad'],
            colModel:[
                {name:'id',index:'id', width:60, sorttype:"int", editable:false,hidden:true},
               
                {name:'cve_articulo',index:'LOTE',width:150, editable:false, sortable:false},
                {name:'des_articulo',index:'des_articulo',width:250, editable:false, sortable:false},
                 {name:'LOTE',index:'LOTE',width:150, editable:false, sortable:false},
                {name:'CADUCIDAD',index:'CADUCIDAD',width:100, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion',width:100, editable:false, sortable:false},
                {name:'existencia',index:'existencia',width:100, editable:false, sortable:false},
                
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_articulo',
            viewrecords: true,
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
                almacen: almacen
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/resumenejecutivo/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#quantity").text(data.total.toLocaleString().replace(",","."));
                   $("#active").text("Activos: "+data.activo.toLocaleString().replace(",","."));
                   $("#inactive").text("Inactivos: "+data.inactivo.toLocaleString().replace(",","."));
                    $("#name").text(data.articulo);
                }
            }
        });
    }

</script>
<script>

    $('select[name="zona"]').on('change', function(e){
        /*var url = new URL(window.location.href),
            params = new URLSearchParams(url.search);
        if( ! params.has('almacen')){
            params.append('almacen', e.target.value);
        }else{
            params.set('almacen', e.target.value)
        }
        url.search = params.toString();
        */
        almancen = $('#almacen').val();
        zona = $('#zona').val();
        window.location.href = '/dashboard/productos-en-piso?almacen='+almancen+'&zona='+zona;
    });


    $("#porvencer").on("click", function(){
        $modal0 = $("#modalPV");
        $modal0.modal('show');
    });
  

    $("#vencidos").on("click", function(){
        $modal0 = $("#modalLV");
        $modal0.modal('show');
    });

    
    $('#almacen').change(function(e) {
        var almacen= $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : almacen,
                action : "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) { 
                if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                    /*var options_articulos = $("#articulo");
                    options_articulos.empty();
                    options_articulos.append(new Option("Seleccione Artículo", ""));*/



                    /*for (var i=0; i<data.articulos.length; i++)
                    {
                        options_articulos.append(new Option(data.articulos[i].id_articulo +" "+data.articulos[i].articulo, data.articulos[i].id_articulo));
                    }*/


                    //$("#articulo").trigger("chosen:updated");

                    var options_zonas = $("#zona");
                    options_zonas.empty();
                    options_zonas.append(new Option("Seleccione Zona", ""));

                    for (var i=0; i<data.zonas.length; i++) {
                        options_zonas.append(new Option(data.zonas[i].clave +" "+data.zonas[i].descripcion, data.zonas[i].clave));
                    }

                    $("#zona").trigger("chosen:updated");

                }

        });
$("#almacen option[value='']").hide();

    });
</script>