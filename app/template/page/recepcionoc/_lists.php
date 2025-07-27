<?php

$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();

$consecSql = \db()->prepare("SELECT consec_protocolo FROM th_aduana ORDER BY consec_protocolo desc limit 1 ");
$consecSql->execute();
$consec = $consecSql->fetch()['consec_protocolo'];

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
</style>

<div class="wrapper wrapper-content  animated " id="list">
 <h3>Recepción de OC</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-4">
						<button class="btn btn-primary" id="nuevo" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button>
                            
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


<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Selecciona Orden de Compra</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio2" placeholder="Buscar por Order de Compra...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                </a>
                            </div>
                        </div>

                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
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


<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Recepcion de OC</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">

								<div class="form-group" id="data_1">
                                    <label class="font-noraml">Fecha de Entrada</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaentrada" type="text" class="form-control" value="03/04/2014">
                                    </div>
                                </div>
								
								  <div class="form-group"><label>Numero de Orden</label> <input id="NumOrden" type="text" placeholder="Numero de Orden" class="form-control"></div>
								  
                                <div class="form-group"><label>Número de Folio</label> <input id="FolioOrden" onkeyup="return ValNumero(this);" type="text" placeholder="Número de Folio" class="form-control"></div>

									<div class="form-group">
                                    <label>Protocolo</label>
                                    <select class="form-control" id="Protocol">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProto->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="Proveedr">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProvee->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Proveedor; ?>"><?php echo $a->Empresa; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
								
                            </div>

                            <div class="col-lg-6">

							<div class="form-group" id="Articul">
                                    <label>Artículo</label>
                                    <select name="country" id="basic" style="width:100%;">
                                        <option value="">Seleccione Artículo</option>
                                        <?php foreach( $listaArtic->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_articulo; ?>"><?php echo "(".$a->cve_articulo.") ".$a->des_articulo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
								
							    <div class="form-group"><label>Lote</label> <input id="lote" type="text" placeholder="Lote" class="form-control"></div>	
								
								<div class="form-group" id="data_1">
                                    <label class="font-noraml">Caducidad</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caducidad" type="text" class="form-control" value="03/04/2014">
                                    </div>
                                </div>
								
								 <div class="form-group"><label>Numero de Serie</label> <input id="nserie" type="text" placeholder="" class="form-control"></div>	
								 
								 
									

                            </div>
							
							
                            <div class="col-lg-12">
									 <div class="form-group"><label>Cantidad</label> <input id="CantPiezas" type="text" placeholder="Cantidad de Piezas" class="form-control"></div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group"><label>Unidad de medida</label> <input id="Umedida" type="text" placeholder="Unidad de medida" class="form-control"></div>
                            </div>

                            <div class="col-lg-12">
                                <center><a href="#" id="addrow"><button type="button" class="btn btn-primary" id="btnCancel">Agregar Producto</button></a>&nbsp;&nbsp;</center>
                            </div>

                            <div class="col-lg-12">
                                <br>
                                <div class="ibox-content">
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-tabla"></table>
                                        <div id="grid-page"></div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button2" data-style="contract" id="btnSave">Guardar</button>
                                </div>
                            </div>


                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">

                    </div>
                </form>
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

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script type="text/javascript">
    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
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
            url:'/api/ordendecompra/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave','Numero de Folio','Nombre de Proveedor', ""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'ID_Aduana',index:'ID_Aduana',width:50, editable:false, sortable:false},
                {name:'num_pedimento',index:'num_pedimento',width:150, editable:false, sortable:false},
                {name:'Empresa',index:'Empresa',width:150, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc"
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
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[1];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenID_Aduana").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
    //////////////////////////////////////////////////////////Aqui se contruye el Grid 2 //////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-tabla";
        var pager_selector = "#grid-page";

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
            //url:'',
            datatype: "json",
            height: 250,
            mtype: 'POST',
            colNames:['Clave','Nombre','Cantidad', ""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'codigo',index:'codigo',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:150, editable:false, sortable:false},
                {name:'CantPiezas',index:'CantPiezas',width:150, sortable:false, editable:true, edittype:"text",
                    editoptions:{
                        size: 25, maxlengh: 30,
                        dataInit: function(element) {
                            $(element).keypress(function(e){
                                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                }
                            });
                        }
                    }
                },
                {name:'myac',index:'', width:40, fixed:true, sortable:false, resize:false, formatter:imageFormat2}
            ],
            rowNum:10,
            rowList:[10,20,30],
            //pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        // Setup buttons
        /*$("#grid-tabla").jqGrid('navGrid', '#grid-page',
         {edit: false, add: false, del: false, search: false},
         {height: 200, reloadAfterSubmit: true}
         );*/

        $("#addrow").click( function() {
            //if ($("#basic").val()=="") return;
            //if ($("#CantPiezas").val()=="") return;
            var ids = $("#grid-tabla").jqGrid('getDataIDs');
            for (var i = 0; i < ids.length; i++)
            {
                var rowId = ids[i];
                var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);

                console.log(rowData.Phrase);
                console.log(rowId);
                if (rowData.codigo==$("#basic").val()) {
                    alert("Este Artículo ya fue incluido");
                    return;
                }
            }
            emptyItem=[{codigo:$("#basic").val(),descripcion:$('#basic :selected').text(),CantPiezas:$("#CantPiezas").val()}];
            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
            //$("#grid-tabla").jqGrid('editRow', 0,true);


             $(':input','#myform')
             .removeAttr('checked')
             .removeAttr('selected')
             .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
             .val('');

            $('#basic').select2('val', '');


        });

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\''+correl+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
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

	/// Aqui se construye el grid de nueva orden /////
	  $(function($) {
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
           url:'/api/ordendecompra/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio2").val()
            },
            mtype: 'POST',
            colNames:['Clave','Numero de Folio','Fecha','Nombre de Proveedor', "Acciones"],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'ID_Aduana',index:'ID_Aduana',width:50, editable:false, sortable:false},
                {name:'num_pedimento',index:'num_pedimento',width:150, editable:false, sortable:false},
				 {name:'fech_pedimento',index:'fech_pedimento',width:150, editable:false, sortable:false},
                {name:'Empresa',index:'Empresa',width:150, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var id_usuario = rowObject[0];
            // var estado = rowObject[1];
            //var correl = rowObject[4];
            //var url = "x/?serie="+poblacion+"&correl="+correl;
            //var url2 = "v/?serie="+poblacion+"&correl="+correl;
            $("#hiddenIDUsuario").val(id_usuario);
            //$("#hiddenIDEstado").val(estado);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            /* html += '<a href="#" onclick="editar(\''+id_usuario+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';*/
            html += '<a href="#" onclick="agregar(\''+id_usuario+'\')"><i class="fa fa-plus" alt="Nuevo"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

	
    function borrarAdd(_codigo) {
        $("#grid-tabla").jqGrid('delRowData', _codigo);
    }

    function Solo_Numerico(variable){
        Numer=parseInt(variable);
        if (isNaN(Numer)){
            return "";
        }
        return Numer;
    }
    function ValNumero(Control){
        Control.value=Solo_Numerico(Control.value);
    }

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }
	
	function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio2").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Aduana : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                }
            }
        });
    }

    function selectarticulo() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Proveedores</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#nombre_proveedor").val("");
        $("#contacto").val("");
    }

    function editar(_codigo) {
        $("#_title").html('<h3>Editar Cliente</h3>');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#FolioOrden").val(data.num_pedimento);
                    $("#FechaEntrada").val(data.fechaentrada);
                    $("#Almcen").val(data.Cve_Almac);
                    $("#Proveedr").val(data.ID_Proveedor);
                    $("#Protocol").val(data.ID_Protocolo);
                    $("#Consecut").val(data.Consec_protocolo);
                    $("#NumOrden").val(data.factura);

                    $("#grid-tabla").jqGrid("clearGridData");

                    if (data.detalle) {
                        for (var i = 0; i < data.detalle.length; i++) {
                            emptyItem=[{codigo:data.detalle[i].cve_articulo,descripcion:data.detalle[i].des_articulo,CantPiezas:data.detalle[i].cantidad}];
                            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                        }
                    }

                    l.ladda('stop');
                    $("#btnCancel").show();

                    $('#list').removeAttr('class').attr('class', '');
                    $('#list').addClass('animated');
                    $('#list').addClass("fadeOutRight");
                    $('#list').hide();

                    $('#FORM').show();
                    $('#FORM').removeAttr('class').attr('class', '');
                    $('#FORM').addClass('animated');
                    $('#FORM').addClass("fadeInRight");

                    $('#FolioOrden').prop('disabled', true);

                    $("#hiddenAction").val("edit");
                }
            }
        });
    }


    function cancelar() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    $('#data_1 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true
    });

    $('#fechaentrada').datepicker('setDate', new Date());

    //enable datepicker
    function pickDate( cellvalue, options, cell ) {
        setTimeout(function(){
            $(cell) .find('input[type=text]')
                .datepicker({format:'yyyy-mm-dd' , autoclose:true});
        }, 0);
    }

    function agregar(_codigo) {
		
		      $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#FolioOrden").val(data.num_pedimento);
                    $("#FechaEntrada").val(data.fechaentrada);
                    $("#Almcen").val(data.Cve_Almac);
                    $("#Proveedr").val(data.ID_Proveedor);
                    $("#Protocol").val(data.ID_Protocolo);
                    $("#Consecut").val(data.Consec_protocolo);
                    $("#NumOrden").val(data.factura);

                    $("#grid-tabla").jqGrid("clearGridData");

                    if (data.detalle) {
                        for (var i = 0; i < data.detalle.length; i++) {
                            emptyItem=[{codigo:data.detalle[i].cve_articulo,descripcion:data.detalle[i].des_articulo,CantPiezas:data.detalle[i].cantidad}];
                            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                        }
                    }

          $modal0 = $("#coModal");
          $modal0.modal('toggle');
        $("#_title").html('<h3>Recepcion de OC</h3>');

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#basic").select2("val", "");

        $('#grid-tabla').jqGrid('clearGridData');
        $('#grid-tabla').trigger('reloadGrid');

        $("#hiddenAction").val("add");
        $('#FolioOrden').prop('disabled', false);
                }
            }
        });
  
		
    }

    var l = $( '.ladda-button2' ).ladda();
    l.click(function() {

        /************************ VALIDAR INPUTS DEL FORM ****************************/
        if ($("#FolioOrden").val()=="") {
            alert("Por, favor, Agregue el Número de Orden de Folio");
            $('#FolioOrden').focus();
            return;
        }
        /************************ FIN VALIDAR INPUTS DEL FORM ************************/

        var arrDetalle = [];

        var ids = $("#grid-tabla").jqGrid('getDataIDs');

        if (ids.length == 0) return;

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);

            console.log(rowData.Phrase);
            console.log(rowId);

            arrDetalle.push({
                codigo: rowData.codigo,
                descripcion: rowData.descripcion,
                CantPiezas: rowData.CantPiezas
            });
        }


        $("#btnCancel").hide();

        l.ladda( 'start' );

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                num_pedimento: $("#FolioOrden").val(),
                fechaentrada: $("#fechaentrada").val(),
                Cve_Almac: $("#Almcen").val(),
                ID_Proveedor: $("#Proveedr").val(),
                ID_Protocolo: $("#Protocol").val(),
                Consec_protocolo: $("#Consecut").val(),
                factura: $("#NumOrden").val(),
                arrDetalle: arrDetalle,
                action : $("#hiddenAction").val()
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    ReloadGrid();
                } else {
                    alert(data.err);
                    l.ladda('stop');
                    $("#btnCancel").show();
                }
            }
        });
    });

    // $(".js-data-example-ajax").select2({
        // ajax: {
            // url: "https://api.github.com/search/repositories",
            // dataType: 'json',
            // delay: 250,
            // data: function (params) {
                // return {
                    // q: params.term, // search term
                    // page: params.page
                // };
            // },
            // processResults: function (data, page) {
                // // parse the results into the forma expected by Select2.
                // // since we are using custom formatting functions we do not need to
                // // alter the remote JSON data
                // return data.items;
            // },
            // cache: true
        // },
        // minimumInputLength: 1,
        // templateResult: formatRepo, // omitted for brevity, see the source of this page
        // templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    // });

</script>

<script>
    $(document).ready(function(){
        $("#basic").select2();

        $("#multi").select2({
            placeholder: "Select a country"
        });
		
		$("#nuevo").on("click", function(){
            $modal0 = $("#coModal");
            $modal0.modal('show');
        });

        $("#minimum").select2({
            minimumInputLength: 2
        });

    });
</script>