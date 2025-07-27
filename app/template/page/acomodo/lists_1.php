<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();

?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>

<div class="wrapper wrapper-content  animated " id="list">
 <h3>Administración de Acomodos (Put Away)</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">

								<div class="form-group col-md-3">
                                    <label>Fecha Inicio</label>
                                    <div class="input-group date" id="data_1">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechai" type="text" class="form-control" >
                                    </div>
                                </div>


                                <div class="form-group col-md-3">
                                    <label>Fecha Fin</label>
                                    <div class="input-group date" id="data_2">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaf" type="text" class="form-control" >
                                    </div>
                                </div>

								<div class="form-group col-md-3">
                                    <label>Caducidad Desde</label>
                                    <div class="input-group date" id="data_3">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="cadui" type="text" class="form-control" >
                                    </div>
                                </div>


                                <div class="form-group col-md-3">
                                    <label>Caducidad Hasta</label>
                                    <div class="input-group date" id="data_4">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="caduf" type="text" class="form-control" >
                                    </div>
                                </div>
                        <div class="col-md-4">
							<label>Almacen</label>
								<div class="form-group">
									<select class="form-control" id="almacen" name="almacen" onchange="almacen()">
										<option value="">Almacen</option>
										<?php foreach( $listaNCompa->getAll() AS $p ): ?>
											<option value="<?php echo $p->clave; ?>"><?php echo $p->nombre; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
                            </div>
                            <div class="col-md-4">
								<div class="form-group">
									<label for="email">Zona de Almacenaje</label>
									<select name="zona" id="zona" class="chosen-select form-control">
									</select>
								</div>
							</div>
							<div class="col-md-4" style="top: 24px;">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-primary" id="buscarA">
                                        <i class="fa fa-search"></i> Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class="ibox-content">
                            <div style="width: 100%; text-align: center; margin-bottom: 20px;">
                                <button id="exportExcel" class="btn btn-primary">
                                    <i class="fa fa-file-excel-o"></i>
                                    Excel
                                </button>
                                <button id="exportPDF" class="btn btn-danger">
                                    <i class="fa fa-file-pdf-o"></i>
                                    PDF
                                </button>
                            </div>
                            <div class="jqGrid_wrapper">
                                <table id="grid-table"></table>
                                <div id="grid-pager"></div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="ibox-content">
					 <h4>Bitácora de Acomodos</h4>
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
                    <h4>Detalle de Entrada</h4>
                </div>
                <div class="modal-body">
                   				<div class="tabbable" id="tabs-131708">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#panel-928563"  data-toggle="tab">Resumen</a>
					</li>
					<li >
						<a href="#panel-594076" id="avanzada" data-toggle="tab">Detalle</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="panel-928563">

                        <div class="jqGrid_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
                        </div>

					</div>
					<div class="tab-pane" id="panel-594076">

                        <div class="jqGrid_wrapper">
                            <table id="grid-table3"></table>
                            <div id="grid-pager3"></div>
                        </div>

					</div>
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

<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.26/build/pdfmake.min.js"></script></script>
<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.26/build/vfs_fonts.js"></script>

<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

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
                    document.getElementById('almacen').value = data.codigo.clave;
                    almacen();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
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
                   url:'/api/acomodo/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
			height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Almacén','Usuario','Fecha','Clave','Producto','Cantidad','Lote','Caducidad','Serie','Zona Recepcion','Zona de Almacenaje','Ubicacion','Tipo de U','Hora Fin'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'almacen',index:'almacen', editable:false, hidden:false, sortable:false,width:150},
                {name:'usuario',index:'usuario', editable:false, sortable:false,width:150},
				 {name:'fecha',index:'fecha', editable:false, sortable:false,width:150},
				 {name:'clave_producto',index:'clave_producto', editable:false, sortable:false,width:150},
				 {name:'producto',index:'producto', editable:false, sortable:false,width:150},
				  {name:'cantidad',index:'cantidad',editable:false, sortable:false,width:100},
				  {name:'lote',index:'lote',editable:false, sortable:false,width:150},
				  {name:'caducidad',index:'caducidad',editable:false, sortable:false,width:150},
				  {name:'serie',index:'serie', editable:false, sortable:false,width:150},
				  {name:'zona_recepcion',index:'zona_recepcion', editable:false, sortable:false,width:150},
				   {name:'zona_almacenaje',index:'zona_recepcion', editable:false, sortable:false,width:150},
				   {name:'ubicacion',index:'ubicacion', editable:false, sortable:false,width:150},
					{name:'tipo_ubicacion',index:'tipo_ubicacion', editable:false, sortable:false, hidden:false,width:150},
					{name:'hora_fin',index:'hora_fin', editable:false, sortable:false, hidden:false,width:150},
//                {name:'myac',index:'',fixed:true, sortable:false, resize:false, formatter:imageFormat},
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
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[8];
			 var estatus = rowObject[7];

            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenID_Aduana").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

          //  html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-search" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';


            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

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
    //////////////////////////////////////////////////////////Aqui se contruye el Grid 2 //////////////////////////////////////////////////////////
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
          //url:'',
            datatype: "json",
            			shrinkToFit: false,
			height:'auto',
            mtype: 'POST',
            colNames:['Linea','Clave','Descripcion','Cantidad Pedida','Cantidad Recibida','Faltantes'],
            colModel:[
                {name:'linea',index:'linea',width:150, editable:false, sortable:false},
				 {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:200, editable:false, sortable:false},
                {name:'cantidad_pedida',index:'cantidad_pedida',width:100, sortable:false},
				{name:'cantidad_recibida',index:'cantidad_recibida',width:100, editable:false, sortable:false},
				{name:'cantidad_faltante',index:'cantidad_faltante',width:100, editable:false, hidden:false, sortable:false},

            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'linea',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
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
                    .datepicker({format:'dd-mm-yyyy' , autoclose:true});
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


	// Grid Avanzado
	  $(function($) {
        var grid_selector = "#grid-table3";
        var pager_selector = "#grid-pager3";

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
            shrinkToFit: false,
			height:'auto',
            mtype: 'POST',
            colNames:['Linea','Clave','Descripcion','Cantidad Pedida','Status','Fecha Entrada Compromiso','Fecha de Recepcion','Cantidad Recibida','Fecha/ Hora Inicio','Fecha/ Hora Fin','Faltantes','Dañado',"Usuario"],
            colModel:[
                {name:'linea',index:'linea',width:150, editable:false, sortable:false},
				 {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:200, editable:false, sortable:false},
                {name:'cantidad_pedida',index:'cantidad_pedida',width:100, sortable:false},
				{name:'status',index:'status',width:100, editable:false, sortable:false},
				{name:'fecha_compromiso',index:'fecha_compromiso',width:100, editable:false, hidden:true, sortable:false},
                 {name:'fecha_recepcion',index:'fecha_recepcion',width:150, editable:false, sortable:false},
				  {name:'cantidad_recibida',index:'cantidad_recibida',width:150, editable:false, sortable:false},
				   {name:'hora_inicio',index:'hora_inicio',width:150, editable:false, sortable:false},
				    {name:'hora_fin',index:'hora_fin',width:150, editable:false, sortable:false},
					 {name:'cantidad_faltante',index:'cantidad_faltante',width:150, editable:false, sortable:false},
					  {name:'cantidad_danada',index:'cantidad_danada',width:150, editable:false, sortable:false},
					   {name:'usuario_activo',index:'usuario_activo',width:150, editable:false, sortable:false}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        // Setup buttons
        $("#grid-table3").jqGrid('navGrid', '#grid-pager3',
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
                    .datepicker({format:'dd-mm-yyyy' , autoclose:true});
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




    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
				fechaInicio: $("#fechai").val(),
				fechaFin: $("#fechaf").val(),
				zona: $("#zona").val(),
				almacen: $("#almacen").val(),
				cadui: $("#cadui").val(),
				caduf: $("#caduf").val()
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

	function filtralo(){
		    $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                 criterio: $("#txtCriterio").val(),
				fechaInicio: $("#fechai").val(),
				fechaFin: $("#fechaf").val(),
				almacen: $("#almacen").val(),

            }, datatype: 'json', page : 1, fromServer: true})
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




    function editar(_codigo) {
          $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/acomodo/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#grid-table2").jqGrid("clearGridData");
					$("#grid-table3").jqGrid("clearGridData");


                        for (var i = 0; i < data.detalle.length; i++) {
							console.log(data.detalle[i]);
                            emptyItem=[{linea:data.detalle[i].linea,clave:data.detalle[i].clave,descripcion:data.detalle[i].descripcion,cantidad_pedida:data.detalle[i].cantidad_pedida,cantidad_recibida:data.detalle[i].cantidad_recibida,cantidad_faltante:data.detalle[i].cantidad_faltante}];
							emptyItem2=[{linea:data.detalle[i].linea,clave:data.detalle[i].clave,
							descripcion:data.detalle[i].descripcion,cantidad_pedida:data.detalle[i].cantidad_pedida,
							cantidad_recibida:data.detalle[i].cantidad_recibida,cantidad_faltante:data.detalle[i].cantidad_faltante,
							cantidad_danada:data.detalle[i].cantidad_danada,status:data.detalle[i].status,
							fecha_recepcion:data.detalle[i].fecha_recepcion,hora_inicio:data.detalle[i].hora_inicio,
							hora_fin:data.detalle[i].hora_fin,usuario_activo:data.detalle[i].usuario_activo}];
                            $("#grid-table2").jqGrid('addRowData',0,emptyItem);
							 $("#grid-table3").jqGrid('addRowData',0,emptyItem2);
                        }

							  $modal0 = $("#coModal");
            $modal0.modal('show');


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


    //enable datepicker
    function pickDate( cellvalue, options, cell ) {
        setTimeout(function(){
            $(cell) .find('input[type=text]')
                .datepicker({format:'dd-mm-yyyy' , autoclose:true});
        }, 0);
    }


    function pickDate( cellvalue, options, cell ) {
        setTimeout(function(){
            $(cell) .find('input[type=text]')
                .datepicker({format:'dd-mm-yyyy', autoclose:true});
        }, 0);
    }


	function almacen(){
		$('#zona')
			.find('option')
			.remove()
			.end()
		;

        $(".itemlist").remove();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave : $('#almacen').val(),
                action : "traerZonasDeAlmacenP"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) {

                 if (data.success == true) {
					        var options = $("#zona");
							options.empty();
							 options.append(new Option("Zona de Almacenaje", ""));
							for (var i=0; i<data.zonas.length; i++)
                    {
						options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
					}
					 $('.chosen-select').trigger("chosen:updated");
                }
            }
        });
	}

</script>

<script>
    $(document).ready(function(){
		$("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);

        $("#basic").select2();

        $("#multi").select2({
            placeholder: "Select a country"
        });


        $("#minimum").select2({
            minimumInputLength: 2
        });

        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        $('#data_2').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        $('#data_3').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        $('#data_4').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

    });
</script>

<script>
    $("#exportExcel").on("click", function(){
        $("#grid-table").jqGrid("exportToExcel",{
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : "PutAway.xls",
            maxlength : 40 // maxlength for visible string data
        })
    })
    $("#exportPDF").on("click", function(){
        $("#grid-table").jqGrid("exportToPdf",{
            orientation: 'landscape',
            pageSize: 'A4',
            description: '',
            customSettings: null,
            download: 'download',
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : "PutAway.pdf",
        })
    })

	$("#txtCriterio").keyup(function(event){
    if(event.keyCode == 13){
        $("#buscarA").click();
    }
});
</script>
