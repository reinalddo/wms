<?php

$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();

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
                            <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
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

<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar Orden de Compra</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">

                                <div class="form-group"><label>Número de Folio</label> <input id="FolioOrden" onkeyup="return ValNumero(this);" type="text" placeholder="Número de Folio" class="form-control"></div>

                                <div class="form-group" id="data_1">
                                    <label class="font-noraml">Fecha de Entrada</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaentrada" type="text" class="form-control" value="03/04/2014">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Almacén</label>
                                    <select class="form-control" id="Almcen">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_almac; ?>"><?php echo $a->des_almac; ?></option>
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

                                <div class="form-group">
                                    <label>Protocolo</label>
                                    <select class="form-control" id="Protocol">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaProto->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group"><label>Consecutivo</label> <input id="Consecut" onkeyup="return ValNumero(this);" type="text" placeholder="Consecutivo" class="form-control"></div>

                                <div class="form-group"><label>Numero de Orden</label> <input id="NumOrden" type="text" placeholder="Numero de Orden" class="form-control"></div>

                            </div>
                            <div class="col-lg-12">
                                <div class="form-group" id="Articul">
                                    <label>Artículo</label>
                                    <select name="country" id="basic" style="width:100%;">
                                        <option value="">Seleccione Artículo</option>
                                        <?php foreach( $listaArtic->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_articulo; ?>"><?php echo $a->des_articulo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                           </div>

                            <div class="col-lg-12">
                                <div class="form-group"><label>Cantidad de Piezas</label> <input id="CantPiezas" type="text" placeholder="Cantidad de Piezas" class="form-control"></div>
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
                                    <button type="button" class="btn btn-primary ladda-button2" data-style="contract" id="btnSave">Guardar-</button>
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
                /*{name:'Colonia',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Ciudad',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Estado',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Pais',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'CodigoPostal',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'RFC',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Telefono1',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},
                 {name:'Telefono2',index:'cve_gpoart',width:50, editable:false, sortable:false, resizable: false},*/
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
            if ($("#basic").val()=="") return;
            if ($("#CantPiezas").val()=="") return;
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
                Cve_Clte : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            //url: '/api/clientes/update/index.php',
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
            url: '/api/clientes/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#txtClaveCliente').prop('disabled', true);
                    $("#txtClaveCliente").val(data.Cve_Clte);
                    $("#txtClaveClienteProv").val(data.Cve_CteProv);
                    $("#txtRazonSocial").val(data.RazonSocial);
                    $("#txtCalleNumero").val(data.CalleNumero);
                    $("#txtColonia").val(data.Colonia);
                    $("#txtCodigoPostal").val(data.CodigoPostal);
                    $("#txtCiudad").val(data.Ciudad);
                    $("#txtEstado").val(data.Estado);
                    $("#txtPais").val(data.Pais);
                    $("#txtRFC").val(data.RFC);
                    $("#txtTelefono1").val(data.Telefono1);
                    $("#txtTelefono2").val(data.Telefono2);
                    $("#txtCondicionPago").val(data.CondicionPago);
                    $("#cboProveedor").val(data.ID_Proveedor);
                    $("#cboTipoCliente").val(data.ClienteTipo);
                    $("#cboZona").val(data.ZonaVenta);
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

    //enable datepicker
    function pickDate( cellvalue, options, cell ) {
        setTimeout(function(){
            $(cell) .find('input[type=text]')
                .datepicker({format:'yyyy-mm-dd' , autoclose:true});
        }, 0);
    }

    function agregar() {
        $("#_title").html('<h3>Agregar Orden de Compra</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $('#codigo').prop('disabled', false);
    }

    var l = $( '.ladda-button2' ).ladda();
    l.click(function() {

        /************************ VALIDAR INPUTS DEL FORM ****************************/
        if ($("#FolioOrden").val()=="") {
            alert("Por, favor, Agregue el Número de Orden de Folio");
            $('#FolioOrden').focus();
            return;
        }
        if ($("#Almcen").val()=="") {
            alert("Por, favor, Seleccione un Almacén");
            $('#Almcen').focus();
            return;
        }
        if ($("#Proveedr").val()=="") {
            alert("Por, favor, Seleccione un Proveedor");
            $('#Proveedr').focus();
            return;
        }
        if ($("#Protocol").val()=="") {
            alert("Por, favor, Seleccione un Protocolo");
            $('#Protocol').focus();
            return;
        }
        if ($("#Consecut").val()=="") {
            alert("Por, favor, Indique si es Consecutivo");
            $('#Consecut').focus();
            return;
        }
        if ($("#NumOrden").val()=="") {
            alert("Por, favor, Indique el Número de Orden");
            $('#NumOrden').focus();
            return;
        }
        if ($("#basic").val()=="") {
            alert("Por, favor, Seleccione el Artículo");
            $('#basic').focus();
            return;
        }
        if ($("#CantPiezas").val()=="") {
            alert("Por, favor, Indique la Cantidad de Piezas");
            $('#CantPiezas').focus();
            return;
        }
        /************************ FIN VALIDAR INPUTS DEL FORM ************************/

        $("#btnCancel").hide();

        l.ladda( 'start' );

        $.post('/api/ordendecompra/update/index.php',
            {
                num_pedimento: $("#FolioOrden").val(),
                fechaentrada: $("#FechaEntrada").val(),
                Cve_Almac: $("#Almcen").val(),
                ID_Proveedor: $("#Proveedr").val(),
                ID_Protocolo: $("#Protocol").val(),
                Consec_protocolo: $("#Consecut").val(),
                factura: $("#NumOrden").val(),
                action : $("#hiddenAction").val()
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                $("#grupoArt").val("");
                $("#descripcion").val("");
                l.ladda('stop');
                $("#btnCancel").show();
                cancelar()
                ReloadGrid();
            });
    });

    $(".js-data-example-ajax").select2({
        ajax: {
            url: "https://api.github.com/search/repositories",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, page) {
                // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data
                return data.items;
            },
            cache: true
        },
        minimumInputLength: 1,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });

</script>

<script>
    $(document).ready(function(){
        $("#basic").select2();

        $("#multi").select2({
            placeholder: "Select a country"
        });

        $("#minimum").select2({
            minimumInputLength: 2
        });

    });
</script>