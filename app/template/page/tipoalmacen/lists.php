<?php

use \AlmacenP\AlmacenP as AlmacenP;
$AlmacenP = new AlmacenP();
$listaTAlmacen = new \TipoAlmacen\TipoAlmacen();
$listaZona = new \Zona\Zona();
$listacia = new \Companias\Companias();


$codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
$codDaneSql->execute();
$codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);


$vere = \db()->prepare("select * from t_profiles as a where id_menu=11 and id_submenu=21 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=11 and id_submenu=22 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=11 and id_submenu=23 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=11 and id_submenu=24 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 11

// VER 21
// AGREGAR 22
// EDITAR 23
// BORRAR 24

?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Mainly scripts -->

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Tipo de Almacén</h4>
            </div>
            <form id="myform">
                <div class="modal-body">
                    <div class="form-group"><label>Clave del Tipo de Almacén *</label> <input id="clave_talmacen" type="text" placeholder="Clave de la Empresa" maxlength="20" class="form-control" required="true"><!--<label id="CodeMessage" style="color:red;"></label>--></div>
                    <div class="form-group"><label>Tipo de Almacén *</label> <input id="desc_tipo_almacen" type="text"  placeholder="Descripcion"  class="form-control" required="true"></div>

                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddencve_tipcia">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Tipo de Almacén</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn  btn-primary" id="buscarT">
                                        <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8">
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

                            <a href="#" onclick="agregar()"><button class="btn btn-primary permiso_registrar" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Tipos de almacen inactivos</button>
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
                    <h4 class="modal-title">Recuperar tipo de almacén</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Tipo almacen...">
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

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

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
            url:'/api/tipoalmacen/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['ID','Clave','Tipo de Almacén',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id',index:'id',width:20, editable:false, hidden:true, sortable:false},
                {name:'clave_talmacen',index:'clave_talmacen',width:20, editable:false, sortable:false},
                {name:'desc_tipo_almacen',index:'desc_tipo_almacen',width:150, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave_talmacen',
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
            $("#hiddencve_tipcia").val(serie);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_editar").val() == 1)
            {
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if($("#permiso_eliminar").val() == 1)
            {
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }

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
                id : _codigo,
                action : "tieneAlmacen"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/tipoalmacen/update/index.php',
            success: function(data) {
                console.log(data);
                if (data.success == true) {
                    swal({
                        title: "¡Alerta!",
                        text: "El tipo de almacen esta siendo usado en este momento",
                        type: "warning",
                        showCancelButton: false,
                    });
                }
                else{
                    swal({
                        title: "¿Está seguro que desea borrar el tipo de almacen?",
                        text: "Está a punto de borrar un tipo de almacen y esta acción no se puede deshacer",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Borrar",
                        cancelButtonText: "Cancelar",
                        closeOnConfirm: false
                    },
                         function(){
                        var borro = localStorage.getItem("borro") | 0;
                        localStorage.setItem('borro', borro + 1);
                        $.ajax({
                            url: '/api/tipoalmacen/update/index.php',
                            type: "POST",
                            dataType: "json",
                            data: {
                                id : _codigo,
                                action : "delete"
                            },
                            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                                    },
                            dataType: 'json'
                        }).done(function(data){
                            if(data.success){
                                ReloadGrid();
                                swal("Borrado", "El tipo de almacen ha sido borrado exitosamente", "success");
                            }else{
                                swal("Error", "Ocurrió un error al eliminar el tipo de almacen", "error");
                            }
                        });
                    });
                }
            }
        });  
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

    function editar(_codigo) {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Tipo de Almacén</h4>');
        $("#clave_talmacen").prop('disabled', true);

        $("#desc_tipo_almacen").html("");
        $("#clave_talmacen").html("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/tipoalmacen/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    $("#desc_tipo_almacen").val(data.desc_tipo_almacen);
                    $("#clave_talmacen").val(data.clave_talmacen);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                    $("#hiddencve_tipcia").val(data.id);

                }
            }
        });
    }

    function agregar() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Tipo de Almacén</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        l.ladda('stop');
        $("#clave_talmacen").prop('disabled', false);
        $("#desc_tipo_almacen").html("");
        $("#clave_talmacen").val("");
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#desc_tipo_almacen").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        if ($("#desc_tipo_almacen").val()=="") {
            return;
        }

        $("#btnCancel").hide();

        l.ladda( 'start' );

        $.post('/api/tipoalmacen/update/index.php',
               {
            id : $("#hiddencve_tipcia").val(),
            clave_talmacen : $("#clave_talmacen").val(),
            desc_tipo_almacen : $("#desc_tipo_almacen").val(),
            action : $("#hiddenAction").val()
        },
               function(response){
            console.log(response);
        }, "json")
            .always(function() {
            $("#desc_tipo_almacen").val("");
            l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
        });
    });

    $("#clave_talmacen").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

            var clave_talmacen = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave_talmacen : clave_talmacen,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                url: '/api/tipoalmacen/update/index.php',
                success: function(data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }else{
                        $("#CodeMessage").html(" Clave de Tipo de Almacén ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }

            });

        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de Tipo de Almacén válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $(document).ready(function(){
        $("#inactivos").on("click", function(){
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarT").click();
        }
    });

</script>

<script type="text/javascript">

    <!-- Segundas Grid -->
    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function () {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
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
            url:'/api/tipoalmacen/lista/index_i.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:['ID','Clave','Tipo de Almacén',"Recuperar"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id',index:'id',width:20, editable:false, hidden:true, sortable:false},
                {name:'clave_talmacen',index:'clave_talmacen',width:200, editable:false, sortable:false},
                {name:'desc_tipo_almacen',index:'desc_tipo_almacen',width:500, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'clave_talmacen',
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
            var id_tipoAlmacen = rowObject[0];
            // var estado = rowObject[1];
            //var correl = rowObject[4];
            //var url = "x/?serie="+poblacion+"&correl="+correl;
            //var url2 = "v/?serie="+poblacion+"&correl="+correl;
            $("#hiddencve_tipcia").val(id_tipoAlmacen);
            //$("#hiddenIDEstado").val(estado);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            /* html += '<a href="#" onclick="editar(\''+id_tipoAlmacen+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';*/
            html += '<a href="#" onclick="recovery(\''+id_tipoAlmacen+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio1").val(),
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

    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/tipoalmacen/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
        /*$.post( "/api/usuarios/update/index.php",
         {
         id_user : _codigo,
         action : "delete"

         } ,function( data ) {
         alert(data);
         });*/
    }

</script>




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