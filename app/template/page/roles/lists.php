<?php

$vere = \db()->prepare("select * from t_profiles as a where id_menu=15 and id_submenu=29 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=15 and id_submenu=30 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu=15 and id_submenu=31 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=15 and id_submenu=32 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);



// MOD 15

// VER 29
// AGREGAR 30
// EDITAR 31
// BORRAR 32

?>


<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Mainly scripts -->

<style type="text/css">
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-table2, #grid-table3, #grid-table4, #grid-pager, #grid-pager2, #grid-pager3, #grid-pager4{
        width: 100% !important;
        max-width: 100% !important;
    }
</style>
<!DOCTYPE HTML>
<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Rol</h4>
            </div>
            <form id="myform">
                <div class="modal-body">
                    <div class="form-group"><label>Descripci&oacute;n del Rol *</label> <input id="rol" type="text"  placeholder="Descripci&oacute;n"  
                                                                                               class="form-control" required="true"></div>

                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenid_role">
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

    <h3>Roles</h3>

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
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarR">
                                            Buscar
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
            url:'/api/roles/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave','Descripción',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'id_role',index:'id_role',width:80, editable:false, sortable:false, hidden: true},
                {name:'rol',index:'rol',width:20, editable:false, sortable:false},
                {name:'myac',index:'', width:80, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'id_role',
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
            $("#hiddenid_role").val(serie);
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
        swal({
            title: "¿Está seguro que desea borrar el rol?",
            text: "Está a punto de borrar un rol y esta acción no se puede deshacer",
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
                url: '/api/roles/update/index.php',
                type: "POST",
                dataType: "json",
                data: {
                    id_role : _codigo,
                    action : "delete"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                        },
                dataType: 'json'
            }).done(function(data){
                if(data.success){
                    ReloadGrid();
                    swal("Borrado", "El rol ha sido borrado exitosamente", "success");
                }else{
                    swal("Error", "Ocurrió un error al eliminar el rol", "error");
                }
            });
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
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Rol</h4>');
        $("#rol").val("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_role : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/roles/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    $("#rol").val(data.rol);
                    $("#hiddenid_role").val(data.id_role);

                    //l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function agregar() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Rol</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        //l.ladda('stop');
        $("#rol").val("");
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#des_tipcia").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        if ($("#rol").val()=="") {
            return;
        }

        $("#btnCancel").hide();

        //l.ladda( 'start' );

        $.post('/api/roles/update/index.php',
               {
            id_role : $("#hiddenid_role").val(),
            rol : $("#rol").val(),
            action : $("#hiddenAction").val()
        },
               function(response){
            console.log(response);
        }, "json")
            .always(function() {
            $("#rol").val("");
            //l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
        });
    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarR").click();
        }
    });
</script>