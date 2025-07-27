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
    <h3>Administración de Ubicaciones de Almacenaje*</h3>
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
                                            <option value="<?php echo $almacen->clave ?>" data-id="<?php echo $almacen->id ?>"><?php echo $almacen->nombre ?></option>
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
                      <div class="col-lg-4">
                            <div class="form-group">
                                <label for="email">Tipo de Ubicación</label>
                                <select id="select-tipoU" class="chosen-select form-control">
                                    <option value="">Seleccione el Tipo de Ubicación</option>
                                    <option value="L">Libre</option>
                                    <option value="R">Reservada</option>
                                    <option value="Q">Cuarentena</option>
                                    <option value="Picking">Picking</option>
                                    <option value="PTL">PTL</option>
                                    <option value="Mixto">Acomodo Mixto</option>
                                    <option value="Produccion">Área de Producción</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                                <label>Existencia</label>
                                <select id="select-existencia" class="chosen-select form-control">
                                    <option value="0">Todas las Ubicaciones</option>
                                    <option value="1">Ubicaciones Con Existencia</option>
                                    <option value="2">Ubicaciones Sin Existencia</option>
                                </select>

                        </div>

                        <div class="col-lg-4" >
                            <div class="form-group">
                               <label>Pallet|Contenedor</label>
                               <input type="text" class="form-control" name="txtPallet" id="input-pallet-contenedor" placeholder="Pallet|Contenedor...">
                            </div>
                        </div>

                        <div class="col-lg-4" >
                            <div class="form-group">
                               <label>License Plate (LP)</label>
                               <input type="text" class="form-control" name="txtLP" id="input-lp" placeholder="License Plate (LP)...">
                            </div>
                        </div>

                        <div class="col-lg-4" >
                            <div class="input-group">
                                <label>Buscar BL</label>
                                <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar BL...">
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
                    <h3 class="modal-title">Productos Ubicados</h3>
                    <h4><label id="detalle_ubicacion" style="text-align: center;"></label></h4>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="col-md-3">
                          <label>Peso Maximo (Kg)</label>
                          <input type="text" class="form-control" id="pes_max" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>Peso Total Ocupado (Kg)</label>
                          <input type="text" class="form-control" id="pes_total" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>Peso Disponible (Kg)</label>
                          <input type="text" class="form-control" id="pes_dispo" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>% Ocupación Peso</label>
                          <input type="text" class="form-control" id="pes_porcentaje" style="text-align:right;" disabled>
                        </div>
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="col-md-3">
                          <label>Volumen Maximo (m3)</label>
                          <input type="text" class="form-control" id="vol_max" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>Volumen Total Ocupado (m3)</label>
                          <input type="text" class="form-control" id="vol_total" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>Volumen Disponible (m3)</label>
                          <input type="text" class="form-control" id="vol_dispo" style="text-align:right;" disabled>
                        </div>
                        <div class="col-md-3">
                          <label>% Ocupacion Volumen</label>
                          <input type="text" class="form-control" id="vol_porcentaje" style="text-align:right;" disabled>
                        </div>
                      </div>
                    <br>
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
            url: '/api/admninistracionubicaciones/lista/index.php',
            datatype: "local",
            autowidth: true,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Acción","BL","Zona de Almacenaje","Pallet|Contenedor", "License Plate (LP)", "Productos Ubicados","Existencia piezas","Peso Máximo","Volumen(m3)","Peso%","Volumen%","Dimensiones (Alt. X Anc. X Lar. )","Picking","PTL","Libre", "Reservada", "Cuarentena", "Acomodo Mixto"/*Secuencia de Surtido"*/],
            colModel:[
                {name:'myac',index:'', width: 100, fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'BL', index:'BL', width: 120, editable:false, sortable:false},
                {name:'zona_almacenaje',index:'zona_almacenaje', width: 210, editable:false, sortable:false},
                {name:'clave_contenedor',index:'clave_contenedor', width: 210, editable:false, sortable:false},
                {name:'CveLP',index:'CveLP', width: 210, editable:false, sortable:false},
                {name:'total_ubicados',index:'total_ubicados', width: 180, editable:false, sortable:false, align: "right"},
                {name:'existencia_total',index:'existencia_total', width: 180, editable:false, sortable:false, align: "right"},
                {name:'PesoMax',index:'PesoMax', width:110, editable:false, sortable:false, align:"right"},
                {name:'volumen_m3',index:'volumen_m3', width:110, editable:false, sortable:false, align:"right"},
                {name:'peso',index:'peso', width: 100, align:"right", editable:false, sortable:false},
                {name:'volumen',index:'volumen', width: 110, align:"right", editable:false, sortable:false},
                {name:'dim',index:'dim',width:250, editable:false, sortable:false, align:"center"},
                {name:'picking',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck},
                {name:'ptl',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck2},
                {name:'li',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck3},
                {name:'re',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck4},
                {name:'cu',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck5},
                {name:'acomodomixto',index:'acomodomixto', width:110, fixed:true, sortable:false, resize:false, align:"center", formatter:acomodoMixto},
//                {name:'surtido',index:'surtido', width: 130, editable:false, sortable:false}
                
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
            html += '<a href="#" onclick="detalle(\''+ubicacion+'\', \'' + codigo_CSD + '\', \'' + peso_max + '\', \'' + vol_max + '\', \'' + pes_porcentaje + '\', \'' + vol_porcentaje + '\')"><i class="fa fa-search" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
      
      function imageCheck(cellvalue, options, rowObject){
            var picking = rowObject[12];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }
      
      function imageCheck2(cellvalue, options, rowObject){
            var picking = rowObject[13];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck3(cellvalue, options, rowObject){
            var picking = rowObject[14];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function imageCheck4(cellvalue, options, rowObject){
            var picking = rowObject[15];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck5(cellvalue, options, rowObject){
            var picking = rowObject[16];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function acomodoMixto(cellvalue, options, rowObject){
            var html;
            if (rowObject[17] === "S"){
                html =  '<div class="text-success"><i class="fa fa-check"></i></div>';
            }else{
                html =  '<div class="text-danger"><i class="fa fa-close"></div</i>';
            }
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
            url: '/api/admninistracionubicaciones/lista/index.php',
            datatype: "local",
            autowidth: false,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['id','Clave','Descripción','Lote','Caducidad','Serie','Existencia','Peso U(Kg)','Volumen U(m3)', 'Peso Total(kg)', 'Volumen Total(m3)'],
            colModel:[

                {name:'id',index:'id',width: 300, editable:false, sortable:false, hidden:true},
                {name:'cve_articulo',index:'cve_articulo',width: 100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:400, editable:false, sortable:false},
                {name:'lote',index:'lote',width:100, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false},
                {name:'serie',index:'serie',width:100, editable:false, sortable:false},
                {name:'Existencia_Total',index:'Existencia_Total',width:80, editable:false, sortable:false,align:'right'},
                {name:'peso_unitario',index:'peso_unitario',width:90, editable:false, sortable:false,align:'right'},
                {name:'volumen_unitario',index:'volumen_unitario',width:132, editable:false, sortable:false,align:'right'},
                {name:'peso_total',index:'peso_total',width:100, editable:false, sortable:false,align:'right'},
                {name:'volumen_total',index:'volumen_total',width:132, editable:false, sortable:false,align:'right'},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            loadComplete:function(data){
              setTimeout(function(){ 
                if(data.total != 0)
                {
                  var peso_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    peso_total = parseFloat(peso_total) + (parseFloat(pesos[i].cell[7])*pesos[i].cell[6]);
                    console.log(peso_total);
                  }
                  
                  $("#pes_total").val(peso_total.toFixed(4));
                  $("#pes_dispo").val(($("#pes_max").val() - peso_total).toFixed(4));
                  var porcentaje_peso = (100*$("#pes_total").val())/$("#pes_max").val();
                  $("#pes_porcentaje").val(porcentaje_peso.toFixed(4));

                  var volumen_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    volumen_total = parseFloat(volumen_total) + (parseFloat(pesos[i].cell[8]*pesos[i].cell[6]));
                  }
                  $("#vol_total").val(volumen_total.toFixed(4));
                  $("#vol_dispo").val(($("#vol_max").val() - volumen_total).toFixed(4));
                  var porcentaje_vol = (100*$("#vol_total").val())/$("#vol_max").val();
                  $("#vol_porcentaje").val(porcentaje_vol.toFixed(4));

                  var input_peso = document.getElementById('pes_porcentaje');
                  var input_volumen = document.getElementById('vol_porcentaje');

                  if($("#pes_total").val() > $("#pes_max").val())
                  {
                    input_peso.style.borderColor = "red";
                  }
                  else
                  {
                    input_peso.style.borderColor = "green";
                  }

                  if($("#vol_total").val() > $("#vol_max").val())
                  {
                    input_volumen.style.borderColor = "red";
                  }
                  else
                  {
                    input_volumen.style.borderColor = "green";
                  }
                  $("#tabla_vacia").hide();
                }
                else
                {
                  $("#tabla_vacia").show();
                  console.log("deam");
                  $("#pes_total").val("0");
                  $("#pes_dispo").val($("#pes_max").val());
                  $("#pes_porcentaje").val("0");

                  $("#vol_total").val("0");
                  $("#vol_dispo").val($("#vol_max").val());
                  $("#vol_porcentaje").val("0");
                  var input_peso = document.getElementById('pes_porcentaje');
                  var input_volumen = document.getElementById('vol_porcentaje');
                  input_peso.style.borderColor = "green";
                  input_volumen.style.borderColor = "green";
                }
              }, 200);
              
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

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

        $("#almacen").on('change', function(e){console.log("hola");
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
    var tipoU = $("#select-tipoU").val();
    $('#grid-table').jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: {
            almacen: id,
            almacenaje: $("#almacenp").val(),
            search: $("#buscar").val(),
            pallet_contenedor: $("#input-pallet-contenedor").val(),
            lp: $("#input-lp").val(),
            action: 'loadGrid',
            tipo: tipoU,
            vacio: $("#select-existencia").val()
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
      url: '/api/admninistracionubicaciones/lista/index.php',
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

function detalle(ubicacion,csd,peso_max,vol_max,pes_porcentaje,vol_porcentaje){
    loadDataToGridDetails(ubicacion, $("#almacen").val());
    $("#tabla_vacia").hide();
    $("#pes_max").val("");
    $("#pes_total").val("");
    $("#pes_dispo").val("");
    $("#pes_porcentaje").val("");
    $("#vol_max").val("");
    $("#vol_total").val("");
    $("#vol_dispo").val("");
    $("#vol_porcentaje").val("");
    console.log(peso_max+'-'+vol_max+'-'+pes_porcentaje+'-'+vol_porcentaje);

    $("#pes_max").val(peso_max);
    $("#vol_max").val(vol_max);
    $("#pes_porcentaje").val(pes_porcentaje);
    $("#vol_porcentaje").val(vol_porcentaje);
    $("#detalle_ubicacion").html("Ubicacion "+csd);
    $("#ver_detalles").modal("show");
    $("#detalleModal").modal('show');
}

</script>
