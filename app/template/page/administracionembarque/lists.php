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
    <h3>Visor Áreas de Embarque*</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen">
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                  
                        <div class="col-md-8" style="margin-bottom: 20px">
                            <label for="email">&#160;&#160;</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" id="buscar" placeholder="Ingrese el Folio o área de embarque...">
                                
                                <div class="input-group-btn">
                                    
                                    <button  onclick="buscar()" type="submit" class="btn btn-primary" id="buscarP">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>
                                    
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
</div>

<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Pedidos en embarque</h4>
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

<style>

.ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-table2, #grid-table3, #grid-table4, #grid-pager, #grid-pager2, #grid-pager3, #grid-pager4 {
    width: auto !important;
    max-width: 100% !important;
}

</style>

<div class="modal fade" id="modal-detalles-pedido" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalles de pedido</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="jqGrid_wrapper" id="detalle_wrapper">
                            <table id="grid-detalles-pedido"></table>
                            <div id="grid-detalles-pedido-pager"></div>
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

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){ 
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
                    $('#almacen').val(data.codigo.clave).trigger("chosen:updated");
                    setTimeout(() => {
                        buscar()
                    }, 1000);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    $(function($){
        almacenPrede()
        
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
                    $(grid_selector).jqGrid( 'setGridWidth', $(window).width() - 200 );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/administracionembarque/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Clave de Embarque", "Área de Embarque", "Total de Pedidos","Existencia Total", "Acción"],
            colModel:[
                {name:'clave',index:'clave', width: 150, editable:false, sortable:false},
                {name:'area',index:'area', width: 350, editable:false, sortable:false},				
                {name:'total_pedidos',index:'total_pedidos', width: 160,editable:false, align:'right', sortable:false},
                {name:'total_productos',index:'total_productos', width: 180,editable:false, align:'right', sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            sortorder: "desc",
			autowidth:true,
            loadComplete: almacenPrede()
        });

        // Setup buttons
        $(grid_selector).jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var embarque = rowObject[0];
            var html = '';
            html += '<a href="#" onclick="detalle(\''+embarque+'\')"><i class="fa fa-search" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
            url: '/api/administracionembarque/lista/index.php',
            datatype: "local",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Área de Embarque", "Folio", "Fecha Pedido", "Fecha Entrega", "Items", "Acciones" ],
            colModel:[
                {name:'area_descripcion',index:'area_descripcion', width: 150,editable:false, sortable:false},
                {name:'pedido_folio',index:'pedido_folio', width: 100, editable:false, sortable:false},
                {name:'pedido_fecha',index:'pedido_fecha', width: 150, editable:false, sortable:false},
                {name:'pedido_entrega',index:'pedido_entrega', width: 150, editable:false, sortable:false},
                {name:'pedido_total_items',index:'pedido_total_items', width: 100, align:'right', editable:false, sortable:false},
                {name:'myac',index:'', width:100, fixed:true, sortable:false, resize:false, formatter:imageFormat}
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

    

        function imageFormat( cellvalue, options, rowObject ){
            var folio = rowObject[1];
            var html = '';
            html += '<a href="#" onclick="detallePedido(\''+folio+'\')"><i class="fa fa-search" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });




    $(function($){
        var grid_selector = "#grid-detalles-pedido";
        var pager_selector = "#grid-detalles-pedido";

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
            url: '/api/administracionembarque/lista/index.php',
            datatype: "local",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Folio", "Clave", "Articulo", "Cantidad" ],
            colModel:[
                {name:'folio',index:'area_descripcion', width: 120,editable:false, sortable:false},
                {name:'clave',index:'clave', width: 200, editable:false, sortable:false},
                {name:'articulo',index:'articulo', width: 350, editable:false, sortable:false},
                {name:'cantidad',index:'cantidad', width: 150, editable:false, align:'right', sortable:false}
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

   

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });


</script>

<script>

    function detallePedido(folio){

        $('#grid-detalles-pedido').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            folio: folio,
            almacen: $("#almacen").val(),
            action: 'cargarDetallePedido'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);

        $('#modal-detalles-pedido').modal('show');
    }



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

function loadDataToGridDetails(embarque, almacen) {
    $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            embarque: embarque,
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

function detalle(embarque){
    loadDataToGridDetails(embarque, $("#almacen").val());
    $("#detalleModal").modal('show');
}

</script>
