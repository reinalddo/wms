<?php
$ga = new \GrupoServicios\GrupoServicios();


$vere = \db()->prepare("select * from t_profiles as a where id_menu=26 and id_submenu=61 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu=26 and id_submenu=62 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("select * from t_profiles as a where id_menu=26 and id_submenu=63 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu=26 and id_submenu=64 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>



<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Grupo de Servicios</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span>  Buscar
                                        </button>
                                 
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8">
                   

                            <a href="#" <?php /* ?>class="permiso_registrar"<?php */ ?> onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <?php /* ?>
                            <a href="/api/v2/grupos-de-servicios/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>

                            <button  class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Grupo de Servicioss inactivos</button>
                            <?php */ ?>
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



<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Grupo de Servicios</h4>
            </div>
            <form id="myform">
            <div class="modal-body">
                <label>Clave *</label>
                <input id="codigo" type="text" placeholder="C&oacute;digo" maxlength="20" class="form-control" required="true"><!--<label id="CodeMessage" style="color:red;"></label>--><br>
                <label>Descripci&oacute;n *</label>
                <input id="descripcion" type="text" placeholder="Descripci&oacute;n" class="form-control" required="true">
                <input type="hidden" id="hiddenAction">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="submit" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
            </div>
        </form>
        </div>
    </div>
</div>


<!-- Modal Recuperar -->
<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Grupo de Servicios</h4>
                </div>
                <div class="modal-body">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()">
                                    <button type="submit" id="buscarR" class="btn btn-sm btn-primary">
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


    <div class="modal fade" id="importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Grupos</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


<script>

$('#btn-import').on('click', function() {

    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');

    var formData = new FormData();
    formData.append("clave", "valor");

    $.ajax({
        // Your server script to process the upload
        url: '/articulos/grupos/importar',
        type: 'POST',

        // Form data
        data: new FormData($('#form-import')[0]),

        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            $('.progress').show();
            var percentVal = '0%';
            bar.width(percentVal);
            percent.html(percentVal);
        },
        // Custom XMLHttpRequest
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = e.loaded / e.total;
                        percentComplete = parseInt(percentComplete * 100);
                        bar.css("width", percentComplete + "%");
                        percent.html(percentComplete+'%');
                        if (percentComplete === 100) {
                            setTimeout(function(){$('.progress').hide();}, 2000);
                        }
                    }
                } , false);
            }
            return myXhr;
        },
        success: function(data) {
            setTimeout(
                function(){if (data.status == 200) {
                    swal("Exito", data.statusText, "success");
                    $('#importar').modal('hide');
                    ReloadGrid();
                }
                else {
                    swal("Error", data.statusText, "error");
                }
            },1000)
        },
    });
});
</script>




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
            url:'/api/gruposervicios/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
			height:'auto',
            postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['Clave','Descripción',"Acciones"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'Cve_GpoServicio',index:'Cve_GpoServicio',width:198, editable:false, sortable:false},
                {name:'Des_GpoServicio',index:'Des_GpoServicio',width:735, editable:false, sortable:false},
                {name:'myac',index:'', width:100, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'Cve_GpoServicio',
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

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //if($("#permiso_editar").val() == 1)
                html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" title="Editar" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //if($("#permiso_eliminar").val() == 1)
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
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

     function ReloadGrid1() {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
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

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                Cve_GpoServicio : _codigo,
                action : "inUse"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/gruposervicios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    swal(
                        '¡Alerta!',
                        'El grupo de servicios esta siendo usado en este momento',
                        'warning'
                    );
                    //$('#codigo').prop('disabled', true);
                    //ReloadGrid();
                }
                else {
                        swal({
                            title: "¿Está seguro que desea borrar el grupo de servicios?",
                            text: "Está a punto de borrar un grupo de servicios y esta acción no se puede deshacer",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: true
                            },

                            function(){
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        almacen: <?php echo $_SESSION['id_almacen']; ?>,
                                        Cve_GpoServicio : _codigo,
                                        action : "delete"
                                    },
                                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
                                    url: '/api/gruposervicios/update/index.php',
                                    success: function(data) {
                                        if (data.success == true) {
                                            swal("Borrado", "El registro ha sido borrado exitosamente", "success");
                                            ReloadGrid();
                                            ReloadGrid1();
                                        } else{
                                            swal("Error", "Ocurrió un error al eliminar el registro", "error");
                                        }
                                    }
                            });
        
                        });
                }
            }
        });
    }

    function editar(_codigo) {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Grupo de Art&iacute;culos</h4>');
		 $("#CodeMessage").html("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/gruposervicios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#codigo').prop('disabled', true);
                    $("#codigo").val(data.codigo);
                    $("#descripcion").val(data.descripcion);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function copiar(_codigo) {
        $("#modaltitle_copiar").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Copiar Grupo de Art&iacute;culos</h4>');
         $("#CodeMessage").html("");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                codigo : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/gruposervicios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    $('#codigo_copiar').prop('disabled', true);
                    $("#codigo_copiar").val(data.codigo);
                    $("#descripcion_copiar").val(data.descripcion);
                    l.ladda('stop');
                    $("#btnCancel_copiar").show();
                    $modal0 = $("#myModal_copiar");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }


    $("#btnSave_copiar").click(function(){

        if($("#grupo_c").val() == "")
        {
            swal("Error", "Debe Seleccionar un grupo a copiar", "error");
            return;
        }

        console.log("id_almacen_actual: ", <?php echo $_SESSION['id_almacen']; ?>);
        console.log("cve_grupo_actual: ", $("#codigo_copiar").val());
        console.log("id_grupo_destino: ", $("#grupo_c").val());

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_almacen_actual: <?php echo $_SESSION['id_almacen']; ?>,
                cve_grupo_actual : $("#codigo_copiar").val(),
                id_grupo_destino : $("#grupo_c").val(),
                action : "copiar"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/gruposervicios/update/index.php',
            success: function(data) {
                console.log("SUCCESS Copiar", data);
                swal("Éxito", "Grupo de Servicios Copiado con éxito", "success");
                $("#myModal_copiar").modal('hide');
            }, error: function(data){
                console.log("ERROR Copiar", data);
            }
        });

    });

    function agregar() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Grupo de Servicios</h4>');
        $modal0 = $("#myModal");
		 $("#CodeMessage").html("");
        $modal0.modal('show');
        l.ladda('stop');
        $('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#codigo").val("");
        $("#descripcion").val("");
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        if ($("#codigo").val()=="") {
            return;
        }

        if ($("#descripcion").val()=="") {
            return;
        }

        $("#btnCancel").hide();

        l.ladda( 'start' );

        if ($("#hiddenAction").val()=="add") {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    almacen: <?php echo $_SESSION['id_almacen']; ?>,
                    clave: $("#codigo").val(),
                    action: "exists"
                },
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/gruposervicios/update/index.php',
                success: function (data) {
                    if (data.success == false) {
                        $.post('/api/gruposervicios/update/index.php',
                        {
                            almacen: <?php echo $_SESSION['id_almacen']; ?>,
                            codigo : $("#codigo").val(),
                            descripcion : $("#descripcion").val(),
                            action : $("#hiddenAction").val()
                        },
                        function(response){
                            console.log(response);
                        }, "json")
                        .always(function() {
                            $("#codigo").val("");
                            $("#descripcion").val("");
                            l.ladda('stop');
                            $("#btnCancel").show();
                            $modal0.modal('hide');
                            ReloadGrid();
                        });
                    } else {
                        alert("La clave del grupo ya Existe...");
                        $("#btnCancel").show();
                        l.ladda( 'stop' );
                    }
                }, error: function (data) {
                    console.log("ERROR", data);
                }
            });
        } else {
            $.post('/api/gruposervicios/update/index.php',
            {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                codigo : $("#codigo").val(),
                descripcion : $("#descripcion").val(),
                action : $("#hiddenAction").val()
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                $("#codigo").val("");
                $("#descripcion").val("");
                l.ladda('stop');
                $("#btnCancel").show();
                $modal0.modal('hide');
                ReloadGrid();
            });
        }



    });
	
	
	$("#codigo").keyup(function(e) {

        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

			 var clave = $(this).val();
        /*
		$.ajax({
			type: "POST",
			dataType: "json",
			data: {
                clave : clave,
				action : "exists"
			},
			beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
			},
			url: '/api/gruposervicios/update/index.php',
			success: function(data) {
                if (data.success == false) {
                   $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
             }else{
            $("#CodeMessage").html(" Clave de tipo de articulo ya existe");
            $("#btnSave").prop('disabled', true);
        }
				}
			
		});
            
            */
			
        }else{
            $("#CodeMessage").html("Por favor, ingresar una Clave de tipo válida");
            $("#btnSave").prop('disabled', true);
        }
    });
	
	$("#txtCriterio").keyup(function(event){
    if(event.keyCode == 13){
        $("#buscarA").click();
    }
});

	$("#txtCriterio1").keyup(function(event){
    if(event.keyCode == 13){
        $("#buscarR").click();
    }
});

</script>

<script>
//GRID DE RECUPERAR
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
			url:'/api/gruposervicios/lista/index_i.php',
            datatype: "json",
            shrinkToFit: false,
			height:'auto',
            postData: {
                almacen: <?php echo $_SESSION['id_almacen']; ?>,
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:['id','Clave','Descripción',"Acciones"],
            colModel:[
                {name:'id',index:'id', width:110, editable: false,hidden:true},
                {name:'Cve_GpoServicio',index:'Cve_GpoServicio',width:200, editable:false, sortable:false},
                {name:'Des_GpoServicio',index:'Des_GpoServicio',width:600, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'Cve_GpoServicio',
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
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

			var id = rowObject[0];

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+id+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
	
	        $("#inactivos").on("click", function(){
            $modal0 = $("#coModal");
            $modal0.modal('show');
        });
</script>

<script>
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
            url: '/api/gruposervicios/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
					ReloadGrid1();
                }
            }
        });
    }
	$(document).ready(function(){
		$("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
	});
</script>



         <style>

<?php /* if($edit[0]['Activo']==0){?>

.fa-edit{

    display: none;

}

<?php }?>


<?php if($borrar[0]['Activo']==0){?>

.fa-eraser{

    display: none;

}

<?php } */ ?>

</style>