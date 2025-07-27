<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();

$motivosQA = new \MotivoCuarentena\MotivoCuarentena();
$motivosQA = $motivosQA->getAll();

$confSql = \db()->prepare("SELECT CURDATE() AS fecha_actual FROM DUAL");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];

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
<input type="hidden" name="val_almacen" id="val_almacen" value="">
<input type="hidden" name="val_ubicacion" id="val_ubicacion" value="">
<div class="wrapper wrapper-content  animated" id="list">
    <h3>QA|Control de Calidad|Cuarentena</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen" >
                                    <option value="" data-id="">Seleccione</option>
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
                      
                        <div class="col-lg-4" >
                            <div class="input-group">
                                <label>Buscar BL</label>
                                <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar BL...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Motivo QA</label>
                                <select class="form-control chosen-select" name="motivoqa" id="motivoqa" >
                                    <option value="" data-id="">Seleccione</option>
                                    <?php if(isset($motivosQA) && !empty($motivosQA)): ?>
                                        <?php foreach($motivosQA as $motivo): ?>
                                            <option value="<?php echo $motivo->id; ?>" data-id="<?php echo $motivo->id; ?>"><?php echo $motivo->Des_Motivo; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4" style="margin-top: 26px;">
                            <div class="input-group">
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
                    <h3 class="modal-title">Productos en QA|Cuarentena</h3>
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
                      <br><br>
                      <div class="row">
                        <br><br>
                        <hr>
                        <div class="col-md-12">
                        <div class="col-md-4">
                          <select class="form-control" id="select_diao" name="select_diao">
                              <option value="">Seleccione Dia Operativo</option>
                          </select>
                        </div>
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
                    <button onclick="mostrar_motivos(0)" type="submit" id="guardar" class="btn btn-sm btn-primary permiso_editar">Liberar</button>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
<div class="modal fade" id="moverModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm" style="wilih: 1200px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Liberar Ubicación</h4>
                </div>
                <div class="modal-body">
                    <h5 class="modal-title">Liberar todos los productos de la ubicacion</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" style="text-align:left;" disabled>
				                </div>
                        <div class="col-md-12">
                            <label>Motivos</label>
                            <select class="form-control chosen-select" name="motivo" id="motivo2">
                                <option value="">Seleccione Un Motivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="mover()" type="submit" id="guardar" class="btn btn-sm btn-primary">Guardar</button>
                </div>
          </div>
        </div>
    </div>
</div>
<div class="modal fade" id="motivos" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm" style="wilih: 1200px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Seleccione un motivo</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Motivos</label>
                            <select class="form-control chosen-select" name="motivo" id="motivo">
                                <option value="">Seleccione Un Motivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="liberar_existencias()" type="submit" id="guardar" class="btn btn-sm btn-primary">Guardar</button>
                </div>
          </div>
        </div>
    </div>
</div>

<input type="hidden" id="tiene_lote" value="">
<input type="hidden" id="tiene_caducidad" value="">
<input type="hidden" id="tiene_serie" value="">
<input type="hidden" id="cambiar_lote_val" value="">

<div class="modal fade" id="cambiar_lote_modal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Cambiar Lote|Serie y Liberar</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Lote|Serie <span id="cve_articulo_cambio_lote_serie"></span></label>
                            <br><br>
                            <label>Nuevo Lote|Serie</label>
                            <input type="text" class="form-control" name="nuevo_lote_serie" id="nuevo_lote_serie" placeholder="Nuevo Lote|Serie">
                            <span id="msj_lote_existente" style="color: red; font-weight: bolder;display: none;">Lote|Serie ya existente</span>
                            <br><br>
                            <label>Lote|Serie Existente</label>
                            <select class="form-control chosen-select" name="select_lotes" id="select_lotes">
                                <option value="">Seleccione Lote</option>
                            </select>

                            <br><br>

                            <input type="date" class="form-control" name="cambiar_caducidad" id="cambiar_caducidad" min="<?php echo $fecha_actual; ?>" value="">
                            <br><br>
                            <select class="form-control chosen-select" name="select_serie" id="select_serie">
                                <option value="">Seleccione Serie</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="mostrar_motivos(1)" type="submit" id="guardar_lote_seria_cambio" class="btn btn-sm btn-primary">Cambiar Lote|Serie</button>
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
            url: '/api/controlcuarentena/lista/index.php',
            datatype: "local",
            autowidth: true,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:["Acción","BL|Zona Rec","Zona de Almacenaje","Productos en QA", "Motivo","Existencias","Peso Máximo","Volumen(m3)","Peso%","Volumen%","Dimensiones (Alt. X Anc. X Lar. )", "tipo_ubicacion"],
            colModel:[
                {name:'myac',index:'', width: 50,fixed:true, sortable:false, resize:false, formatter:imageFormat},
                {name:'BL', index:'BL', width: 120, editable:false, sortable:false},
                {name:'zona_almacenaje',index:'zona_almacenaje', width: 210, editable:false, sortable:false},
                {name:'total_ubicados',index:'total_ubicados',align:"right", width: 130, editable:false, sortable:false},
                {name:'motivo',index:'motivo',align:"left", width: 130, editable:false, sortable:false},
                {name:'existencia_total',index:'existencia_total',align:"right", width: 120, editable:false, sortable:false},
                {name:'PesoMax',index:'PesoMax', width:110, editable:false,align:"right", sortable:false},
                {name:'volumen_m3',index:'volumen_m3', width:110, editable:false,align:"right", sortable:false},
                {name:'peso',index:'peso', width: 100, align:"right", editable:false, sortable:false},
                {name:'volumen',index:'volumen', width: 110, align:"right", editable:false, sortable:false},
                {name:'dim',index:'dim',width:250, editable:false, sortable:false, align:"center"},
                {name:'tipo_ubicacion',index:'tipo_ubicacion',width:250, editable:false, sortable:false, align:"center", hidden: true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            loadComplete: function(data){
                console.log("SUCCES TABLE = ", data);
            }
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
            var tipo_ubicacion = rowObject[11];
            var html = '';

            html += '<a href="#" onclick="detalle(\''+ubicacion+'\', \'' + codigo_CSD + '\', \'' + peso_max + '\', \'' + vol_max + '\', \'' + pes_porcentaje + '\', \'' + vol_porcentaje + '\')"><i class="fa fa-search" alt="Detalle" title="Ver Artículos en Cuarentena"></i></a>&nbsp;&nbsp;&nbsp;';
            if(tipo_ubicacion != 'RTM' && $("#permiso_editar").val() == 1)
            html += '<a href="#" onclick="liberar(\''+ubicacion+'\', \'' + codigo_CSD + '\')"><i class="fa fa-share" aria-hidden="true" title="Liberar toda la ubicación"></i></a>&nbsp;';
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
            url: '/api/controlcuarentena/lista/index.php',
            datatype: "local",
            autowidth: false,
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:['id','Liberar', 'DiaO','Clave','Descripción','Pallet/Contenedor','Lote','Caducidad','Serie','Existencia','Peso U(Kg)','Volumen U(m3)', 'Peso Total(kg)', 'Volumen Total(m3)', 'control_lote', 'control_caducidad', 'control_serie', 'caducidad_input', 'val'],
            colModel:[
                {name:'id',index:'id',width: 300, editable:false, sortable:false, hidden:true},
                {formatter:setInput, width:80, align:"center"},
                {name:'diao',index:'diao',width: 50, editable:false, sortable:false, align:"right"},
                {name:'cve_articulo',index:'cve_articulo',width: 100, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:220, editable:false, sortable:false},
                {name:'conteedor',index:'descripcion',width:130, editable:false, sortable:false},
                {name:'lote',index:'lote',width:100, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:100, editable:false, sortable:false, align:"center"},
                {name:'serie',index:'serie',width:100, editable:false, sortable:false},
                {name:'Existencia_Total',index:'Existencia_Total',width:80, sortable:true, align:"right"},
                {name:'peso_unitario',index:'peso_unitario',width:90, editable:false, sortable:false,align:'right'},
                {name:'volumen_unitario',index:'volumen_unitario',width:132, editable:false, sortable:false,align:'right'},
                {name:'peso_total',index:'peso_total',width:100, editable:false, sortable:false,align:'right'},
                {name:'volumen_total',index:'volumen_total',width:132, editable:false, sortable:false,align:'right'},
                {name:'control_lote',index:'control_lote',width:132, editable:false, sortable:false,align:'right', hidden: true},
                {name:'control_caducidad',index:'control_caducidad',width:132, editable:false, sortable:false,align:'right', hidden: true},
                {name:'control_serie',index:'control_serie',width:132, editable:false, sortable:false,align:'right', hidden: true},
                {name:'caducidad_input',index:'caducidad_input',width:132, editable:false, sortable:false,align:'right', hidden: true},
                {name:'val',index:'val',width:132, editable:false, sortable:false,align:'right', hidden: true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            viewrecords: true,
            loadComplete:function(data){
                console.log("SUCCESS = ", data);
                console.log("TOTAL = ", data.total);
              setTimeout(function(){ 
                if(data.total != 0)
                {
                  var peso_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    console.log("********************************************");
                    console.log("pesos7",pesos[i].cell[9]);										
					console.log("pesos8",pesos[i].cell[10]);
                    peso_total = parseFloat(peso_total) + (parseFloat(pesos[i].cell[9])*pesos[i].cell[10]);
                    console.log(peso_total);
                    console.log("********************************************");
                  }
                  
                  $("#pes_total").val(peso_total.toFixed(4));
                  $("#pes_dispo").val(($("#pes_max").val() - peso_total).toFixed(4));
                  var porcentaje_peso = (100*$("#pes_total").val())/$("#pes_max").val();
                  $("#pes_porcentaje").val(porcentaje_peso.toFixed(4));

                  var volumen_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    volumen_total = parseFloat(volumen_total) + (parseFloat(pesos[i].cell[8]*pesos[i].cell[10]));
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
                $("#select_diao").empty();
                $("#select_diao").append(data.options_diao);
              }, 200);
              
            }, loadError: function(data){console.log("ERROR: ", data);}
            
            
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
      
        function setInput(cellvalue, options, rowObject){
            var id = rowObject[0];
            var cve_articulo = rowObject[3];
            var Contenedor = rowObject[5];//Si esta en RTM es tipo descarga en Cuarentena.
            var lote = rowObject[6];
            var serie = rowObject[8];
            var control_lote = rowObject[14];
            var control_caducidad = rowObject[15];
            var control_serie = rowObject[16];
            var caducidad = rowObject[17];
            var val = rowObject[18];
            var c = 'check'+id;
            var html = '';
            if((control_lote == 'S' || control_serie == 'S') && val != 'RTM')
            {
                html += '<input type="checkbox" name="liberar" class="check_cambiar_lote" id="check'+id+'" value="liberar">';
                if($("#permiso_editar").val() == 1)
                html += '&nbsp;&nbsp;&nbsp;&nbsp; <a href="#" onclick="detalleCambiarLote(\''+c+'\',\''+cve_articulo+'\',\''+control_lote+'\', \''+control_caducidad+'\', \''+control_serie+'\',\''+lote+'\', \''+caducidad+'\', \''+serie+'\')" title="Cambiar Lote|Serie y Liberar"><i class="fa fa-edit" ></i></a>';
            }

            return html;
        }
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

    function detalleCambiarLote(id_check, cve_articulo, control_lote, control_caducidad, control_serie, lote, caducidad, serie)
    {
        console.log("check = ",id_check, " cve_articulo = ",cve_articulo, " control_lote = ", control_lote, " control_caducidad = ", control_caducidad, " control_serie = ", control_serie);
        $("#tiene_lote").val(control_lote);
        $("#tiene_caducidad").val(control_caducidad);
        $("#tiene_serie").val(control_serie);

        $("#select_lotes, #select_lotes_chosen").hide();
        $("#cambiar_caducidad").hide();
        $("#select_serie, #select_serie_chosen").hide();

        if(control_lote == 'S')
        {
            $("#select_lotes_chosen").show();
            if(control_caducidad == 'S')
            {
                $("#cambiar_caducidad").show();
                $("#cambiar_caducidad").val(caducidad);
            }

                $.ajax({
                    url: '/api/controlcuarentena/update/index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                      action: 'cargarLotes',
                      cve_articulo: cve_articulo,
                      lote_a_cambiar: lote
                    }
                })
                .done(function(data){
                    console.log("data lotes = ", data);
                    $("#select_lotes").empty();
                    $("#select_serie").empty();
                    $("#select_lotes").append(data.options);
                    $('.chosen-select').trigger("chosen:updated");
                }).fail(function(data){
                    console.log("ERROR data lotes = ", data);
                });


            $("#select_serie, #select_serie_chosen").hide();
        }

        if(control_serie == 'S')
        {
            $("#select_lotes, #select_lotes_chosen").hide();
            $("#cambiar_caducidad").hide();
            $("#select_serie_chosen").show();

                $.ajax({
                    url: '/api/controlcuarentena/update/index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                      action: 'cargarSeries',
                      cve_articulo: cve_articulo,
                      serie_a_cambiar: serie
                    }
                })
                .done(function(data){
                    console.log("data series = ", data);
                    $("#select_serie").empty();
                    $("#select_lotes").empty();
                    $("#select_serie").append(data.options);
                    $('.chosen-select').trigger("chosen:updated");
                }).fail(function(data){
                    console.log("ERROR data series = ", data);
                });

        }
        $(".check_cambiar_lote").prop("checked", "");
        $('#'+id_check).prop("checked", "checked");
        $("#cve_articulo_cambio_lote_serie").text(cve_articulo);
        $("#cambiar_lote_modal").modal("show");
        //$('.chosen-select').chosen();
        $('.chosen-select').trigger("chosen:updated");
    }

    function buscar()
    {
        console.log("motivoQA = ", $("#motivoqa").val());
        var almacen = $("#almacen").val();
        var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
        var tipoU = $("#select-tipoU").val();
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacen: id,
                almacenaje: $("#almacenp").val(),
                search: $("#buscar").val(),
                motivoqa: $("#motivoqa").val(),
                action: 'loadGrid',
                tipo: tipoU,
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
        getStatistics();
        loadDataToGridDetails();
    }

    function loadDataToGrid(almacenaje)
    {
        var almacen = $("#almacen").val();
        var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
        console.log("motivoQA = ", $("#motivoqa").val());
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                almacenaje: almacenaje,
                almacen: id,
                motivoqa: $("#motivoqa").val(),
                action: 'loadGrid'
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
        getStatistics();
    }

    function getStatistics()
    {
        var almacen = $("#almacen").val();
        var id = $("#almacen option[value='"+almacen+"']").attr("data-id");
        $.ajax({
            url: '/api/controlcuarentena/lista/index.php',
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

    $("#select_diao").change(function(){

        loadDataToGridDetails($("#val_ubicacion").val(), $("#val_almacen").val());

    });

    function loadDataToGridDetails(ubicacion, almacenaje)
    {
        //console.log("*******************loadDataToGridDetails**************************");
        //console.log("val_almacen = ", almacenaje);
        //console.log("val_ubicacion = ", ubicacion);
        $("#val_almacen").val(almacenaje);
        $("#val_ubicacion").val(ubicacion);
        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                ubicacion: ubicacion,
                almacenaje: almacenaje,
                diaoperativo: $("#select_diao").val(),
                action: 'loadDetails'
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
    }

    $("#buscar").keyup(function(event)
    {
        if(event.keyCode == 13){
            buscar()
        }
    });

    function detalle(ubicacion,csd,peso_max,vol_max,pes_porcentaje,vol_porcentaje)
    {
        console.log(peso_max+'-'+vol_max+'-'+pes_porcentaje+'-'+vol_porcentaje);
      
        loadDataToGridDetails(csd, $("#almacen").val());
        $("#tabla_vacia").hide();
        $("#pes_maxpes_total,#pes_dispo,#pes_porcentaje,#vol_max,#vol_total,#vol_dispo,#vol_porcentaje").val("");
        $("#pes_max").val(parseFloat(peso_max));
        $("#vol_max").val(parseFloat(vol_max));
        $("#pes_porcentaje").val(parseFloat(pes_porcentaje));
        $("#vol_porcentaje").val(parseFloat(vol_porcentaje));
        $("#detalle_ubicacion").html("Ubicacion "+csd);
        $("#ver_detalles").modal("show");
        $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                action:'traer_motivos',
            },
            beforeSend: function(x)
            {
              if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcuarentena/lista/index.php',
            success: function(data)
            {
                $("#motivo").removeAttr("disabled");
                if (data.success == true)
                {
                    var options = $("#motivo");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i=0; i<data.motivos.length; i++)
                    {
                        options.append(new Option(data.motivos[i][1], data.motivos[i][0]));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                }
            }
        });
        $("#detalleModal").modal('show');
    }
  
    function liberar(ubicacion, bl){
        $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                  action:'traer_motivos'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcuarentena/lista/index.php',
            success: function(data) {
                $("#motivo").removeAttr("disabled");
                console.log("traer_motivos = ", data);
                if (data.success == true) {
                    var options = $("#motivo2");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i=0; i<data.motivos.length; i++)
                    {
                        options.append(new Option(data.motivos[i][1], data.motivos[i][0]));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                    $("#ubicacion").val(bl);
                    $("#moverModal").modal('show');
                }
            }, error: function(data) {
                console.log("ERROR traer_motivos = ", data);
            }

        });
    }
  

  var permitidos = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M','N','O','P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '/', '-'];
    $("#nuevo_lote_serie").keyup(function(e){
        console.log("KEY = ", e.key.toUpperCase());

            //console.log("NO");
            var folio_pedido = $(this).val();
            var arr_tecla = folio_pedido.split("");

            for(var i = 0; i < arr_tecla.length; i++)
            {
                if($.inArray(arr_tecla[i].toUpperCase(), permitidos) == -1)
                   folio_pedido = folio_pedido.replace(arr_tecla[i].toUpperCase(), '');
            }

            $(this).val(folio_pedido);

            //console.log("SI");
        $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                  action:'lote_serie_existente',
                  cve_articulo: $("#cve_articulo_cambio_lote_serie").text(),
                  lote_serie: $(this).val(),
                  usuario: '<?php echo $_SESSION["id_user"]?>',
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcuarentena/lista/index.php',
            success: function(data){
                //console.log("Lote|Serie Existe = ",data);
                if(data == true)
                {
                    $("#msj_lote_existente").show();
                    $("#guardar_lote_seria_cambio").prop("disabled", true);
                }
                else
                {
                    $("#msj_lote_existente").hide();
                    $("#guardar_lote_seria_cambio").prop("disabled", false);
                }
            }
        });

    });

    function mover(){
        var ubicacion = $("#ubicacion").val();
        var motivo = $("#motivo2").val();
      
        if(motivo == "")
        {
            swal("Error", "Seleccione un motivo", "error");
            return;
        }
        $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                  action:'mover',
                  ubicacion: ubicacion,
                  id_motivo: motivo,
                  usuario: '<?php echo $_SESSION["id_user"]?>',
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcuarentena/lista/index.php',
            success: function(data){
                if (data["success"] == true){
                    $("#moverModal").modal("hide");
                    swal("Éxito","Se ha realizado con éxito","success");
                    buscar();

                }
            }
        });
    }
  
    function mostrar_motivos(cambiar_lote)
    {
        $("#cambiar_lote_val").val(cambiar_lote);

        if(cambiar_lote == 0)
        {
            var existencia =[];
            var j = 0;
            $("#grid-table2>tbody>tr").each(function(i,e)
            {
                console.log("e",i," = ", e);
                if(j>0)
                {
                    if(e.cells[1].firstChild != null)
                    if (e.cells[1].firstChild.checked)
                    {
                        clave = e.cells[2].title;
                        contenedor = e.cells[4].title;
                        lote = e.cells[5].title;
                        serie = e.cells[7].title;
                        val = e.cells[8].title;
                        
                        existencia[j-1] = {};
                        existencia[j-1]["clave"] = clave;
                        existencia[j-1]["contenedor"] = contenedor;
                        existencia[j-1]["existencia"] = val;
                        existencia[j-1]["lote"] = lote;
                        existencia[j-1]["serie"] = serie;
                    }
                }
                j++;
            });
            console.log(existencia);
            if(existencia.length <= 0)
            {
                swal("Error", "Debe seleccionar al menos un articulo", "error");
                return;
            }
            $("#cambiar_lote_modal").modal('hide');
            $("#motivos").modal('show');
        }
        else
        {
            console.log("**liberar_existencias**")
            liberar_existencias();
        }
    }
  
    function liberar_existencias()
    {
        if($("#cambiar_lote_val").val() == 1)
        {
            console.log("tiene_lote = ", $("#tiene_lote").val());
            console.log("select_lotes = ", $("#select_lotes").val());
            console.log("select_lotes_chosen = ", $("#select_lotes_chosen").val());
            console.log("tiene_caducidad = ", $("#tiene_caducidad").val());
            console.log("tiene_serie = ", $("#tiene_serie").val());

            if($("#tiene_lote").val() == 'S' && $("#select_lotes").val() == "" && $("#nuevo_lote_serie").val() == "")
            {
                var mensaje = "Debe Ingresar un Lote";
                swal("Error", mensaje, "error");
                return;
            }

            if($("#tiene_caducidad").val() == 'S' && $("#cambiar_caducidad").val() == "")
            {
                var mensaje = "Debe Ingresar un Lote y Caducidad";
                swal("Error", mensaje, "error");
                return;
            }
            
            if($("#tiene_serie").val() == 'S' && $("#select_serie").val() == "" && $("#nuevo_lote_serie").val() == "")
            {
                swal("Error", "Debe Seleccionar la Serie", "error");
                return;
            }
        }
        //return;
        var existencia =[];
        var j = 0;
        $("#grid-table2>tbody>tr").each(function(i,e){
        
            if(j>0){
                console.log("e",i," = ", e, "e.cells = ", e.cells);
                if(e.cells[1].firstChild != null)
                if (e.cells[1].firstChild.checked){
                    clave = e.cells[3].title;
                    contenedor = e.cells[5].title;
                    lote = e.cells[6].title;
                    serie = e.cells[8].title;
                    val = e.cells[9].title;
                    
                    existencia[j-1] = {};
                    existencia[j-1]["clave"] = clave;
                    existencia[j-1]["contenedor"] = contenedor;
                    existencia[j-1]["existencia"] = val;
                    existencia[j-1]["lote"] = lote;
                    existencia[j-1]["serie"] = serie;
                }
            }
            j++;
        });
        console.log(existencia);
        if(existencia.length <= 0)
        {
            swal("Error", "Debe seleccionar al menos un articulo", "error");
            return;
        }

        var almacen = $("#almacen").val(), almacen_id = $("#almacen").find(':selected').data('id');
        var ubicacion = $("#detalle_ubicacion").text().split(" ")[1];
        var motivo = $("#motivo").val();
        if(motivo == "" && $("#cambiar_lote_val").val() == 0)
        {
            swal("Error", "Seleccione un motivo", "error");
            return;
        }

        console.log("almacen:", almacen);
        console.log("almacen_id:", almacen_id);
        console.log("ubicacion:", ubicacion);
        console.log("existencia:", existencia);
        console.log("id_motivo:", motivo);
        console.log("nuevo_lote_serie:", $("#nuevo_lote_serie").val());
        console.log("tiene_serie:", $("#tiene_serie").val());
        console.log("tiene_lote:", $("#tiene_lote").val());
        console.log("tiene_caducidad:", $("#tiene_caducidad").val());
        console.log("cambiar_caducidad:", $("#cambiar_caducidad").val());
        console.log("cambiar_lote:", $("#cambiar_lote_val").val());
        console.log("lote_serie:", ($("#select_lotes").val() != '')?($("#select_lotes").val()):($("#select_serie").val()));

        //return;
        $.ajax({
            type:'POST',
            datatype: 'json',
            data:{
                  idUser: '<?php echo $_SESSION["id_user"]?>',
                  action:'liberar_existencias',
                  almacen: almacen,
                  almacen_id: almacen_id,
                  ubicacion: ubicacion,
                  existencia: existencia,
                  id_motivo: motivo,
                  nuevo_lote_serie: $("#nuevo_lote_serie").val(),
                  tiene_serie: $("#tiene_serie").val(),
                  tiene_lote: $("#tiene_lote").val(),
                  tiene_caducidad: $("#tiene_caducidad").val(),
                  cambiar_caducidad: $("#cambiar_caducidad").val(),
                  cambiar_lote: $("#cambiar_lote_val").val(),
                  lote_serie: ($("#select_lotes").val() != '')?($("#select_lotes").val()):($("#select_serie").val())

            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url:'/api/controlcuarentena/lista/index.php',
            success: function(data){
                if (data["success"] == true){
                    if($("#cambiar_lote_val").val() == 0)
                    {
                        $("#motivos").modal('hide');
                        $("#detalleModal").modal('hide');
                        swal("Éxito","Se libero el artículo","success");
                        buscar();
                    }
                    else
                    {
                        $("#cambiar_lote_modal").modal('hide');
                        $("#nuevo_lote_serie").val("");
                        loadDataToGridDetails(ubicacion, $("#almacen").val());
                    }
                }
            }, error: function(data){
                console.log("ERROR ", data);
            }
        });    
    }
</script>
