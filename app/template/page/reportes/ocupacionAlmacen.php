<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();
?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style type="text/css">
th[role="columnheader"]{
    text-align: center;
}
</style>

<style type="text/css">
    ul.inline li{
        display: inline;
    }
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-table2, #grid-table3, #grid-table4, #grid-pager, #grid-pager2, #grid-pager3, #grid-pager4{
      max-width: 100%;
    }
</style>

<!-- Mainly scripts -->

<div class="wrapper wrapper-content  animated" id="list">
    <h3>Reporte de Ocupación de Almacen</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen" >
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>" data-id="<?php echo $almacen->id ?>"><?php echo "(".$almacen->clave.") - ".$almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Zona de Almacenaje</label>
                                <select class="form-control chosen-select" name="almacenp" id="almacenp" disabled>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4" style="margin-bottom: 20px; top: 24px;">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="buscar()">
                                        <button type="submit" class="btn btn-sm btn-primary" id="buscarP">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                    </div>
                </div>
                <div class="ibox-content">
                  <div class="row">
                      <div class="col-lg-12" style="text-align: right">
                          <ul class="list-unstyled inline">
                            <li><b>| Total ubicaciones:</b> <span id="total_ubicaciones">0</span></li>
                            <li><b>| Porcentaje de ocupación:</b> <span id="porcentaje_ocupadas">0</span></li>
                            <li><b>| Ubicaciones Vacías:</b> <span id="vacias">0</span> <b>|</b></li>
                          </ul>
                      </div>
                  </div>
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
        <div class="modal-dialog vertical-align-center modal-lg" style="wilih: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle</h4>
                </div>
                <div class="modal-body">
					<div class="ibox-content">
                        <div class="jqGrid_wrapper" id="detalle_wrapper">
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
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Jquery Validate -->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>


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
                if (data.success == true) {
                    document.getElementById('almacen').value = data.codigo.clave;
                    $('#almacen').trigger('change');
                    buscar();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    $(function($){
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
            url: '/api/reportes/lista/ocupacionAlmacen.php',
            datatype: "local",
            autowidth: true,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Acción","Pasillo","Rack","Nivel","Sección","Posición", "BL","Tipo de Ubicación","Total de Productos Ubicados","Existencia (Piezas)","Peso","Volumen","Secuencia de Surtido", "Zona de Almacenaje", 'ubicacion'],
            colModel:[
                {name:'myac',index:'', width: 100, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'pasillo',index:'pasillo', width: 60, editable:false, sortable:false, hidden: true},
                {name:'rack',index:'rack', width: 60, editable:false, sortable:false, hidden: true},
                {name:'nivel',index:'nivel', width: 60, editable:false, sortable:false, hidden: true},
                {name:'seccion',index:'seccion', width: 60, editable:false, sortable:false, hidden: true},
                {name:'posicion',index:'posicion', width: 60, editable:false, sortable:false, hidden: true},
                {name:'bl',index:'bl', width: 60, editable:false, sortable:false},
                {name:'tipo_ubicacion',index:'tipo_ubicacion', width: 160, editable:false, sortable:false},
                {name:'total_ubicados',index:'total_ubicados', width: 180, editable:false, sortable:false},
                {name:'existencia_total',index:'existencia_total', width: 180, editable:false, sortable:false,align:"right" },
                {name:'peso',index:'peso', width: 100, editable:false, sortable:false},
                {name:'volumen',index:'volumen', width: 110, editable:false, sortable:false},
                {name:'surtido',index:'surtido', width: 130, editable:false, sortable:false},
                {name:'zona_almacenaje',index:'zona_almacenaje', width: 210, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion', width: 130, editable:false, sortable:false, hidden: true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            loadComplete: almacenPrede()
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        jQuery("#grid-table").jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'peso', numberOfColumns: 2, titleText: 'Portentaje de Ocupación'},
            ]
        });


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var ubicacion = rowObject[14];
            var html = '';
            html += '<a href="#" onclick="detalle(\''+ubicacion+'\')"><i class="fa fa-search" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
    $(function($){
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
            url: '/api/reportes/lista/ocupacionAlmacen.php',
            datatype: "local",
            autowidth: false,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Zona de Almacenaje","Ubicación","Clave","Descripción","Cantidad","Lote/Serie","Caducidad"],
            colModel:[
                {name:'zona_almacenaje',index:'zona_almacenaje', width: 200, editable:false, sortable:false},
                {name:'ubicacion',index:'ubicacion', width: 200, editable:false, sortable:false},
                {name:'clave',index:'clave', width: 150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width: 250, editable:false, sortable:false},
                {name:'cantidad',index:'cantidad', editable:false, sortable:false},
                {name:'lote',index:'lote', width: 200,editable:false, sortable:false},
                {name:'caducidad',index:'caducidad', width: 200, editable:false, sortable:false}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        /*jQuery("#grid-table2").jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                {startColumnName: 'clave', numberOfColumns: 2, titleText: 'Producto'},
            ]
        });*/


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
</script>

<script>
    $(document).ready(function(){

        $("#almacenp").on('change', function(e){
            loadDataToGrid(e.target.value);
        });

        $("#almacen").on('change', function(e){
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave : e.target.value,
                    action : "traerZonasDeAlmacenP"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/almacenp/update/index.php',
                success: function(data) {
                    $("#almacenp").removeAttr("disabled");
                    if (data.success == true) {
                        var options = $("#almacenp");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i=0; i<data.zonas.length; i++)
                        {
                            options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        });

        $(function() {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
      });
    });
</script>

<script>

function buscar() {
    var almacen = $("#almacen").val();
    var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacen: id,
            almacenaje: $("#almacenp").val(),
            search: $("#buscar").val(),
            action: 'loadGrid'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    getStatistics();
}

function loadDataToGrid(almacenaje) {
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacenaje: almacenaje,
            action: 'loadGrid'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    getStatistics();
}

function getStatistics(){
  var almacen = $("#almacen").val();
  var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
  $.ajax({
      url: '/api/reportes/lista/ocupacionAlmacen.php',
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'loadStatistics',
        almacen: id,
        almacenaje: $("#almacenp").val()
      }
  })
  .done(function(data){
      $("#total_ubicaciones").html(data.total)
      $("#porcentaje_ocupadas").html(data.porcentajeocupadas + '%')
      $("#vacias").html(data.vacias)
  });
}

function loadDataToGridDetails(ubicacion, almacenaje) {
    $('#grid-table2').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            ubicacion: ubicacion,
            almacenaje: almacenaje,
            action: 'loadDetails'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
}

$("#buscar").keyup(function(event){
    if(event.keyCode == 13){
        buscar()
    }
});

function detalle(ubicacion){
    loadDataToGridDetails(ubicacion, $("#almacenp").val());
    $("#detalleModal").modal('show');
}

</script>
