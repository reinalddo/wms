<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();

$codDaneSql = \db()->prepare("SELECT 
			pe.`id_pedido` AS consecutivo,
            pe.`id_pedido` AS pedido,
			pe.`Fol_folio` AS folio,
			IFNULL(c.RazonSocial, '--') AS cliente,
			IFNULL(e.DESCRIPCION, '--') AS STATUS,
			IFNULL(DATE_FORMAT(pe.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
			IFNULL(DATE_FORMAT(pe.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
			u.descripcion AS mesa, 
			IFNULL(us.nombre_completo, '--') AS usuario
		FROM c_almacenp AS p
		INNER JOIN t_ubicaciones_revision AS u
		INNER JOIN th_pedido AS pe
        LEFT JOIN c_cliente c On c.Cve_Clte = pe.Cve_clte
		INNER  JOIN cat_estados e ON e.ESTADO = pe.status
		LEFT JOIN c_usuario us ON us.cve_usuario = pe.Cve_Usuario
		WHERE u.cve_almac = p.clave
		AND p.activo = 1 AND u.activo =1 and pe.status in ('L','R')
		GROUP BY pe.`id_pedido`");
$codDaneSql->execute();
$codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);


$codDaneSqln = \db()->prepare("SELECT 
			pe.`id_pedido` AS consecutivo,
            pe.`id_pedido` AS pedido,
			pe.`Fol_folio` AS folio,
			u.descripcion AS mesa
		FROM c_almacenp AS p
		INNER JOIN t_ubicaciones_revision AS u
		INNER JOIN th_pedido AS pe
		WHERE u.cve_almac = p.clave
		AND p.activo = 1 AND u.activo =1 AND pe.status IN ('L','R')
		GROUP BY pe.`Fol_folio`");
$codDaneSqln->execute();
$codDanen = $codDaneSqln->fetchAll(PDO::FETCH_ASSOC);


?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">



<style>
    #inicio {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #modulo {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    #auditoriayempaque{
        width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }
</style>

<!-- Inicio -->
<div class="wrapper wrapper-content  animated" id="inicio">
    <h3>Auditoria y Empaque</h3>

    <div class="row">
        <div class="col-lg-12" style="display:none;">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">

                        <div class="col-lg-4" style="display:none;">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid2()">
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarA">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                            <!--   <input class="form-control" type="text" name="daterange" value="<?php echo date("d/m/Y"); ?> - <?php echo date("d/m/Y"); ?>" />-->
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="email">Status de Orden</label>
                                <select name="status" id="status" class="chosen-select form-control">
                                    <option value="">Seleccione un Status</option>
                                    <option value="R">Auditando</option>
                                    <option value="L">Pendiente de auditar</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="pull-right">

                                <style>

                                    <?php if($edit[0]['Activo']==0){?>

                                    .fa-edit{

                                        display: none;

                                    }

                                    <?php }?>


                                    <?php if($borrar[0]['Activo']==0){?>

                                    .fa-eraser{

                                        display: none;

                                    }

                                    <?php }?>

                                </style>


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

        <div id="auditoriayempaque" class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">

                        <div class="col-lg-4">	
                            <div class="form-group">
                                <label for="email">Area de revisión</label>
                                <select name="area" id="area" class="chosen-select form-control">
                                    <option value="">Seleccione un Area</option>
                                    <?php foreach( $codDanen AS $p ): ?>
                                    <option value="<?php echo $p["folio"];?>"><?php echo "(".$p["folio"].") ".$p["mesa"]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>	

                        <div class="col-lg-4">	
                            <div class="form-group">
                                <label for="email">Pedido</label>
                                <select name="ped" id="ped" class="chosen-select form-control">
                                    <option value="">Seleccione un Pedido</option>
                                    <?php foreach( $codDane AS $p ): ?>
                                    <option value="<?php echo $p["pedido"];?>"><?php echo $p["pedido"]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>		

                        <div class="col-lg-4">

                            <div class="input-group-btn">

                                <button type="button" class="btn btn-sm btn-primary" id="Busc">
                                    Buscar
                                </button>

                            </div>
                        </div>


                        <div class="col-lg-6">			
                            <label>Área de Revisión</label>
                            <input id="mesa_ae" type="text" placeholder="" value="" class="form-control" readonly>
                        </div>				
                        <div class="col-lg-6">
                            <label>Pedido</label>
                            <input id="pedido_ae" type="text" placeholder="" value="" class="form-control" readonly>
                        </div>
                        <div class="col-lg-12">			
                            <label>Cliente</label>						
                            <input id="cliente_ae" type="text" placeholder="" value="" class="form-control" readonly />						
                        </div>


                        <div class="col-lg-6">			
                            <label>Total Pzs Fact.</label>
                            <input id="totalfact_ae" type="text" placeholder="" value="" class="form-control" readonly>
                        </div>				
                        <div class="col-lg-6">	
                            <label>Total Pzs Rev.</label>					
                            <input id="totalrev_ae" type="text" placeholder="" value="" class="form-control" readonly />
                        </div>
                        <div class="col-lg-12 text-center">		

                            <div class="input-group-btn" style="display:none;">
                                <a href="#" onclick="salir()"><button type="submit" class="btn btn-sm btn-primary">Salir</button></a>
                            </div>	
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
        </div>
    </div>
</div>

<input type="hidden" id="hiddenPedido" />
<!-- Mainly scripts -->
<!--<script src="/js/dropdownLists.js"></script>-->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>


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


<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script>
    $('#mesa').on('change', function() {
        if ($(this).val()!="") 
            $("#aceptar").prop('disabled',false);
        else
            $("#aceptar").prop('disabled',true);
    });
</script>
<!-- Grid de Productos -->
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
            url:'/api/qaauditoria/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                valor:"ver"
            },
            mtype: 'POST',
            colNames:['Consecutivo','Pedido' ,'Folio', 'Cliente','Status','Fecha Inicio',"Fecha Final" , "Mesa Revisión", "Usuario" , "Opciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'consecutivo',index:'consecutivo',width:120, editable:false, sortable:false, resizable: false},
                {name:'pedido',index:'pedido',width:120, editable:false, sortable:false, resizable: false},
                {name:'folio',index:'folio',width:120, editable:false, sortable:false, resizable: false},
                {name:'cliente',index:'cliente',width:180, editable:false, sortable:false, resizable: false},
                {name:'status',index:'STATUS',width:180, editable:false, sortable:false, resizable: false},
                {name:'fechaIni',index:'fecha_p',width:180, editable:false, sortable:false, resizable: false},
                {name:'fechaFin',index:'fecha_e',width:180, editable:false, sortable:false, resizable: false},
                {name:'mesa',index:'mesa',width:180, editable:false, sortable:false, resizable: false},
                {name:'usuario',index:'usuario',width:180, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat2, frozen : true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'consecutivo',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size


        function imageFormat2( cellvalue, options, rowObject ){
            var serie = rowObject[2];
            var html = '';


            html += '<a href="#" onclick="detalle(\''+serie+'\')"  title="Ver"><i class="fa fa-eye"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="auditoriayempaque(\''+serie+'\')"  title="Ver"><i class="fa fa-check"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';


            return html;

        }






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
            url:'/api/qaauditoria/lista/index_pedidos.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio1").val(),
                pedido: $("#hiddenPedido").val()
            },
            mtype: 'POST',
            colNames:['Clave','Nombre del Articulo','Lote','Tipo','Pedidas','Revisada'],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'clave',index:'clave',width:110, editable:false, sortable:false},
                {name:'nombre',index:'nombre',width:200, editable:false, sortable:false},
                {name:'lote',index:'lote',width:130, editable:false, sortable:false},
                {name:'tipo',index:'tipo',width:160, editable:false, sortable:false},
                {name:'pedidas',index:'pedidas',width:110, editable:false, sortable:false},
                {name:'revisadas',index:'revisadas',width:110, editable:false, sortable:false},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
                                 {edit: false, add: false, del: false, search: false},
                                 {height: 200, reloadAfterSubmit: true}
                                );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
</script>


<script type="text/javascript">
    /*************************** Variables Temporales *********************/
    var EncabezadosIncidencias = null;
    var DetalleIncidencias = [];
    /**********************************************************************/
    $('.clockpicker').clockpicker();

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

    //////////////////////////////////////////////////////////Aqui se contruye el Grid de pedidos//////////////////////////////////////////////////////////
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
            url:'/api/qaauditoria/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                mesa: $("#hiddenMesa").val()
            },
            mtype: 'POST',
            colNames:['Pedido','Sub Pedido', 'Cliente','Área de Revisión'],
            colModel:[
                {name:'Fol_folio',index:'Fol_folio',width:225, editable:false, sortable:false,key: true},
                {name:'subpedido',index:'subpedido',width:225, editable:false, sortable:false},
                {name:'cliente',index:'cliente',width:400, editable:false, sortable:false},
                {name:'mesa',index:'mesa',width:183, editable:false, sortable:false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'Fol_folio',
            viewrecords: true,
            sortorder: "desc",
            onSelectRow: function(ids) { 
                if(ids == null) {
                    swal({
                        title: "Pedido sin articulos",
                        text: "El pedido que ha seleccionado no posee articulos",					
                    });
                }else{
                    $("#hiddenPedido").val(ids);
                    auditoriayempaque(ids);
                }
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////

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

    /***********************************************************************************************************************/

    function borrarAdd(_codigo) {
        $("#grid-tabla").jqGrid('delRowData', _codigo);
    }

    function borrarAdd(_codigo) {
        $("#grid-tabla2").jqGrid('delRowData', _codigo);
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
                mesa: $("#hiddenMesa").val()
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio1").val(),
                pedido: $("#hiddenPedido").val()
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

    function cancelarlo() {
        $('#FORM2').removeAttr('class').attr('class', '');
        $('#FORM2').addClass('animated');
        $('#FORM2').addClass("fadeOutRight");
        $('#FORM2').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
    }



</script>


<script>

    function cancelarAuditar() {		
        $('#almacen').val("");	
        $('#mesa').empty();		
        $('#auditar').removeAttr('class').attr('class', '');
        $('#auditar').addClass('animated');
        $('#auditar').addClass("fadeOutRight");
        $('#auditar').hide();
        $('#inicio').show();
        $('#inicio').removeAttr('class').attr('class', '');
        $('#inicio').addClass('animated');
        $('#inicio').addClass("fadeInRight");
        $('#inicio').addClass("wrapper");
        $('#inicio').addClass("wrapper-content");
    }

    function cancelar() {		
        $('#almacen').val("");		
        $('#mesa').empty();			
        $('#modulo').removeAttr('class').attr('class', '');
        $('#modulo').addClass('animated');
        $('#modulo').addClass("fadeOutRight");
        $('#modulo').hide();
        $('#inicio').show();
        $('#inicio').removeAttr('class').attr('class', '');
        $('#inicio').addClass('animated');
        $('#inicio').addClass("fadeInRight");
        $('#inicio').addClass("wrapper");
        $('#inicio').addClass("wrapper-content");
    }

    function salir() {	
        $('#almacen').val("");		
        $('#mesa').empty();	
        $('#auditoriayempaque').removeAttr('class').attr('class', '');
        $('#auditoriayempaque').addClass('animated');
        $('#auditoriayempaque').addClass("fadeOutRight");
        $('#auditoriayempaque').hide();
        $('#inicio').show();
        $('#inicio').removeAttr('class').attr('class', '');
        $('#inicio').addClass('animated');
        $('#inicio').addClass("fadeInRight");
        $('#inicio').addClass("wrapper");
        $('#inicio').addClass("wrapper-content");
    }

    function aceptar() {		
        $('#inicio').removeAttr('class').attr('class', '');
        $('#inicio').addClass('animated');
        $('#inicio').addClass("fadeOutRight");
        $('#inicio').hide();
        $('#modulo').show();
        $('#modulo').removeAttr('class').attr('class', '');
        $('#modulo').addClass('animated');
        $('#modulo').addClass("fadeInRight");			
    }

    function almacen(){
        $('#mesa')
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
                action : "traerMesasdeAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/qaauditoria/update/index.php',
            success: function(data) {			
                if (data.success == true) {
                    var options = $("#mesa");
                    options.empty();
                    options.append(new Option("Area de Revisión", ""));
                    for (var i=0; i<data.mesas.length; i++){
                        options.append(new Option(data.mesas[i].descripcion, data.mesas[i].clave));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                }
            }
        });
    }

    function modulo() {
        $('#inicio').removeAttr('class').attr('class', '');
        $('#inicio').addClass('animated');
        $('#inicio').addClass("fadeOutRight");
        $('#inicio').hide();
        $('#modulo').show();
        $('#modulo').removeAttr('class').attr('class', '');
        $('#modulo').addClass('animated');
        $('#modulo').addClass("fadeInRight");

        $('#hiddenAlmacen').val($('#almacen').val());
        $('#hiddenMesa').val($('#mesa').val());
        console.log($('#hiddenAlmacen').val());
        console.log($('#hiddenMesa').val());
    }

    function auditarEmpacar() {
        ReloadGrid();
        $('#modulo').removeAttr('class').attr('class', '');
        $('#modulo').addClass('animated');
        $('#modulo').addClass("fadeOutRight");
        $('#modulo').hide();
        $('#auditar').show();
        $('#auditar').removeAttr('class').attr('class', '');
        $('#auditar').addClass('animated');
        $('#auditar').addClass("fadeInRight");	
    }

    function auditoriayempaque(id) {
        $('#hiddenPedido').val(id);
        ReloadGrid1();
        $('#auditar').removeAttr('class').attr('class', '');
        $('#auditar').addClass('animated');
        $('#auditar').addClass("fadeOutRight");
        $('#auditar').hide();
        $('#auditoriayempaque').show();
        $('#auditoriayempaque').removeAttr('class').attr('class', '');
        $('#auditoriayempaque').addClass('animated');
        $('#auditoriayempaque').addClass("fadeInRight");	


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                pedido : id,
                action : "cargarAuditoria"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/qaauditoria/update/index.php',
            success: function(data) {
                if (data.success == true) {		
                    $('#mesa_ae').val(data.auditoria[0].descripcion);	
                    $('#pedido_ae').val(id);
                    $('#cliente_ae').val(data.auditoria[0].RazonSocial);
                    $('#totalfact_ae').val(data.auditoria[0].totalFact);
                    $('#totalrev_ae').val(0);
                }
            }
        });

    }

    $(document).ready(function(){ 
        $('#Busc').on('click',function(){
            var id=$('#ped').val();

            var ids=$('#area').val();

            $('#hiddenPedido').val(ids);
            ReloadGrid1();
            $('#auditar').removeAttr('class').attr('class', '');
            $('#auditar').addClass('animated');
            $('#auditar').addClass("fadeOutRight");
            $('#auditar').hide();
            //$('#auditoriayempaque').show();
            //$('#auditoriayempaque').removeAttr('class').attr('class', '');
            //$('#auditoriayempaque').addClass('animated');
            //$('#auditoriayempaque').addClass("fadeInRight");	


            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    area : id,
                    pedido : ids,
                    action : "cargarAuditoria"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/qaauditoria/update/index.php',
                success: function(data) {
                    if (data.success == true) {		
                        $('#mesa_ae').val(data.auditoria[0].descripcion);	
                        $('#pedido_ae').val(id);
                        $('#cliente_ae').val(data.auditoria[0].RazonSocial);
                        $('#totalfact_ae').val(data.auditoria[0].totalFact);
                        $('#totalrev_ae').val(0);
                    }
                }
            });

            //$('#auditoriayempaque').hide();
        });
    });
</script>

<script>
    $(document).ready(function(){
        $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);

        $('#modulo').removeAttr('class').attr('class', '');
        $('#modulo').addClass('animated');
        $('#modulo').addClass("fadeOutRight");
        $('#modulo').hide();
        $('#auditar').removeAttr('class').attr('class', '');
        $('#auditar').addClass('animated');
        $('#auditar').addClass("fadeOutRight");
        $('#auditar').hide();
        //$('#auditoriayempaque').removeAttr('class').attr('class', '');
        //$('#auditoriayempaque').addClass('animated');
        //$('#auditoriayempaque').addClass("fadeOutRight");
        //$('#auditoriayempaque').hide();
    });
</script>