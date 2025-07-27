<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style type="text/css">
th[role="columnheader"]{
    text-align: center;
}
</style>

<style type="text/css">
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-table2, #grid-table3, #grid-table4, #grid-pager, #grid-pager2, #grid-pager3, #grid-pager4{
        width: 100% !important;
        max-width: 100% !important;
    }
</style>

<!-- Mainly scripts -->

<div class="wrapper wrapper-content  animated" id="list">
    <h3>Visor de Áreas de Recepción</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen">
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->id ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4" style="margin-bottom: 20px">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="buscar()">
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarP">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle</h4>
                </div>
                <div class="modal-body">
					<div class="ibox-content">
                        <div class="jqGrid_wrapper" id="detalle_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
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

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Jquery Validate -->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>


<script type="text/javascript">
    $(function($){
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
            url: '/api/administracionrecepcion/lista/index.php',
            datatype: "local",
            shrinkToFit: true,
            height:'auto',
            mtype: 'POST',
            colNames:["Área de Recepción","Total de Productos ubicados","Existencia Total","Acción"],
            colModel:[
                {name:'area',index:'area', width: 180, editable:false, sortable:false},
                {name:'total_productos',index:'total_productos', width: 180,editable:false, sortable:false},
                {name:'existencia_total',index:'existencia_total', width: 180,editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var ubicacion = rowObject[3];
            var html = '';
            html += '<a href="#" onclick="detalle(\''+ubicacion+'\')"><i class="fa fa-search" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
    $(function($){
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

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
            url: '/api/administracionrecepcion/lista/index.php',
            datatype: "local",
            autowidth:true,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Área de Recepción", "Folio Pedido", "Fecha Entrada", "Clave artículo", "Descripción", "Cantidad", "Lote", "Serie", "Caducidad"],
            colModel:[
                {name:'area',index:'area',  editable:false, sortable:false},
                {name:'folio',index:'folio', editable:false, sortable:false},
                {name:'fecha_entrada',index:'fecha_entrada', editable:false, sortable:false},
                {name:'clave',index:'clave', editable:false, sortable:false},
                {name:'descripcion',index:'descripcion', editable:false, sortable:false},
                {name:'cantidad',index:'cantidad', editable:false, sortable:false},
                {name:'lote',index:'lote', editable:false, sortable:false},
                {name:'serie',index:'serie', editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',editable:false, sortable:false}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            viewrecords: true,
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

      /*  jQuery("#grid-table2").jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'clave', numberOfColumns: 2, titleText: 'Producto'},
            ]
        });*/


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
</script>

<script>
    $(document).ready(function(){

        $("#almacen").on('change', function(e){
            loadDataToGrid(e.target.value);
        });

        $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
    });
</script>

<script>

function buscar() {
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacen: $("#almacen").val(),
            search: $("#buscar").val(),
            action: 'loadGrid'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
}

function loadDataToGrid(almacen) {
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacen: almacen,
            action: 'loadGrid'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
}

function loadDataToGridDetails(ubicacion, almacen) {
    $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            ubicacion: ubicacion,
            almacen: almacen,
            action: 'loadDetails'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
}

$("#buscar").keyup(function(event){
    if(event.keyCode == 13){
        buscar()
    }
});

function detalle(ubicacion){
    loadDataToGridDetails(ubicacion, $("#almacen").val());
    $("#detalleModal").modal('show');
}

</script>
