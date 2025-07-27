<?php
$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();
$listStatus = new \Pedidos\Pedidos();
$listClientes = new \Clientes\Clientes(); ///////////////
$listaEmbar =  new \Almacen\Almacen(); ///////////////
$usuario = $_SESSION['id_user'];
?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

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

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <div class="col-lg-12" id="_title"></div>
            </div>
            <div class="modal-body">
                <select class="form-control" id="txtCriterio3">
                    <option value="">Seleccione</option>
                    <?php foreach( $listStatus->getStatus() AS $a ): ?>
                        <option value="<?php echo $a->ESTADO; ?>"><?php echo ucwords($a->DESCRIPCION); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
            </div>

            <input type="hidden" id="hiddenStatus">
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated " id="list">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="col-lg-4">
                                <select class="form-control" id="txtCriterio">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listStatus->getStatus() AS $a ): ?>
                                        <option value="<?php echo $a->ESTADO; ?>"><?php echo ucwords($a->DESCRIPCION); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <input type="text" class="form-control input-sm" id="txtCriterio2" placeholder="Buscar por Número de Pedido...">
                            </div>

                            <div class="col-lg-4">
                                <a href="#" onclick="ReloadGrid()">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Buscar
                                    </button>
                                </a>
                            </div>

                        </div>
                        <!--<div class="col-lg-4">
                            <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                        </div>-->
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
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>

<script type="text/javascript">
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    });
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
            url:'/api/adminpedidos/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val(),
                criterio2: $("#txtCriterio2").val()
            },
            mtype: 'POST',
            colNames:['','Status','Prioridad','Pedido','Fecha de Pedido','Cliente','Observaciones',""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},

                {name:'ID_Pedido',index:'ID_Pedido',width:50, editable:false, sortable:false, hidden:true},
                {name:'DESCRIPCION',index:'DESCRIPCION',width:70, editable:false, sortable:false},
                {name:'Descripcion',index:'Descripcion',width:30, editable:false, sortable:false},
                {name:'Fol_folio',index:'Fol_folio',width:50, editable:false, sortable:false},
                {name:'Fec_Pedido',index:'Fec_Pedido',width:55, editable:false, sortable:false},
                {name:'RazonSocial',index:'RazonSocial',width:80, editable:false, sortable:false},
                {name:'Observaciones',index:'Observaciones',width:100, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
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
            var folio = rowObject[3];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenID_Aduana").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+serie+'\',\''+folio+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                criterio2: $("#txtCriterio2").val(),
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
                Fol_folio : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/pedidos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                }
            }
        });
    }

    function selectarticulo() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Pedido</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#nombre_proveedor").val("");
        $("#contacto").val("");
    }

    function editar(_codigo,_folio) {
        $('#hiddenStatus').val(_codigo);
        $("#_title").html(' <h4 class="modal-title">Editar Status - Folio #'+_folio+'</h4>');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Pedido : _codigo,
                action : "change_status"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/pedidos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $("#txtCriterio3").val(data.status);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
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
        $('#hiddenStatus').val();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $('#FolioOrden').prop('disabled', false);
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Pedido: $('#hiddenStatus').val(),
                status: $("#txtCriterio3").val(),
                action : "update_status"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/pedidos/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    l.ladda('stop');
                    $modal0.modal('hide');
                    cancelar();
                    ReloadGrid();
                } else {
                    alert(data.err);
                    l.ladda('stop');
                    $("#btnCancel").show();
                }
            }
        });
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