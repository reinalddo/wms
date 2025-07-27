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
    <h3>QA | Control de Calidad | Cuarentena</h3>
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
                                <label>Articulo</label>
                                <select class="form-control chosen-select" name="articulo" id="articulo">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control chosen-select" name="status" id="status">
                                    <option value="">Seleccione</option>
                                    <option selected value="Abierto">Abierto</option>
                                    <option value="Cerrado">Cerrado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                              <div class="input-group">
                                  <label>Buscar Folio</label>
                                  <input type="text" class="form-control input-sm" name="buscar_folio" id="buscar_folio" placeholder="Buscar Folio...">
                              </div>
                          </div>
                          <div class="col-lg-4" >
                              <div class="input-group">
                                  <label>Buscar BL</label>
                                  <input type="text" class="form-control input-sm" name="buscar_bl" id="buscar_bl" placeholder="Buscar BL...">
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
                        <div class="col-lg-4" style="text-align: left;">
                        <a href="#" class="btn btn-primary" target="_blank" id="reportepdf" onclick="ReportePDF()" title="Imprimir PDF"><i class="fa fa-print"></i>PDF</a>
                        </div>
                      <div class="col-lg-8" style="text-align: right">
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
  
 // var select_tipoU = document.getElementById('select-tipoU'),
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
                    setTimeout(function() {
                        $('#almacen').trigger('change');
                        buscar();
                    }, 1000);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
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
            url: '/api/controlcalidad/lista/index.php',
            datatype: "local",
            autowidth: true,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Acción","Folio","Clave","Descripción","BL","Pallet | Contenedor","Estatus","Fecha QA","Motivo Ingreso", "Usuario Ingreso","Fecha Liberación","Tiempo en QA","Motivo Salida","Usuario Salida","Almacén","Zona de Almacenaje"],
            colModel:[
                {name:'myac',index:'', width: 50, fixed:true, sortable:false, resize:false, hidden: true, formatter:imageFormat},
                {name:'folio', index:'folio', width: 110, editable:false, sortable:false},
                {name:'clave',index:'clave', width: 80, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion', width: 210, editable:false, sortable:false},
                {name:'bl',index:'bl', width: 100, editable:false, sortable:false},
                {name:'tipo',index:'tipo', width:100, editable:false, sortable:false, align:"center"},
                {name:'estatus',index:'estatus', width:100, editable:false, sortable:false, align:"center"},
                {name:'fechaQA',index:'fechaQA', width:150, editable:false, sortable:false, align:"center"},
                {name:'motivo',index:'motivo', width: 150, align:"left", editable:false, sortable:false},
                {name:'usuario_ing',index:'usuario_ing', width: 150, align:"left", editable:false, sortable:false},
                {name:'fecha',index:'fecha', width: 150, align:"center", editable:false, sortable:false},
                {name:'tiempo',index:'tiempo', width: 150, align:"center", editable:false, sortable:false},
                {name:'motivo2',index:'motivo2',width:150, editable:false, sortable:false, align:"center"},
                {name:'usuario',index:'usuario', width:150, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'almacen',index:'almacen', width:150, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'almacenaje',index:'almacenaje', width:150, fixed:true, sortable:false, resize:false, align:"center"},
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

        jQuery("#grid-table").jqGrid('setGroupHeaders', {
            useColSpanStyle: true,
            groupHeaders:[
                //{startColumnName: 'peso', numberOfColumns: 2, titleText: 'Porcentaje de Ocupación'},
            ]
        });


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            var ubicacion = rowObject[16];
            var codigo_CSD = rowObject[1];
            var peso_max = rowObject[5];
            var vol_max = rowObject[6];
            var pes_porcentaje = rowObject[7];
            var vol_porcentaje = rowObject[8];
            var html = '';
            //onclick="printPDF()"
            html += '<a href="/api/koolreport/export/reportes/QA/reporteqa" target="_blank" title="Imprimir PDF"><i class="fa fa-print"></i>PDF</a>';
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
    
</script>

<script>
    $(document).ready(function(){

        /*$("#articulo").on('change', function(e){
            loadDataToGrid(e.target.value);
        });*/
        
        $("#almacen").on('change', function(e){console.log("hola");
            $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                  almacen:e.target.value,
                  action:'traer_articulos',
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcalidad/lista/index.php',
            success: function(data) {
              console.log("cargando articulos");
                $("#articulo").removeAttr("disabled");
                if (data.success == true) {
                    var options = $("#articulo");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i=0; i<data.articulos.length; i++)
                    {
                        options.append(new Option(data.articulos[i][1], data.articulos[i][0]));
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
            id_almacen: id,
            cve_articulo: $("#articulo").val(),
            buscar_bl: $("#buscar_bl").val(),
            buscar_fol: $("#buscar_folio").val(),
            status: $("#status").val(),
            action: 'loadGrid',
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    getStatistics();
}

  function ReportePDF()
  {
    //console.log("ok koolreport");
    $("#reportepdf").attr("href", "/api/koolreport/export/reportes/QA/reporteqa?almacen="+$("#almacen").val()+"&articulo="+$("#articulo").val()+"&status="+$("#status").val()+"&bl="+$("#buscar_bl").val());
  }

/*
function loadDataToGrid(almacenaje) {
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacenaje: almacenaje,
            action: 'loadGrid'
        }, datatype: 'json'})
        .trigger('reloadGrid',[{current:true}]);
    getStatistics();
}*/

function getStatistics(){
  var almacen = $("#almacen").val();
  var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
  $.ajax({
      url: '/api/controlcalidad/lista/index.php',
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

</script>
