<?php

$listaProto = new \Protocolos\Protocolos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaArtic = new \Articulos\Articulos();
$listClientes = new \Clientes\Clientes(); ///////////////
$listaEmbar =  new \Almacen\Almacen(); ///////////////

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

<div class="wrapper wrapper-content  animated " id="list">

    <h3>Promociones</h3>


    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-12">

                                <div class="form-group" id="Articul">
                                    <label>Código</label>
                                    <select name="basic2" id="basic2" style="width:100%;">
                                        <option value="">Seleccione Código de Artículo</option>
                                        <?php foreach( $listaArtic->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_articulo; ?>"><?php echo $a->des_articulo; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

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

                                <div class="form-group"><label>Cantidad de Piezas</label> <input id="CantPiezas" type="text" placeholder="Cantidad de Piezas" class="form-control"></div>
                            </div>

                            <div class="col-lg-12">
                                <center><a href="#" onclick="agregar()" id="addrow"><button type="button" class="btn btn-primary" id="btnCancel">Agregar Artículo</button></a></center>
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

                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenPromocion">

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

        /********************************************************************************************************************/
        $('#basic2').on('change', function(){
            //alert('Selected value: ' + $(this).val());
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo : $(this).val(),
                    action : "loadArt"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/promocion/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        $('#grid-tabla').jqGrid('clearGridData');
                        $('#grid-tabla').trigger('reloadGrid');
                        for (var i = 0; i < data.detalle.length; i++) {
                            emptyItem=[{
                                Cve_Articulo:data.detalle[i].Cve_Articulo,
                                Cve_ArtComponente:data.detalle[i].Cve_ArtComponente,
                                Cantidad:data.detalle[i].Cantidad
                            }];
                            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
                        }
                    }
                }
            });
        });
        /*************************************************************************************************/
        $(grid_selector).jqGrid({
            //url:'',
            type: "POST",
            datatype: "json",
            data: {
                IDpromo: $(this).val()
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/promocion/update/index.php',
            colNames:['Clave','Nombre','Cantidad', ""],/*'Calle y Numero','Colonia','Ciudad','Estado','Pais','Codigo Postal','RFC','Telefono1','Telefono2'*/
            colModel:[
                {name:'Cve_Articulo',index:'Cve_Articulo',width:100, editable:false, sortable:false},
                {name:'Cve_ArtComponente',index:'Cve_ArtComponente',width:150, editable:false, sortable:false},
                {name:'Cantidad',index:'Cantidad',width:100, sortable:false, editable:true, edittype:"text",
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
            rowNum:30,
            rowList:[30,40,50],
            //pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });
        /********************************************************************************************************************/

        $("#addrow").click( function() {
            if ($("#basic").val()=="") return;
            if ($("#CantPiezas").val()=="") return;
            var ids = $("#grid-tabla").jqGrid('getDataIDs');
            for (var i = 0; i < ids.length; i++) {
                var rowId = ids[i];
                var rowData = $('#grid-tabla').jqGrid ('getRowData', rowId);

                console.log(rowData.Phrase);
                console.log(rowId);
                if (rowData.Cve_Articulo==$("#basic").val()) {
                    alert("Este Artículo ya fue incluido");
                    return;
                }
            }
            emptyItem=[{Cve_Articulo:$("#basic").val(),Cve_ArtComponente:$('#basic :selected').text(),Cantidad:$("#CantPiezas").val()}];
            $("#grid-tabla").jqGrid('addRowData',0,emptyItem);
            //$("#grid-tabla").jqGrid('editRow', 0,true);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    Cve_Articulo: $("#basic").val(),
                    Cve_ArtComponente: $('#basic2').val(),
                    Cantidad: $("#CantPiezas").val(),
                    action : "add"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/promocion/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        if (data.err != "") {
                            alert(data.err);
                            return;
                        }
                        alert("El Articulo ya fue agregado!");
                    }
                }
            });
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

        $("#basic2").select2();

        $("#multi").select2({
            placeholder: "Select a country"
        });

        $("#minimum").select2({
            minimumInputLength: 2
        });

    });
</script>