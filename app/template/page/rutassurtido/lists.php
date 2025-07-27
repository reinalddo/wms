<?php
    $listaAlm = new \Almacen\Almacen();
    $almacenes = new \AlmacenP\AlmacenP();
?>
<!DOCTYPE html>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/downloads/moment.js"></script>
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

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Drag & Drop Panel -->
<script src="/js/dragdrop.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>

<!-- Clock picker -->
<script src="/js/plugins/clockpicker/clockpicker.js"></script>

<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<!-- Sweet Alert -->
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style>
    #dragUbi li{
      cursor: pointer;
    }
    #dragUbicaciones li{
      cursor: pointer;
    }
    .wi{
      width: 90% !important;
    }
    .relative{
      position: relative;
    }
    .relative .floating-button{
      position: absolute;
      right: 0;
      top: 50%;

    }
    [aria-grabbed="true"]
    {
      background: #1ab394 !important;
      color: #fff !important;
    }
    .witwo{
      width: 100% !important;

    }
    .ret{
      margin-left: 5px !important;
      margin-top: 1px !important;
      margin-bottom: 1px !important;
      padding: 1px;
    }
    #fromLugar>li>input[type=number]
    {
      display:none;
    }
</style>
<style>
    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
</style>

<div class="wrapper wrapper-content  animated" id="list">
    <h3>Rutas de Surtido</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="almacenes">Almacen:</label>
                                <select class="form-control" id="almacenes" name="almacenes" onchange="almacen(); ReloadGrid();">
                                    <option value=" ">Seleccione el Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->clave; ?>"><?php echo $a->clave." - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="email">Zona de Almacenaje</label>
                                <select name="zona" id="zona" class="chosen-select form-control" onchange="carga_ubicaciones()"></select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Proveedores</label>
                              <select name="proveedores" id="proveedores" class="chosen-select form-control" onchange="carga_ubicaciones()">
                                  <option value="">Seleccione un Proveedor</option>
                              </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <a href="#">
                                        <button type="submit" id="ordenar" class="btn btn-sm btn-primary"><i class="fa fa-sort-amount-asc" alt="Ordenar"></i>
                                            Ordenar Secuencia de surtido
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <button type="button" onclick="modalGuardar()" id="guardar" class="btn btn-sm btn-primary"><i class="fa fa-save" alt="Guardar"></i>
                                        Guardar Ruta de surtido
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php 
                        /*
                        ?>
                      <button class="btn btn-primary pull-right" style="margin-left:20px; ; margin-top: 30px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                      <?php 
                      */
                      ?>
                    </div>
                </div> 
                <div class="ibox-content dragdrop">
                    <div class="form-group">
                        <div class="col-sm-12" style="margin-bottom: 30px">
                            <input type="checkbox" id="select_lugar_todos" onclick="selectAllCheckbox('select_lugar_todos','fromLugar')">
                            <label for="select_lugar_todos">Seleccionar Todas las Ubicaciones</label>
                        </div>
                    </div>
                    <div class="form-group" id="dragUbi">
                        <div class="col-md-6 relative">
                            <label for="email">Ubicaciones Disponibles (<span id="ub_disp"></span>)</label>
                            <ol data-draggable="target" id="fromLugar" class="wi"></ol>
                            <button class="btn btn-primary floating-button" onclick="add('#fromLugar', '#lugar_asignado')">>></button>
                            <button class="btn btn-primary floating-button" onclick="remove('#lugar_asignado', '#fromLugar')" style="margin-top: 40px"><<</button>
                        </div>
                        <div class="col-md-6">
                            <label for="email">Ubicaciones Asignadas</label>
                            <ol data-draggable="target" id="lugar_asignado" class="wi"></ol>
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
                        <h4 class="modal-title">Importar Artículos</h4>
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
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                      <div class="col-md-6" style="text-align: right">
                          <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            <!--<button class="btn btn-primary floating-button" onclick="importar_rutas()" id="clickme">Clickme</button> -->
            <!--<div class="ibox-content">
                <div class="jqGrid_wrapper">
                    <table id="grid-table"></table>
                    <div id="grid-pager"></div>
                </div>
            </div>-->
        </div>
    </div>
</div>

<div class="modal inmodal" id="coModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Editar Ruta de Surtido</h4>
            </div>
            <form id="myform">
                <div class="modal-body">
                    <div class="form-group" style="display:none"><label>ID</label> <input id="id" type="text" class="form-control" disabled="disabled"></div>
                    <div class="form-group"><label>Almacen</label> <input id="almacen" type="text" placeholder="Almacen" class="form-control" disabled="disabled"><!--<label id="CodeMessage" style="color:red;"></label>--></div>
                    <div class="form-group"><label>Secuencia de Surtido *</label> <input id="secuencia" type="text" maxlength="6" class="form-control" required="true"></div>
                    <div class="form-group"><label>Nombre de Secuencia *</label> <input id="nombsecuencia" type="text" class="form-control" required="true"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="button" class="btn btn-primary ladda-button" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal articulo -->
<div class="modal fade" id="modal_guardar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 70% !important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Guardar ruta de surtido</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Indique nombre de la ruta</label>
                            <input class="form-control" id="nombre_ruta" value="">
                        </div>
                    </div>
                    <!--  <div class="row">
                        <div class="col-md-12">
                            <label>Seleccione un surtidor</label>
                            <select class="form-control" id="surtidor_ruta"></select>
                        </div>
                    </div> -->
                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-12" style="margin-bottom: 30px">
                                <input type="checkbox" id="selectAll" onclick="selectAllCheckbox('selectAll','fromU')">
                                <label for="selectAll">Seleccionar Todo</label>
                            </div>
                        </div>
                        <div class="form-group" id="dragUbicaciones">
                            <div class="col-md-6 relative">
                                <label for="email">Surtidores Disponibles</label>
                                <ol data-draggable="target" id="fromU" class="wi" style="width: 90% !important;"></ol>
                                <div style="position: absolute;right: 0;top: 35%; display: inline-grid;">
                                    <button class="btn btn-primary floating-button" onclick="add('#fromU', '#toU')" title="Agregar">>></button>
                                    <button class="btn btn-primary floating-button" onclick="remove('#toU', '#fromU')" title="Quitar" style="margin-top: 40px"><<</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email">Surtidores a asignar</label>
                                <ol data-draggable="target" id="toU" class="wi"></ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button onclick="cerrarmodal()" type="button" class="btn btn-default" id="volverSurtido">Cancelar</button>
                    <button onclick="guardandoSurtido()" type="button" class="btn btn-primary" id="guardarSurtido">Guardar Ruta de Surtido</button>
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
<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/bootstrap-imageupload.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>
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
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.clave;
                    almacen();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
       ReloadGrid();
    }

    $(function($) 
    {
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
        var lastsel;

        $(grid_selector).jqGrid({
            url:'/api/rutassurtido/lista/index.php',
            datatype: "json",
            contentType: "application/json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#criteriob").val()
            },
            mtype: 'POST',
            colNames:['ID','Almacen',"Zona de Almacenaje", "Tipo de Ubicación","Secuencia de Surtido*","BL"/* "Pasillo", "Rack", "Nivel", "Seccion", "Posicion", "Perfil"*/],
            colModel:[
                {name:'idy_ubica',index:'idy_ubica', width: 80, editable:false, sortable:false, hidden: true},
                {name:'almacen',index:'almacen', width: 180, editable:false, sortable:false},
                {name:'zona',index:'zona', width: 150, editable:false, sortable:false},
                {name:'tipo_u',index:'tipo_u', width: 150, editable:false, sortable:false},
                {name:'orden_secuencia',index:'orden_secuencia',  width: 180, editrules:{integer: true}, editable:true, edittype:'text', sortable:true},
                {name:'bl',index:'bl',  width: 100, editable:false, sortable:false, resizable: false},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'idy_ubica',
            viewrecords: true,
            editurl: '/api/rutassurtido/lista/index.php', 
            onSelectRow: function (orden_secuencia) {
               if (orden_secuencia && orden_secuencia !== lastsel) 
               {
                   jQuery('#grid-table').restoreRow(lastsel);
                   lastsel = orden_secuencia;
               }
               jQuery('#grid-table').editRow(orden_secuencia, true, false);
            },
            sortorder: "asc",
            loadComplete: almacenPrede()
        });

        $("#ordenar").click(function() {
             var aux;
             var idy_ubica;
             var k = 0;
             var cont = 0;
             var ordenado = [];
             var rows= jQuery("#grid-table").jqGrid('getRowData');
             console.log(rows);
             for(var i=1;i<rows.length;i++)
             {
                for(var j=0;j<rows.length-i;j++)
                {
                    if(Number(rows[j]['orden_secuencia'])>0 && Number(rows[i]['orden_secuencia'])>0)
                    {
                        if(Number(rows[j]['orden_secuencia'])>Number(rows[j+1]['orden_secuencia']))
                        {
                            aux=Number(rows[j+1]['orden_secuencia']);
                            idy_ubica=rows[j+1]['idy_ubica'];
                            rows[j+1]['orden_secuencia']=Number(rows[j]['orden_secuencia']);
                            rows[j]['orden_secuencia']=aux;
                            rows[j+1]['idy_ubica'] = rows[j]['idy_ubica'];
                            rows[j]['idy_ubica'] = idy_ubica;
                        }
                    } 
                }
            }
            for(var i=0;i<rows.length;i++)
            {
               if(Number(rows[i]['orden_secuencia'])>0)
               {
                    rows[i]['orden_secuencia'] = k+1;
                    ordenado[k] = rows[i];
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            idy_ubica : ordenado[k]['idy_ubica'],
                            orden_secuencia : ordenado[k]['orden_secuencia'],
                            oper: "edit"
                        },
                        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                        },
                        url: '/api/rutassurtido/lista/index.php',
                        success: function(data) {
                            if (data.success == true) 
                            {
                                console.log("Actualizo");
                            }
                        }
                    });
                    k++;
                }
            }
           jQuery("#grid-table").jqGrid('setGridParam',{sortname:"orden_secuencia",sortorder:"asc"}).trigger('reloadGrid',[{current:true}]);
        });

        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function aceSwitch( cellvalue, options, cell ) 
        {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) 
        {
            setTimeout(function(){
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

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
    
    function selectAllCheckbox(check,checkGroup)
    {
        $('#'+checkGroup+' li input[type="checkbox"].drag').prop('checked', $('#'+check).is(":checked"));
    }

    function almacen()
    {
        $('#zona').find('option').remove().end();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave : $('#almacenes').val(),
                action : "traerZonasDeAlmacenP"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data){
                if (data.success == true) 
                {
                    var options = $("#zona");
                    options.empty();
                     //options.append(new Option("Zona de Almacenaje", ""));
                    for (var i=0; i<data.zonas.length; i++)
                    {
                        options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                    carga_ubicaciones();
                    proveedor();
                }
            }
        });
        
        
    }
  
    function proveedor()
    {
      $('#proveedores').find('option').remove().end();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave_almacen : $('#almacenes').val(),
                action : "traerProveedores"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data){
                if (data.success == true) 
                {
                    var options = $("#proveedores");
                    options.empty();
                     options.append(new Option("Seleccione un Proveedor", ""));
                    for (var i=0; i<data.proveedores.length; i++)
                    {
                        options.append(new Option(data.proveedores[i].Nombre, data.proveedores[i].ID_Proveedor));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                    carga_ubicaciones();                
                }
            }
        });
    }
  
    function surtidores()
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/rutassurtido/lista/index.php',
            data: {
                action : "traerSurtidores",
                almacen : $('#almacenes').val()
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                if (data["success"] == true) 
                {
                    var options = $("#surtidor_ruta");
                    options.empty();
                    options.append(new Option("Seleccionar", "0"));
                    for (var i=0; i<data["user"].length; i++)
                    {
                        options.append(new Option(data["user"][i].nombre, data["user"][i].id));
                    }
                    $('.chosen-select').trigger("chosen:updated");

                    for (var i=0; i<data["user"].length; i++)
                    {
                        var ul = document.getElementById("fromU");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("value", data["user"][i].id);
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data["user"][i].nombre));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data["user"][i].id);
                        ul.appendChild(li);
                    }
                }
            }
        });
    }
  
    function carga_ubicaciones()
    {
        $("#select_lugar_todos").iCheck('uncheck');
        $("#fromLugar .itemlist").remove();
        $("#lugar_asignado .itemlist").remove();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/rutassurtido/lista/index.php',
            data: {
                action : "cargarUbicaciones",
                almacen : $('#almacenes').val(),
                zona: $("#zona").val(),
                proveedores: $("#proveedores").val(),
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            success: function(data) 
            {
                console.log(data);
                $("#ub_disp").text(data.length);
                for (var i=0; i < data.length; i++)
                {
                    var ul = document.getElementById("fromLugar");
                    var li = document.createElement("li");
                    var orden = document.createElement("input");
                    orden.setAttribute("id", data[i].id);
                    orden.style.marginRight = "5px";
                    orden.style.width = "35px";
                    orden.setAttribute("type", "number");
                    var checkbox = document.createElement("input");
                    checkbox.style.marginRight = "10px";
                    checkbox.setAttribute("type", "checkbox");
                    checkbox.setAttribute("value", data[i].id);
                    checkbox.setAttribute("class", "drag");
                    checkbox.setAttribute("onclick", "selectParent(this)");
                    li.appendChild(checkbox);
                    li.appendChild(orden);
                    li.appendChild(document.createTextNode(data[i].cell[1]+" - "+data[i].cell[2]+" - "+data[i].cell[3]));
                    li.setAttribute("dayta-draggable", "item");
                    li.setAttribute("draggable", "false");
                    li.setAttribute("aria-draggable", "false");
                    li.setAttribute("aria-grabbed","false");
                    li.setAttribute("tabindex","0");
                    li.setAttribute("class","itemlist");
                    li.setAttribute("onclick","selectChild(this)");
                    li.setAttribute("value",data[i].id);
                    ul.appendChild(li);
                }
            }
        });
    }

    function add(from, to)
    {
        var elements = document.querySelectorAll(`${from} input.drag:checked`), li, newli;
        var i = 0;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            console.log(li.value);
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${from}`).removeChild(li);
            document.querySelector(`${to}`).appendChild(newli);
            i++;
        }
    }
  
    function remove(to, from)
    {
        var elements = document.querySelectorAll(`${to} input.drag:checked`), li, newli;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${to}`).removeChild(li);
            document.querySelector(`${from}`).appendChild(newli);
        }
    }
  
    function selectParent(e)
    {
        if(e.checked)
        {
            e.parentNode.setAttribute("aria-grabbed", "true");
        }
        else
        {
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }
  
    function selectChild(e)
    {
        if(e.getAttribute("aria-grabbed") == "true"){
            e.firstChild.checked = true;
        }else{
            e.firstChild.checked = false;
        }
    }

    function ReloadGrid() 
    {
        $('#grid-table').jqGrid('clearGridData').jqGrid(
        'setGridParam', 
        {postData: {
            criterio: $("#txtCriterio").val(),
            almacen: $("#almacenes").val(),
            zona: $("#zona").val(),
            proveedores: $("#proveedores").val(),
        }, 
        datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }
  
    function RecorrerGrid() 
    {
        var arr = [];
        $("#lugar_asignado>li>input[type=number]").each(function() {
            arr.push($(this).attr('id')+"-"+$(this).val());
            console.log(arr);
        });
        for(var j = 0; j < arr.length; j++)
        {
            arr.toString();
            var data = arr[j].split("-");
            var idy_ubica = data[0];
            var valor_secuencia = data[1];
            if(valor_secuencia > 0)
            {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/rutassurtido/update/index.php',
                    data: {
                        action : "guardarOrdenSec",
                        idy_ubica : idy_ubica,
                        orden_secuencia : valor_secuencia
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                    success: function(data) {
                        if (data.success == true) 
                        {
                            console.log("Actualizo");
                        }
                    }
                });
            }
        }
        return true;
    } 
  
    function modalGuardar() 
    {
        var arr = [];
        $("#lugar_asignado>li>input[type=number]").each(function() {
            arr.push($(this).attr('id')+"-"+$(this).val());
        });
        for(var j = 0; j < arr.length; j++)
        {
            arr.toString();
            var data = arr[j].split("-");
            if(data[1] == "")
            {
                var no_ingresar = true;
            }
        }
        if(no_ingresar == true)
        {
            alert("Indique secuencia de surtido en las ubicaciones asignadas");
        }
        else
        {
            surtidores();
            console.log("surtidores");
            $("#selectAll").iCheck('uncheck');
            $("#fromU .itemlist").remove();
            $("#nombre_ruta").val("");
            $("#toU .itemlist").remove();
            $('#modal_guardar').modal('show');
        }
    }
  
    function guardandoSurtido()
    {
        var surtidor = $("#surtidor_ruta").val();
        var nombre = $("#nombre_ruta").val();
        var usuarios = [];

        $("#toU").each(function() {
            var localRels = [];
            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value'));
            });
            if(localRels.length > 0 )
            {
                usuarios = localRels;
            }
        });
        console.log(usuarios);
        console.log(nombre);
        console.log(surtidor);
        if(nombre == "")
        {
            swal("Error", "Indique el nombre de la Ruta", "error");
        }
        else if(usuarios.length <= 0 )
        {
            swal("Error", "Seleccione al menos un surtidor", "error");
        }
        else
        {
            var arr = [];
            $("#lugar_asignado>li>input[type=number]").each(function() {
                arr.push($(this).attr('id')+"-"+$(this).val());
                console.log(arr);
            });
            arr.toString();
            if(typeof arr[0] === 'undefined'){
                swal("Error", "Seleccione una o mas ubicaciones", "error");
                return;
            }
            var data = arr[0].split("-");
            var idy_ubica = data[0];
            var valor_secuencia = data[1];
            $.ajax({
                url: '/api/rutassurtido/update/index.php',
                type: "POST",
                dataType: "json",
                data: {
                    id     : usuarios,
                    nombre : nombre,
                    ubicacion : idy_ubica,
                    action : "guardarRuta"
                },
                success: function(data) {
                    if (data.success == true) 
                    {
                        RecorrerGrid();
                        console.log("Guardado");
                        $('#modal_guardar').modal('hide');
                        $("#nombre_ruta").val("");
                        $("#surtidor_ruta").val("0");
                        swal("Guardado", "La ruta de surtido se ha guardado con éxito", "success");
                        ReloadGrid();
                        setTimeout(function(){location.reload();},1000);
                        surtidores();
                    }
                    else if(data.text != "")
                    {
                        swal("Error", data.text, "error");
                    }
                }
            });
        }
    }
  
    function cerrarmodal()
    {
        $('#modal_guardar').modal('hide');
    }

    
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
  
    $(document).ready(function(){
        setTimeout(function(){carga_ubicaciones();},1000);
    });
  
    function importar_rutas()
    {
        $("#clickme").prop( "disabled", true );
        $.ajax({
            url: '/api/rutassurtido/update/index.php',
            type: "POST",
            dataType: "json",
            data: {
                action : "importar_rutas"
            },
            success: function(data) {
                swal("OK", "ok", "success");
            }
        });
    }
</script>