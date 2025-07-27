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
    <h3>Administrador de Rutas de Surtido*</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control" id="almacenes" name="almacenes">
                                    <?php /* ?><option value=" ">Seleccione el Almacen</option><?php */ ?>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->clave; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                          <label>Buscar</label>
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="buscar()">
                                        <button type="button" class="btn btn-sm btn-primary" id="buscarP">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4" style="margin-bottom: 20px">
                            
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



<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 80% !important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle Ruta de Surtido</h4>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-lg-6">
                      <label>Nombre de la ruta</label>
                      <input  class="form-control input-sm" id="d_nombre" val="" disabled>
                    </div>
                    <!--div class="col-lg-6">
                      <label>Nombre del surtidor</label>
                      <input  class="form-control input-sm" id="d_surtidor" val="" disabled>
                    </div-->
                    <div class="form-group" id="dragUbicaciones">
                        <div class="col-md-12">
                          <label for="email">Surtidores asignados</label>
                          <ol data-draggable="target" id="toU_d" class="wi">
                          </ol>
                        </div>
                      </div>
                  </div>
                  <br>
                    <div class="ibox-content" style="overflow-y: auto; height: 300px; ">
                      <table class="table table-bordered" id="tabla_ubicaciones2">
                        <thead>
                          <tr>
                            <th style="display:none;">ID</th>
                            <th>BL</th>
                            <th>Secuencia de Surtido</th>
                            <th>Tipo de ubicación</th>
                            <th>Zona de almacenaje</th>
                            <th>Almacen</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                    </div>
<!--                     <div class="ibox-content">
                        <div class="<jqGrid_w></jqGrid_w>rapper" id="detalle_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
                        </div>
                    </div>                   -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 80% !important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Editar Ruta de Surtido</h4>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-lg-12">
                      <label>Nombre de la ruta</label>
                      <input  class="form-control input-sm" id="nombre" val="">
                      <input id="id_ruta" val="" hidden>
                    </div>
                  </div>
                    <!--div class="col-lg-6">
                      <label>Nombre del surtidor</label>
                      <select class="form-control" id="surtidor_ruta"></select>
                    </div-->
                  <div class="row">
                    <div class="form-group">
                      <div class="col-sm-12" style="margin-bottom: 30px">
                        <input type="checkbox" id="selectAll" >
                        <label for="selectAll">Seleccionar Todo</label>
                      </div>
                    </div>
                    <div class="form-group" id="dragUbicaciones">
                      <div class="col-md-6 relative">
                        <label for="email">Surtidores Disponibles</label>
                        <ol data-draggable="target" id="fromU" class="wi" style="width: 90% !important;">
                        </ol>
                        <div style="position: absolute;right: 0;top: 35%; display: inline-grid;">
                          <button class="btn btn-primary floating-button" onclick="add('#fromU', '#toU')"title="Agregar" >>></button>
                          <button class="btn btn-primary floating-button" onclick="remove('#toU', '#fromU')" title="Quitar" style="margin-top: 40px"><<</button>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <label for="email">Surtidores asignados</label>
                        <ol data-draggable="target" id="toU" class="wi">
                        </ol>
                      </div>
                    </div>
                  </div>
                  
                  <br>
                    <div class="ibox-content" style="overflow-y: auto; height: 300px; ">
                      <table class="table table-bordered" id="tabla_ubicaciones" >
                        <thead>
                          <tr>
                            <th style="display:none;">ID</th>
                            <th>Almacen</th>
                            <th>Zona de almacenaje</th>
                            <th>Tipo de ubicación</th>
                            <th>Secuencia de Surtido</th>
                            <th>BL</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                    </div>
                  
<!--                     <div class="ibox-content">
                        <div class="jqGrid_wrapper" id="detalle_wrapper">
                            <table id="grid-table3"></table>
                            <div id="grid-pager3"></div>
                        </div>
                    </div>                   -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="guardandoSurtido()" type="submit" id="guardar" class="btn btn-sm btn-primary"><i class="fa fa-save" alt="Guardar"></i>Guardar Ruta de surtido</button>
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
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

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
                //console.log("Almacen = ", data);
                if (data.success == true) {
                    //document.getElementById('almacenes').value = data.codigo.clave;
                    $("#almacenes").val(data.codigo.clave);
                    //buscar();
                    setTimeout(function() {
                    loadDataToGrid(data.codigo.clave);
                    }, 1000);
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
        console.log("Almacen Init = ", $("#almacenes").val());
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
            url:'/api/adminrutasurtido/lista/index.php',
            datatype: "json",
            contentType: "application/json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#buscar").val()
            },
            mtype: 'POST',
            colNames:[ "Acciones",'ID','Nombre', 'Ubicaciones Asignadas',"Usuario(s) Asignado(s)","Almacen"],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'myac',index:'', width:105, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'id',index:'id', width: 50, editable:false, sortable:false, hidden: false},
                {name:'nombre',index:'nombre', width: 250, editable:false, sortable:false},
                {name:'ubica',index:'ubica', width: 150, editable:false, sortable:false, align:"center"},
                {name:'usuarios',index:'usuarios', width: 250, editable:false, sortable:false},
                {name:'almacen',index:'almacen', width: 250, editable:false, sortable:false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'id',
            viewrecords: true,
            sortorder: "asc",
            loadComplete: function(data){console.log("SUCCESS", data);}
            //loadComplete: buscar()
            //loadComplete: almacenPrede()
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var id = rowObject[1];
            var html = '';
            html += '<a href="#" onclick="detalle(\''+id+'\')"><i class="fa fa-search" title="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
             if($("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="editar(\''+id+'\')"><i class="fa fa-pencil" title="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
             if($("#permiso_eliminar").val() == 1)
          html += '<a href="#" onclick="eliminar(\''+id+'\')"><i class="fa fa-eraser" title="Eliminar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
       });
/*
       $(function($){
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
            url: '/api/adminrutasurtido/lista/index.php',
            datatype: "local",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['ID','Almacen', "Zona de almacenaje", "Tipo de ubicacion","Secuencia de Surtido","BL"],
            colModel:[
                {name:'idy_ubica',index:'idy_ubica', width: 80, editable:false, sortable:false, hidden: true},
                {name:'almacen',index:'almacen', width: 150, editable:false, sortable:false},
                {name:'zona',index:'zona', width: 150, editable:false, sortable:false},
                {name:'tipo_u',index:'tipo_u', width: 150, editable:false, sortable:false},
                {name:'orden_secuencia',index:'orden_secuencia',  width: 150, editrules:{integer: true}, editable:true, edittype:'text', sortable:true},
                {name:'bl',index:'bl',  width: 80, editable:false, sortable:false, resizable: false},
               
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            viewrecords: true,
        });
         
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 120 );
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        jQuery("#grid-table2").jqGrid('setGroupHeaders', {
            useColSpanStyle: true, 
            groupHeaders:[
                {startColumnName: 'clave', numberOfColumns: 2, titleText: 'Producto'},
            ]   
        });
         
        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
  */
  
    $(function($){
        var grid_selector = "#grid-table3";
        var pager_selector = "#grid-pager3";

        //resize to fit page size
        $(window).on("resize", function () {
            var $grid = $("#grid-table3"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width()-30;
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width()-30 );
                }, 0);
            }
        })
     
        var lastsel;
        $(grid_selector).jqGrid({
            url: '/api/adminrutasurtido/lista/index.php',
            datatype: "local",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: $("#buscar").val()
            },
            mtype: 'POST',
            colNames:['ID','Almacen.', "Zona de almacenaje", "Tipo de ubicación","Secuencia de Surtido", "BL"],
            colModel:[
                {name:'idy_ubica',index:'idy_ubica', width: 50, editable:false, sortable:false, hidden: true},
                {name:'almacen',index:'almacen', width: 150, editable:false, sortable:false},
                {name:'zona',index:'zona', width: 210, editable:false, sortable:false},
                {name:'tipo_u',index:'tipo_u', width: 150, editable:false, sortable:false},
                {name:'orden_secuencia',index:'orden_secuencia',  width: 180, editrules:{integer: true}, editable:true, edittype:'text', sortable:true},
                {name:'bl',index:'bl',  width: 80, editable:false, sortable:false, resizable: false},
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'idy_ubica',
            viewrecords: true,
            editurl: '/api/rutassurtido/lista/index.php', 
            onSelectRow: function (orden_secuencia) {
               if (orden_secuencia && orden_secuencia !== lastsel) {
                   jQuery('#grid-table3').restoreRow(lastsel);
                   lastsel = orden_secuencia;
               }
               jQuery('#grid-table3').editRow(orden_secuencia, true, false);
            },
            sortorder: "asc",
            viewrecords: true,
        });
     
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 150 );
        });

        jQuery("#grid-table3").jqGrid('setGroupHeaders', {
            useColSpanStyle: true, 
            groupHeaders:[
                {startColumnName: 'clave', numberOfColumns: 2, titleText: 'Producto'},
            ]   
        });
         
        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
     
     
        $('#selectAll').iCheck({
          checkboxClass: 'icheckbox_square-green',
          radioClass: 'iradio_square-green'
        });
      
        $("body").on("ifToggled", function(e){
          if(e.target.checked && e.target.id === 'selectAll'){
            $('#fromU li input[type="checkbox"].drag').each(function(i, e){
              e.checked = true;
              e.parentElement.setAttribute('aria-grabbed', true);
            });
          }else{
            $('#fromU li input[type="checkbox"].drag').each(function(i, e){
              e.checked = false;
              e.parentElement.setAttribute('aria-grabbed', false);
            });
          }
        });
    });
  
    function RecorrerGrid2() 
    {
      //EDG
      var arr_id = [];
      var arr_orden = [];
      var k = 0;
      var j = 1;
      $("#tabla_ubicaciones>tbody>tr").each(function(i, e){
        arr_id.push($(this).attr('id'));
        arr_orden.push(document.getElementById("tabla_ubicaciones").rows[j].cells[3].firstChild.value);
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/rutassurtido/update/index.php',
          data: {
            idy_ubica : arr_id[k],
            orden_secuencia : arr_orden[k],
            id_ruta : $("#id_ruta").val(),
            action : "editarOrdenSec"
          },
          beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
          },
          success: function(data) 
          {
            if (data.success == true) 
            {
              console.log("Actualizo");
            }
          }
        });
        k++;
        j++;
      });
      return true;
    }
  
    function RecorrerGrid() {
        
        var rows = jQuery("#grid-table3").jqGrid('getRowData');
        for(var i=0;i<rows.length;i++)
        {
            var row=rows[i];
            if(Number(row['orden_secuencia'])>0)
            {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        idy_ubica : row['idy_ubica'],
                        orden_secuencia : row['orden_secuencia'],
                        id_ruta : $("#id_ruta").val(),
                        action : "editarOrdenSec"
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    url: '/api/rutassurtido/update/index.php',
                    success: function(data) {
                        if (data.success == true) {
                            console.log("Actualizo");
                        }
                    }
                });
            }
        }
        return true;
    };
</script>
<script>
    $(document).ready(function(){
        $("#almacenes").on('change', function(e){
            console.log("Entro");
            loadDataToGrid(e.target.value);
        });

        $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
    });
</script>

<script>
    function eliminar(id)
    {
        console.log("Eliminando ruta...",id);

            swal({
                title: "¿Está seguro que desea borrar la ruta?",
                text: "Está a punto de borrar una ruta y esta acción no se puede deshacer",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: false
            }, function() {
                    $.ajax({
                    url: '/api/adminrutasurtido/lista/index.php',
                    type: "POST",
                    dataType: "json",
                    data: {
                        id_ruta: id,
                        action: "eliminarRS"
                    },
                    success: function(data) {
                      console.log("Regresando...",data);
                      if(data.success)
                      {
                          console.log("Ruta eliminada...",data);
                          swal("Borrado", "Se ha borrado la ruta con exito", "success");
                          $('#grid-table').trigger( 'reloadGrid' );
                      }
                    }
                });
            });
    }
  
    function buscar() 
    {
        console.log("Search = ", $("#buscar").val()," Search2 = ", $("#buscar").val(), " almacen = ", $("#almacenes").val());
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: $("#almacenes").val(),
                criterio: $("#buscar").val(),
                action: 'loadGrid'
            }, datatype: 'json'
            })
            .trigger('reloadGrid',[{current:true}]);
    }

    function loadDataToGrid(almacen) 
    {
        console.log("Search = ", $("#buscar").val(), " almacen = ", $("#almacenes").val());
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: almacen,
                criterio: $("#buscar").val(),
                action: 'loadGrid'
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
    }

    function loadDataToGridDetails(id) {
        cargar_ubicaciones(id , "Detalle");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                almacen:$("#almacenes").val(),
                action: 'loadDetails2'
            },
            url: '/api/adminrutasurtido/lista/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    console.log(data);
                    name="";
                    for (var i=0; i<data["user"].length; i++)
                    {
                        if(data["user"][i].id === data.nombre_surtidor)
                            name=data["user"][i].nombre;
                    }
                    $("#d_nombre").val(data[0]["nombre"]);
                    $("#d_surtidor").val(name);
                    $("#toU_d .itemlist").remove();

                    for (var i=0; i<data["user_asign"].length; i++)
                    {
                        var ul = document.getElementById("toU_d");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("value", data["user_asign"][i].id);
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        checkbox.disabled = true;
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data["user_asign"][i].nombre));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data["user_asign"][i].id);
                        ul.appendChild(li);
                    }
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
//         $('#grid-table2').jqGrid('clearGridData')
//             .jqGrid('setGridParam', {postData: {
//                 id: id,
//                 almacen:$("#almacenes").val(),
//                 action: 'loadDetails'
//             },
//             datatype: 'json',
//             success:function(data){
//                 console.log("succes");
//             }
//             })
//             .trigger('reloadGrid',[{current:true}]);
    }
  
    function loadDataToGridEdit(id) 
    {
      cargar_ubicaciones(id , "Editar");
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          id: id,
          almacen:$("#almacenes").val(),
          criterio: $("#buscar").val(),
          action: 'loadDetails2'
        },
        url: '/api/adminrutasurtido/lista/index.php',
        success: function(data) {
          if (data.success == true) 
          {
            console.log(data);
            $("#nombre").val(data[0]["nombre"]);
            $("#id_ruta").val(id);

            var options = $("#surtidor_ruta");
            options.empty();
            options.append(new Option("Surtidor", "0"));
            for (var i=0; i<data["user"].length; i++)
            {
              options.append(new Option(data["user"][i].nombre, data["user"][i].id));
            }
            options.val(data.nombre_surtidor);
            $('.chosen-select').trigger("chosen:updated");

            $("#selectAll").iCheck('uncheck');
            $("#fromU .itemlist").remove();
            $("#toU .itemlist").remove();
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

            for (var i=0; i<data["user_asign"].length; i++)
            {
              var ul = document.getElementById("toU");
              var li = document.createElement("li");
              var checkbox = document.createElement("input");
              checkbox.style.marginRight = "10px";
              checkbox.setAttribute("type", "checkbox");
              checkbox.setAttribute("value", data["user_asign"][i].id);
              checkbox.setAttribute("class", "drag");
              checkbox.setAttribute("onclick", "selectParent(this)");
              li.appendChild(checkbox);
              li.appendChild(document.createTextNode(data["user_asign"][i].nombre));
              li.setAttribute("dayta-draggable", "item");
              li.setAttribute("draggable", "false");
              li.setAttribute("aria-draggable", "false");
              li.setAttribute("aria-grabbed","false");
              li.setAttribute("tabindex","0");
              li.setAttribute("class","itemlist");
              li.setAttribute("onclick","selectChild(this)");
              li.setAttribute("value",data["user_asign"][i].id);
              ul.appendChild(li);
            }
          }
        },
        error: function(res){
          window.console.log(res);
        }
      });
//       $('#grid-table3').jqGrid('clearGridData')
//       .jqGrid('setGridParam', {postData: {
//         id: id,
//         action: 'loadDetails',
//         almacen: $("#almacenes").val()
//       }, datatype: 'json'})
//       .trigger('reloadGrid',[{current:true}]);
    }
  
    function cargar_ubicaciones(id, accion)
    {
      $("#tabla_ubicaciones>tbody").empty();
      if(accion == "Editar")
      {
        console.log("Editar");
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/adminrutasurtido/lista/index.php',
          data: {
            action: 'loadDetails_pro',
            id: id,
            almacen:$("#almacenes").val(),
          },
          success: function(data) 
          {
            //console.log("here",data);
            $("#tabla_ubicaciones>tbody").empty();
            var i = 0;
            data.ubicaciones.forEach(function(element) {
              //console.log(i);
              var id = data.ubicaciones[i].datos[0];
              var almacen = data.ubicaciones[i].datos[1];
              var zona = data.ubicaciones[i].datos[2];
              var tipo = data.ubicaciones[i].datos[3];
              var secuencia = data.ubicaciones[i].datos[4];
              var bl = data.ubicaciones[i].datos[5];
              $("#tabla_ubicaciones").find('tbody').append(
                $('<tr id="'+id+'">'+
                '<td>'+almacen+'</td>'+
                '<td>'+zona+'</td>'+
                '<td>'+tipo+'</td>'+
                '<td><input type="text" class="form-control input-sm" id="'+id+'_orden_secuencia"></td>'+
                '<td>'+bl+'</td>'+
                '</tr>')
              );
              i++;
              document.getElementById(id+"_orden_secuencia").value = secuencia; 
            });

          },
        });
      }
      if(accion == "Detalle")
      {
        $("#tabla_ubicaciones2>tbody").empty();
        console.log("Detalle");
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/adminrutasurtido/lista/index.php',
          data: {
            action: 'loadDetails_pro2',
            id: id,
            almacen:$("#almacenes").val(),
          },
          success: function(data) 
          {
            //console.log("here",data);
            $("#tabla_ubicaciones2>tbody").empty();
            var i = 0;
            data.ubicaciones.forEach(function(element) {
              //console.log(i);
              var id = data.ubicaciones[i].datos[0];
              var almacen = data.ubicaciones[i].datos[1];
              var zona = data.ubicaciones[i].datos[2];
              var tipo = data.ubicaciones[i].datos[3];
              var secuencia = data.ubicaciones[i].datos[4];
              var bl = data.ubicaciones[i].datos[5];
              $("#tabla_ubicaciones2").find('tbody').append(
                $('<tr id="'+id+'">'+
                '<td>'+bl+'</td>'+
                '<td><input type="text" class="form-control input-sm" id="'+id+'_detalle" disabled></td>'+
                '<td>'+tipo+'</td>'+
                '<td>'+zona+'</td>'+
                '<td>'+almacen+'</td>'+
                '</tr>')
              );
              i++;
              document.getElementById(id+"_detalle").value = secuencia; 
            });

          },
        });
      }
    }
  
    function add(from, to)
    {
        var elements = document.querySelectorAll(`${from} input.drag:checked`),li, newli;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${from}`).removeChild(li);
            document.querySelector(`${to}`).appendChild(newli);
        }
    }
    function remove(to, from)
    {
        var elements = document.querySelectorAll(`${to} input.drag:checked`),li, newli;
        for(e of elements)
        {
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${to}`).removeChild(li);
            document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
        }
    }
    function selectParent(e)
    {
        if(e.checked)
        {
            e.parentNode.setAttribute("aria-grabbed", "true");
        }else{
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }
    function selectChild(e)
    {
        if(e.getAttribute("aria-grabbed") == "true")
        {
            e.firstChild.checked = true;
        }else{
            e.firstChild.checked = false;
        }
    }

    $("#buscar").keyup(function(event)
    {
        if(event.keyCode == 13)
        {
            buscar();
        }
    });

    function detalle(id)
    {
        loadDataToGridDetails(id);
        $("#detalleModal").modal('show');
    }
  
    function editar(id)
    {
      loadDataToGridEdit(id);
      $("#editarModal").modal('show');
    }
  
    function ElementoVacio(vec_surtido)
    {
        //console.log("vec_surtido = ", vec_surtido);
        var acepto = true;
        for(var i = 0; i < vec_surtido.length; i++)
        {
            //console.log("vec_surtido[",i, "] = ", vec_surtido[i]);
            if(vec_surtido[i] == '') 
            {
                acepto = false; 
                break;
            }
        }

        return acepto;
    }

    function BuscarRepetido(vec_surtido, val_id)
    {
        var acepto = true, cont = 0;
        for(var i = 0; i < vec_surtido.length; i++)
        {
            if(vec_surtido[i] == val_id) cont++;
            if(cont == 2) 
            {
                acepto = false; 
                break;
            }
        }

        return acepto;
    }

    function guardandoSurtido()
    {
        var surtidor = $("#surtidor_ruta").val();
        var nombre = $("#nombre").val();
        var usuarios = [];

        $("#toU").each(function() 
        {
            var localRels = [];
            $(this).find('li').each(function()
            {
                localRels.push( $(this).attr('value'));
            });
            if(localRels.length > 0 )
            {
                usuarios = localRels;
            }
        });

        if(nombre == " ")
        {
            swal("Error", "Indique el nombre de la Ruta", "error");
        }
        
        else
        {
            var j = 1;
            var  sum_orden = 0;
            var secuencia_surtido = [];

            $("#tabla_ubicaciones>tbody>tr").each(function(i, e){
              val = document.getElementById("tabla_ubicaciones").rows[j].cells[3].firstChild.value;
              //if(isNaN(val) || val === undefined){val = "RSBAD";}
              sum_orden += parseInt(val);
              secuencia_surtido[i] = val;
              j++;
            });
            //console.log("secuencia_surtido = ", secuencia_surtido);

            if(!ElementoVacio(secuencia_surtido))
            {
               swal("Error", "No se debe registrar Secuencia de Surtido Vacíos", "error");
               return;
            }

            for(var y = 0; y < secuencia_surtido.length; y++)
            {
                if(!BuscarRepetido(secuencia_surtido, secuencia_surtido[y]))
                {
                   swal("Error", "No se debe registrar Secuencia de Surtido Repetidos", "error");
                   return;
                }
            }

            if(sum_orden <= 0){
               swal("Error", "Indique una secuencia de surtido", "error");
               return;
            }
            $.ajax({
                url: '/api/adminrutasurtido/lista/index.php',
                type: "POST",
                dataType: "json",
                data: {
                    id      : usuarios,
                    id_ruta : $("#id_ruta").val(),
                    nombre  : nombre,
                    almacen : $("#almacenes").val(),
                    action  : "guardarRuta2"
                },
                success: function(data) 
                {
                if (data.success == true) 
                {
                  RecorrerGrid2();
                  $('#editarModal').modal('hide');
                  loadDataToGrid();
                }
                else if(data.text != "")
                {
                  swal("Error", data.text, "error");
                }
                }
            });
        }
    }
</script>
