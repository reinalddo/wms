<?php
$listaAlm = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();

$mod=36;
$var1=101;
$var2=102;
$var3=103;
$var4=104;

$vere = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>

<!-- Mainly scripts -->

<style type="text/css"></style>
<input type="hidden" id="fol_cambio" value="">

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Área de Embarque</h4>
            </div>
            <form id="myform">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Almacén *</label>
                        <select class="form-control" id="almacen" name="almacen" required="true">
                            <?php foreach( $listaAlm->getAll() AS $p ): ?>
                                <option value="<?php echo $p->id; ?>"><?php echo"($p->clave)". $p->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <label>Clave *</label>
                    <input id="clave" type="text" placeholder="Clave" class="form-control" required="true"><!--<label id="CodeMessage" style="color:red;"></label>--><br>
                    <label>Descripción *</label>
                    <input id="descripcion" type="text" placeholder="descripcion" class="form-control" required="true">
                    <br>
                    <div class="form-group">
                        <input type="checkbox" name="stagging" id="stagging">
                        <label for="checkbox2">Área de Stagging</label>
                    </div>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenId_Embarque">	
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Área de Embarque</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="txtCriterio" id="txtCriterio1" placeholder="Buscar...">
                            <div class="input-group-btn">
                                <button onclick="ReloadGrid1()" type="submit" id="buscarA" class="btn btn-primary">
                                    <span class="fa fa-search"></span>Buscar
                                </button>
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


    <div class="modal fade" id="modal_zonasembarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Zonas de Embarque</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="zonaembarque">Zonas Disponibles</label>
                                <select id="zonaembarque" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarZonaEmbarque()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="pedidos_ws_modal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Pedidos WS</h4>
                </div>
                <div class="modal-body">

<!-- *************************************************************************** -->
<!-- *************************************************************************** -->
  
  <div class="panel-group" style="max-height: 500px;overflow-y: scroll;">
    <div class="panel panel-default" id="acordeon_ws">


    </div>
  </div>

<!-- *************************************************************************** -->
<!-- *************************************************************************** -->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="wrapper wrapper-content  animated fadeInRight">
    <h3>Área de Embarque</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="almacenes">Almacen:</label>
                                <select class="form-control" id="almacenes" name="almacenes" >
                                    <option value=" ">Seleccione el Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->clave; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="area_stagging">Area Stagging:</label>
                                <select class="form-control" id="area_stagging" name="area_stagging" >
                                    <option value="">Todas</option>
                                    <option value="S">Si</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-8">

                                <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>

                            <a href="/api/v2/area-de-embarque/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px;"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px;" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                            <button  class="btn btn-primary pull-right" type="button" id="inactivos"><i class="fa fa-search"></i>&nbsp;&nbsp;Áreas de embarque inactivas</button>
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
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<!-- Select -->
<script src="/js/select2.js"></script>

<div class="modal fade" id="importar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Importar áreas de embarque</h4>
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

$('#btn-import').on('click', function() 
{
    $('#btn-import').prop('disable', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');

    var formData = new FormData();
    formData.append("clave", "valor");

    $.ajax({
        url: '/area-de-embarque/importar',
        type: 'POST',
        data: new FormData($('#form-import')[0]),
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() 
        {
            $('.progress').show();
            var percentVal = '0%';
            bar.width(percentVal);
            percent.html(percentVal);
        },
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) 
            {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) 
                {
                    if (e.lengthComputable) 
                    {
                        var percentComplete = e.loaded / e.total;
                        percentComplete = parseInt(percentComplete * 100);
                        bar.css("width", percentComplete + "%");
                        percent.html(percentComplete+'%');
                        if (percentComplete === 100) 
                        {
                            setTimeout(function(){$('.progress').hide();}, 2000);
                        }
                    }
                } , false);
            }
            return myXhr;
        },
        success: function(data) 
        {
            setTimeout(
                function()
                {
                    if (data.status == 200) 
                    {
                        swal("Exito", data.statusText, "success");
                        $('#importar').modal('hide');
                        ReloadGrid();
                    }
                    else 
                    {
                        swal("Error", data.statusText, "error");
                    }
                },1000)
        },
    });
});
</script>

<script type="text/javascript">
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede()
    { 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.clave;
                    setTimeout(function() 
                    {
                        ReloadGrid();
                    }, 1000);
                }
            },
            error: function(res)
            {
                window.console.log(res);
            }
        });
    }
    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) 
    {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () 
        {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) 
        {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() 
                {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/areaembarque/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['ID', "Acciones",'Clave','Descripción','Status', "Area Stagging",'Almacén'],
            colModel:[
                {name:'ID_Embarque',index:'ID_Embarque', width:60, sorttype:"int", editable: false,hidden: true},
                {name:'myac',index:'', width:100, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'cve_ubicacion',index:'cve_ubicacion',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:383, editable:false, sortable:false},
                {name:'status',index:'status',width:100, editable:false, sortable:false},
                {name:'AreaStagging',index:'AreaStagging',width:100, editable:false, sortable:false},
                {name:'nombre',index:'nombre',width:200, editable:false, sortable:false},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'ID_Embarque',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: almacenPrede()
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
         );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function imageFormat( cellvalue, options, rowObject )
        {
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var clave = rowObject[2];
            var is_stagging = rowObject[5];
            $("#hiddenId_Embarque").val(serie);
            //var correl = rowObject[4];
            //var url = "x/?serie="+serie+"&correl="+correl;
            //var url2 = "v/?serie="+serie+"&correl="+correl;
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //console.log(html);
            if($("#permiso_eliminar").val() == 1)
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if(is_stagging == 'SI')
                html += '<a href="#" onclick="detalle_pedidos(\'' + clave + '\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) 
        {
            setTimeout(function()
            {
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) 
        {
            setTimeout(function()
            {
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if(form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
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

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {}

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {}

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {}

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) 
        {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    $("#area_stagging").change(function(){
        ReloadGrid();
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() 
    {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
                almacen: $("#almacenes").val(),
                area_stagging: $("#area_stagging").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function ReloadGrid1() 
    {
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio1").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) 
    {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function detalle_pedidos(clave) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_ubicacion : clave,
                action : "pedidos_ws"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/areaembarque/update/index.php',
            success: function(data) 
            {
                console.log("data = ", data);
                $("#acordeon_ws").empty();
                $("#acordeon_ws").append(data.html);
                $("#pedidos_ws_modal").modal("show");
            }, error: function(data) 
            {
                console.log("ERROR = ", data);
            }

        });
    }


        function enviarAEbarque_o_Qa(folio_pedido) {

            console.log("folio_pedido = ", folio_pedido);
            swal({
                    title: "Enviar Pedido "+folio_pedido,
                    text: "¿A donde desea enviar el pedido?",
                    type: "success",
                    allowOutsideClick: false, 
/*
                    showCancelButton: true,
                    cancelButtonText: "Enviar a QA (Auditar)",
                    cancelButtonColor: "#14960a",
*/
                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Enviar a embarque",
                    closeOnConfirm: true, 

                    showCancelButton: true,
                    cancelButtonText: "Cancelar",
                    cancelButtonColor: "#14960a"
                },
                function(e) {
                    if (e == true) {
                        //Embarque
                        obtenerZonasDisponibles(folio_pedido);
                    } else {
                        //QA
                        /*
                        var status = 'L';
                        var folio = folio_pedido;

                        console.log("enviarAEbarque_o_Qa()");
                        console.log("folio = ", folio);
                        console.log("sufijo = 0");
                        console.log("status = ", status);
                        console.log("enviarAEbarque_o_Qa()");

                        $.ajax({
                                url: '/api/administradorpedidos/update/index.php',
                                data: {
                                    folio: folio_pedido,
                                    sufijo: 0,
                                    status: 'L',
                                    almacen: "<?php //echo $_SESSION['id_almacen']; ?>",
                                    motivo: 'QASEND',
                                    action: 'cambiarStatus'
                                },
                                type: 'POST',
                                dataType: 'json'
                            })
                            .done(function(data) {
                                console.log("QA -> ",data);
                                console.log("QA data.success -> ",data.success);
                                console.log("QA data.msj -> ",data.msj);
                                console.log("QA data.folio -> ",data.folio);
                                console.log("QA data.status -> ",data.status);

                                window.location.reload(); //PROVISIONAL MIENTRAS SE ARREGLA LO DE CAMBIO DE ESTATUS 
                                if (data.success == true) {
                                    //filtralo();
                                    //$('#modalItems').modal('hide');
                                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                                    window.location.reload();
                                } 
                                //else swal("Cambio No Permitido", data.msj, "error");//PROVISIONAL MIENTRAS SE ARREGLA LO DE CAMBIO DE ESTATUS 
                            });
                            */
                    }
                });
        }

        function obtenerZonasDisponibles(folio_pedido) {
            console.log("folio_pedido Zona = ", folio_pedido);
            console.log("almacen Zona = ", $('#almacen').val());
            console.log("almacen: ", "<?php echo $_SESSION['cve_almacen']; ?>");
            $.ajax({
                    url: '/api/administradorpedidos/lista/index.php',
                    data: {
                        folio: folio_pedido,
                        almacen: "<?php echo $_SESSION['cve_almacen']; ?>",
                        modulo_areaembarque: true,
                        action: 'obtenerZonasEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {

                        options = '';
                        $.each(data.data, function(key, value) {
                            options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                        });

                        $('#zonaembarque').html(options);
                        $('#zonaembarque').trigger("chosen:updated");
                        $('#modal_zonasembarque').modal('show');
                        $('#pedidos_ws_modal').modal('hide');
                        $("#fol_cambio").val(folio_pedido);
                    } else {

                    }
                });
        }

        function asignarZonaEmbarque() {
            //console.log("sufijo asignarZonaEmbarque() = ", window.detalle_pedido["articulos"][0]["sufijo"]);

            //console.log("folio:", $("#fol_cambio").val());
            //console.log("almacen:", $('#almacen').val());
            //console.log("status:", 'C');
            //console.log("sufijo:", 0);
            //console.log("separar_ola:", true);
            //console.log("zonaembarque:", $('#zonaembarque').val());
            //console.log("action:", 'asignarZonaEmbarque');
            //return;

            $.ajax({
                    url: '/api/administradorpedidos/update/index.php',
                    data: {
                        folio: $("#fol_cambio").val(),
                        almacen: $('#almacen').val(),
                        status: 'C',
                        sufijo: 0,
                        separar_ola: true,
                        zonaembarque: $('#zonaembarque').val(),
                        action: 'asignarZonaEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {
                        $('#modal_zonasembarque').modal('hide');
                        //$('#modalItems').modal('hide');
                        //swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                        swal("Éxito", "Pedido Enviado a Embarques exitosamente", "success");

                        console.log("filtralo 2");
                        $('#modal_zonasembarque').modal('hide');
                        //filtralo();
                        //window.location.reload();
                    } else {
                        //swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                        swal("Error", "Ocurrió un error al enviar el pedido a Embarques", "success");
                    }
                }).fail(function(data){
                    console.log("ERROR EMBARQUE = ", data);
                });
        }

    $modal0 = null;

    function borrar(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_ubicacion : _codigo,
                action : "inUse"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/areaembarque/update/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    swal(
                        '¡Alerta!',
                        'El area de embarque esta siendo usada en este momento',
                        'warning'
                    );
                }
                else 
                {
                    swal({
                        title: "¿Está seguro que desea borrar el area de embarque?",
                        text: "Está a punto de borrar un area de embarque y esta acción no se puede deshacer",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Borrar",
                        cancelButtonText: "Cancelar",
                        closeOnConfirm: true
                    },
                    function()
                    {
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                cve_ubicacion : _codigo,
                                action : "delete"
                            },
                            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                            url: '/api/areaembarque/update/index.php',
                            success: function(data) 
                            {
                                if (data.success == true) 
                                {
                                    //$('#codigo').prop('disabled', true);
                                    ReloadGrid();
                                    ReloadGrid1();
                                }
                            }
                        });
                    });
                }
            }
        });
    }

    function editar(_codigo) 
    {
        console.log("_codigo = ", _codigo);
        $("#CodeMessage").html("");
        $("#hiddenId_Embarque").val(_codigo);
        $("#clave").prop("disabled",true);
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Área de Embarque</h4>');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_ubicacion : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/areaembarque/update/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    console.log(data);
                    $("#almacen").val(data.cve_almac);
                    $("#clave").val(data.cve_ubicacion);
                    $("#descripcion").val(data.descripcion);
                    if(data.AreaStagging == "S")
                    {
                        $("#stagging").iCheck('check');
                    }
                    else if(data.AreaStagging == "N")
                    {
                        $("#stagging").iCheck('uncheck');
                    }

                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");
                }
            }
        });
    }

    function agregar() 
    {
        $("#CodeMessage").html("");
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Área de Embarque</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');

        l.ladda('stop');
        //$('#codigo').prop('disabled', false);
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#clave").val("");
        $("#stagging").iCheck('uncheck');
        $("#clave").prop("disabled",false);
        $("#descripcion").val("");

    }
    console.log($("#hiddenId_Embarque").val());

    var l = $( '.ladda-button' ).ladda();
    l.click(function() 
    {

        console.log("almacen = ", $('#almacen').val());
        console.log("clave = ", $('#clave').val());
        console.log("descripcion = ", $('#descripcion').val());
        console.log("hiddenAction = ", $('#hiddenAction').val());

        if ($('#almacen').val()=="") 
        {
            swal("Almacén Vacío", "Debe seleccionar un almacén", "error");
            return;
        }

        if ($('#clave').val()=="") 
        {
            swal("Clave Vacía", "Debe escribir una clave", "error");
            return;
        }

        if ($('#descripcion').val()=="") 
        {
            swal("Descripción Vacía", "Debe escribir una Descripción", "error");
            return;
        }
        var staggingSelect;
        if($("#stagging")[0].checked == true)
        {
            staggingSelect = "S";
        }
        else
        {
            staggingSelect = "N";
        }
        $("#btnCancel").hide();

        l.ladda( 'start' );

        $.post('/api/areaembarque/update/index.php',
        {
            cve_ubicacion : $("#clave").val(),
            cve_almac : $("#almacen").val(),
            descripcion : $("#descripcion").val(),
            ID_Embarque:  $("#hiddenId_Embarque").val(),
            stagging : staggingSelect,
            action : $("#hiddenAction").val()
        },
        function(response)
        {
            console.log(response);
        }, "json")
        .always(function() 
        {
            $("#btnCancel").show();
            $("#almacen").val("");
            $("#clave").val("");
            $("#descripcion").val("");
            l.ladda('stop');
            $("#btnCancel").show();
            $modal0.modal('hide');
            ReloadGrid();
        });
    });

</script>

<script>
    $("#clave").keyup(function(e) 
    {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
            var clave = $(this).val();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ubicacion : clave,
                    action : "exists"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                url: '/api/areaembarque/update/index.php',
                success: function(data) {
                    if (data.success == false) 
                    {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    }
                    else
                    {
                        $("#CodeMessage").html(" Clave de Área de Embarque ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }
            });
        }
        else
        {
            $("#CodeMessage").html("Por favor, ingresar una Clave de Área de Embarque válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#txtCriterio").keyup(function(event)
    {
        if(event.keyCode == 13)
        {
            $("#buscarA").click();
        }
    });

    $("#txtCriterio1").keyup(function(event)
    {
        if(event.keyCode == 13)
        {
            $("#buscarR").click();
        }
    });
</script>

<script>
    $(function($) 
    {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function () 
        {
            var $grid = $("#grid-table2"),
                newWidth = $("#coModal .modal-body").width() - 60;
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) 
        {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() 
                {
                    $(grid_selector).jqGrid( 'setGridWidth', $("#coModal .modal-body").width() - 60 );
                }, 100);
            }
        })

        $(grid_selector).jqGrid({			
            url:'/api/areaembarque/lista/index_i.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames:['ID','Clave','Almacén','Descripción','Status',"Area de Stagging","Recuperar"],
            colModel:[
                {name:'ID_Embarque',index:'ID_Embarque', width:60, sorttype:"int", editable: false,hidden: true},
                {name:'cve_ubicacion',index:'cve_ubicacion',width:110, editable:false, sortable:false},
                {name:'nombre',index:'nombre',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:500, editable:false, sortable:false},
                {name:'status',index:'status',width:100, editable:false, sortable:false},
                {name:'AreaStagging',index:'AreaStagging',width:150, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:10,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'ID_Embarque',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
           {edit: false, add: false, del: false, search: false},
           {height: 200, reloadAfterSubmit: true}
        );
       
        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function imageFormat( cellvalue, options, rowObject )
        {
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            var ID_Embarque = rowObject[0];

            $("#hiddenId_Embarque").val(ID_Embarque);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="recovery(\''+ID_Embarque+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) 
        {
            setTimeout(function()
            {
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) 
        {
            setTimeout(function()
            {
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() 
        {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data)
                {
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) 
        {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {}

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {}

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {}

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

<script>
    function recovery(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Embarque : _codigo,
                action : "recovery"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/areaembarque/update/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    //$('#codigo').prop('disabled', true);
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
    }

    $(document).ready(function()
    {
        $("#inactivos").on("click", function()
        {
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });
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