<?php
    $almacenes = new \AlmacenP\AlmacenP();
$listaRuta = new \Ruta\Ruta();
$vendedores = new \Usuarios\Usuarios();

    $rutasvpSql = \db()->prepare("SELECT *  FROM t_ruta WHERE venta_preventa = 1 AND Activo = 1");
    $rutasvpSql->execute();
    $rutasvp = $rutasvpSql->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/switchery.min.css" rel="stylesheet">
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>
<!--script src="/js/jquery-2.1.4.min.js"></script-->
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/switchery.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>
<script src="/js/plugins/validate/jquery.validate.min.js"></script>


<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM,#search {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
    .checkbox-2{
        position: relative;
        display: block;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .blink {
  
      animation-name: blink;
      animation-duration: 4s;
      animation-timing-function: linear;
      animation-iteration-count: infinite;

      -webkit-animation-name:blink;
      -webkit-animation-duration: 4s;
      -webkit-animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
    }

    @-moz-keyframes blink{  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }

    @-webkit-keyframes blink {  
      0% { opacity: 1.0; }
      50% { opacity: 0.0; }
       100% { opacity: 1.0; }
    }

    @keyframes blink {  
      0% { opacity: 1.0; }
       50% { opacity: 0.0; }
      100% { opacity: 1.0; }
    }
</style>

<style type="text/css">

</style>

<div class="wrapper wrapper-content  animated fadeInRight" id="form-prin">
    <h3>Envases</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="almacenes">Almacen:</label>
                                    <select class="form-control" id="almacenes" name="almacenes">
                                        <option value=" ">Seleccione el Almacen</option>
                                        <?php foreach( $almacenes->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                            </div>
                        </div>
                              <div class="col-sm-3">
                                  <div class="form-group">
                                      <label>Cliente</label>
                                      <input id="cliente" class="form-control"></input>
                                  </div>
                              </div>
                              <div class="col-sm-3">
                                  <div class="form-group">
                                      <label>Tipo</label>
                                      <select id="select-tipo" class="chosen-select form-control">
                                          <option value="" selected>Seleccione</option>
                                          <option value="Envase">Envase</option>
                                          <option value="Garrafon">Garrafón</option>
                                          <option value="Plastico">Cajas de Plástico</option>
                                      </select>
                                  </div>
                              </div>
                    </div>


                    <div class="row">
                              <div class="col-sm-3">
                                  <div class="form-group">
                                     <label>Agente</label>
                                    <select class="form-control chosen-select" id="agentes" name="agentes">
                                        <option value="">Seleccione Agente | Operador</option>
                                        <?php 
                                        foreach( $vendedores->getAllVendedor() AS $ch ): 
                                        ?>
                                        <option value="<?php echo $ch->cve_usuario; ?>"><?php echo "( ".$ch->cve_usuario." ) - ".$ch->nombre_completo; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                  </div>
                              </div>
                              <div class="col-sm-3">
                                    <div class="form-group">
                                       <label>Ruta</label>
                                        <select id="rutas_ventas" class="form-control">
                                            <option value="">Ruta</option>
                                            <?php foreach( $rutasvp AS $p ): ?>
                                                <option value="<?php echo $p["cve_ruta"];?>"><?php echo $p["cve_ruta"]." | ".$p["descripcion"]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                              </div>

                        <div class="col-sm-3">
                                <label>Status</label>
                                <select id="select-existencia" class="chosen-select form-control">
                                    <option value="0">Todos los Status</option>
                                    <option value="1">Ocupado</option>
                                    <option value="2">Libre</option>
                                </select>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input id="fecha" class="form-control"></input>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input id="fecha-fin" class="form-control"></input>
                            </div>
                        </div>

                        <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <input type="text" class="form-control " name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                </div>
                        </div>

                        <div class="col-sm-3">
                           <div class="form-group">
                                  <button onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary" style="margin-top: 22px">
                                      <i class="fa fa-search"></i> Buscar
                                  </button>
                            </div>
                        </div>

                    </div>

                    <div class="row text-center">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Envases</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Garrafón</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Cajas de Plástico</label>
                            </div>
                        </div>
                    </div> 
                    <div class="row text-center">
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Venta</label>
                                <input style="text-align:center;" id="plibre" class="form-control" disabled style="margin-left: 10px"></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Promoción</label>
                                <input style="text-align:center;" id="pocupado" class="form-control" disabled style="margin-left: 10px"></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Consignación</label>
                                <input style="text-align:center;margin-left:8px;" id="pconsignacion" class="form-control" disabled style="margin-left: 10px"></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Total</label>
                                <input style="text-align:center;" id="ptotal" class="form-control" disabled></input>
                            </div>
                        </div>

                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Venta</label>
                                <input style="text-align:center;" id="clibre" class="form-control" disabled ></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Promoción</label>
                                <input style="text-align:center;" id="cocupado" class="form-control" disabled></input>
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                <label>Consignación</label>
                                <input style="text-align:center;margin-left:8px;" id="cconsignacion" class="form-control" disabled></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Total</label>
                                <input style="text-align:center;" id="ctotal" class="form-control" disabled ></input>
                            </div>
                        </div>     

                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Venta</label>
                                <input style="text-align:center;" id="plibre" class="form-control" disabled ></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Promoción</label>
                                <input style="text-align:center;" id="pocupado" class="form-control" disabled></input>
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                <label>Consignación</label>
                                <input style="text-align:center;margin-left:8px;" id="pconsignacion" class="form-control" disabled></input>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label>Total</label>
                                <input style="text-align:center;" id="ptotal" class="form-control" disabled ></input>
                            </div>
                        </div>     
                  </div>

              </div>
                <div class="ibox-content">
                    <div class="jqGrid_wrapper" >
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_detalles" role="dialog">
    <div class="modal-dialog modal-lg">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4>Detalle Contenedor|Pallet</h4>
            </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table id="detalles"class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>CONTENEDOR/PALLET</th>
                                        <th>LP</th>
                                        <th>CLAVE</th>
                                        <th>DESCRIPCIÓN</th>
                                        <th>LOTE</th>
                                        <th>CADUCIDAD</th>
                                        <th>SERIE</th>
                                        <th>EXISTENCIA</th>                    
                                    </tr>
                                </thead>  
                                <tbody></tbody>              
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
          </div>
      </div>
</div>

<script>
  
    $(function($) 
    {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";
        //resize to fit page size
        $(window).on('resize.jqGrid', function() 
        {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) 
        {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() 
                {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                },0);
            }
        })

        $(grid_selector).jqGrid(
        {
            url: '/api/palletsycontenedores/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                lp: $("#input-lp").val(),
                vacio: $("#select-existencia").val()
            },
            mtype: 'POST',
            colNames: ['ID','Acciones', 'Folio Doc','Fecha', 'Envase', 'Descripción', 'Status', 'Tipo', 'Clave Cliente','Cliente','Razón Social','Responsable','Fecha Consignación','Días en Prestamo','Autorizó'],
            colModel: [
                {name: 'IDContenedor',index: 'IDContenedor',width: 10,editable: false,hidden: true,sortable: false},
                {name: 'myac',index:'myac', width:120, fixed:true, sortable:false, resize:false, align:"center", formatter: imageFormat},
                {name: 'ClaveLP',index: 'ClaveLP',width: 150,editable: false,sortable: false, align:"left"},
                {name: 'destino',index: 'destino',width: 100,editable: false,sortable: false, align:"center"},
                {name: 'clave',index: 'clave',width: 150, editable: false,sortable: false, align:"center"}, 
                {name: 'descripcion',index: 'descripcion',width: 150,editable: false, sortable: false, align:"center"}, 
                {name: 'statu',index: 'statu',width: 100,editable: false,sortable: false, align:"center"}, 
                {name: 'tipo',index: 'tipo',width: 100,editable: false,sortable: false, align:"center"},
                {name: 'bl',index: 'bl',width: 100,editable: false,sortable: false, align:"center"},
                {name: 'cliente',index: 'cliente',width: 100,editable: false,sortable: false, align:"center", hidden: true},
                {name: 'razon',index: 'razon',width: 300,editable: false,sortable: false, align:"center", hidden: true},
                {name: 'direccion',index: 'direccion',width: 200,editable: false,sortable: false},
                {name: 'fecha',index: 'fecha',width: 125,editable: false,sortable: false, align:"center"},
                {name: 'dias',index: 'dias',width: 120,editable: false,sortable: false, align:"center"},
                {name: 'des_almac',index: 'des_almac',width: 150,editable: false,sortable: false,align:"center"},
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'IDContenedor',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: almacenPrede()
        });
        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            height: 200,
            reloadAfterSubmit: true
        });
        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
    function imageFormat(cellvalue, options, rowObject) 
    {
        var clave = rowObject[2];
        var ubicacion = rowObject[6];
        var almacen = rowObject[13];
        var cliente = rowObject[8];
        $("#hiddenContenedor").val(clave);
        var html = '';
        html += '<a href="#" onclick="ver(\''+clave+'\', \'' + ubicacion + '\', \'' + cliente + '\')"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';
        html += '<a href="#" onclick="PDF(\'' + almacen + '\')"><i class="fa fa-print"></i>PDF</a>&nbsp;&nbsp;&nbsp;';
        html += '<a href="#" onclick="excel(\'' + clave + '\', \'' + almacen + '\')"><i class="fa fa-print"></i>Excel</a>&nbsp;&nbsp;&nbsp;';
        return html;
    }

    function aceSwitch(cellvalue, options, cell) 
    {
        setTimeout(function() {
            $(cell).find('input[type=checkbox]')
                .addClass('ace ace-switch ace-switch-5')
                .after('<span class="lbl"></span>');
        }, 0);
    }
    //enable datepicker
    function pickDate(cellvalue, options, cell) 
    {
        setTimeout(function() {
            $(cell).find('input[type=text]')
                .datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true
                });
        }, 0);
    }

    function beforeDeleteCallback(e) 
    {
        var form = $(e[0]);
        if (form.data('styled')) return false;
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
            success: function(data) {
                grid.trigger("reloadGrid", [{
                    current: true
                }]);
            },
            error: function() {}
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

    function enableTooltips(table)
    {
        $('.navtable .ui-pg-button').tooltip({
            container: 'body'
        });
        $(table).find('.ui-pg-div').tooltip({
            container: 'body'
        });
    }

    //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

    $(document).one('ajaxloadstart.page', function(e) 
    {
        $(grid_selector).jqGrid('GridUnload');
        $('.ui-jqdialog').remove();
    });
});
  
  //////////////////////////////////////////////////////////////Nueva Tabla //////////////////////////////////////////////////////////////////
    function traer_cant_ac(){
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/palletsycontenedores/lista/index.php',
          data: {
              action : 'traer_cant_ac',
              criterio: $("#txtCriterio").val(),
              lp: $("#input-lp").val(),
              almacen: $("#almacenes").val(),
              clave: $("#pallet-contenedor").val(),
              cliente: $("#cliente").val(),
              tipo: $("#select-tipo").val(),
              bl: $("#slect-BL").val(),
              vacio: $("#select-existencia").val()
          },
          beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
          },
          success: function(data) {
              console.log(data);
              $("#cconsignacion").val(data.consignacion1);
              $("#cocupado").val(data.almacen1);
              $("#clibre").val(data.almacen2);
              $("#ctotal").val(data.almacen2+data.almacen1+data.consignacion1);
              $("#pconsignacion").val(data.consignacion2);
              $("#pocupado").val(data.almacen3);
              $("#plibre").val(data.almacen4);
              $("#ptotal").val(data.almacen3+data.almacen4+data.consignacion2);
            
          },
          error:function(res){
              window.console.log(res);
          }
      });
    }
  
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    lp: $("#input-lp").val(),
                    almacen: $("#almacenes").val(),
                    clave: $("#pallet-contenedor").val(),
                    cliente: $("#cliente").val(),
                    tipo: $("#select-tipo").val(),
                    bl: $("#slect-BL").val(),
                    vacio: $("#select-existencia").val()
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
          traer_cant_ac();
    }
  
    function ver(clave,ubicacion, cliente)
    {
    console.log("clave",clave);
    console.log("ubicacion",ubicacion);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/api/palletsycontenedores/lista/index.php",
            data: {
                action: 'detalles',
                clave: clave,
                cliente: cliente, 
                ubicacion: ubicacion
            },
            beforeSend: function(x) 
            { 
            if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            $('#detalles > tbody').html('<tr><td colspan="8">Cargando datos...</td></tr>');
            },
            success: function(data) 
            {
                if (data.success == true) 
                {
										html = "";
										console.log("entro",data.data);
                    $.each(data.data, function(key, value){
											console.log("val",value);
                        html += '<tr>'+
                        '<td align="right">'+value.clave+'</td>'+
                        '<td align="right">'+value.pallet+'</td>'+
                        '<td>'+value.articulo+'</td>'+
                        '<td>'+value.descripcion+'</td>'+
                        '<td>'+value.lote+'</td>'+
                        '<td>'+value.caducidad+'</td>'+
                        '<td>'+value.serie+'</td>'+
                        '<td align="right">'+value.existencia+'</td>'+
                        '</tr>';
											console.log("html", html);
                    });
                    $('#detalles > tbody').html(html);
                }
            },
            error: function(res)
            {
                window.console.log(res);
            }
        });
        $('#modal_detalles').modal('show');  
    }
  
    function PDF(){
        console.log('PDF()');  

    }

    function excel(){
        console.log('excel()');
    }
  
    function almacenPrede() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/almacenPredeterminado/index.php',
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            success: function(data) {
                if (data.success == true) {
                    document.getElementById('almacenes').value = data.codigo.id;
                    document.getElementById('select-tipo').value = data.codigo.tipo;
                    setTimeout(function() {
                        ReloadGrid();
                    }, 1000);
                }
                traer_cant_ac();
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function configCodiBL(){

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'enter-codiBL'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/index.php',
            success: function(data) {
                config(data.codigoBL[0].codigo);
            },
            error:function(res){
                window.console.log(res);
            }

        });

        function config(node){

            check_pasillo.checked = false;
            check_rack.checked = false;
            check_nivel.checked = false;
            check_seccion.checked = false;
            check_posicion.checked = false;
            input_codigo_bl.value = "";

            var array = node.split("-");

            if(array.length > 0){
                for(var i = 0; i < array.length; i++){
                    if(array[i] === "cve_pasillo"){
                        check_pasillo.checked = true;
                        addCodBl(check_pasillo.value);
                    }
                    else if(array[i] === "cve_rack"){
                        check_rack.checked = true;
                        addCodBl(check_rack.value);
                    }
                    else if(array[i] === "cve_nivel"){
                        check_nivel.checked = true;
                        addCodBl(check_nivel.value);
                    }
                    else if(array[i] === "Seccion"){
                        check_seccion.checked = true;
                        addCodBl(check_seccion.value);
                    }
                    else if(array[i] === "Ubicacion"){
                        check_posicion.checked = true;
                        addCodBl(check_posicion.value);
                    }
                }
            }
        }
    }

    function init(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'enter-view'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/palletsycontenedores/lista/index.php',
            success: function(data) {
                fillsAlmacens(data.almacens);
                almacenPrede();
            },
            error:function(res){
                window.console.log(res);
            }

        });
    }

    function changeAlmacens(element, select, valor){
        
        var value = element.value;

        if(value){

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'search-alma',
                    'id': value
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    //console.log(data.zona[0]["BL"]);
                    //$("#codigo_BL_name").text(data.BL);
                    fillZona(data.zona, select, valor);
                    //serchTableInfo();
                },
                error:function(res){
                    window.console.log(res);
                }

            });
        }
        else{
            fillZona(null, select);
        }

        function fillZona(node, element, valor){

            var options = "<option value = ''>Seleccione una zona de almacenaje</option>";

            if(node){
                
                for(var i = 0; i < node.length; i++){console.log(node[i].des_almac)
                    options += "<option value = "+node[i].cve_almac+">"+htmlEntities(node[i].des_almac)+"</option>";
                }
            }

            element.innerHTML = options;

            if(valor)
                element.value = valor;
        }
      this.changeBL()
    }
  
  
    function changeBL()
    {
        $.ajax({
           type:"POST",
           dataType:"json",
           data: {
             action:"traer_BL",
             almacen: select_almacen_1.value
           },
           beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
           url:"/api/ubicacionalmacenaje/index.php",
          success: function(data){
            console.log(data);
            var options = "<option value = ''>Seleccione BL</option>";
            for(var i = 0; i < data.length; i++){
                options += "<option value = "+data[i].CodigoCSD+">"+htmlEntities(data[i].CodigoCSD)+"</option>";
            }
            select_BL.innerHTML = options;
          },
          error: function(res){
            window.console.log(res);
          }
        });
    }
  
    

    function fillsAlmacens(node){

        var options = "";

        if(node){

            for(var i = 0; i < node.length; i++){
                options += "<option value = "+node[i].clave+">"+htmlEntities(node[i].clave)+" - "+htmlEntities(node[i].nombre)+"</option>";
            }
        }

        select_almacen_1.innerHTML += options;
        select_almacen_2.innerHTML += options;
        select_alma_re.innerHTML += options;
    }

    function fillTableInfo(node){

        tableDataInfo.destroy();

        var data = [];

        DATA = node;

        for(var i = 0; i < node.length ; i++){

            var volumen = (node[i].num_ancho / 1000) * (node[i].num_alto / 1000) * (node[i].num_largo / 1000);
            var dimension = (node[i].num_alto)+" X "+(node[i].num_ancho)+" X "+(node[i].num_largo);
                volumen = volumen.toFixed(2); 

            var Picking = (node[i].picking === "S")  ? 'check' : 'close',
                Ptl = (node[i].Ptl === "S")  ? 'check' : 'close',
                libre = (node[i].Tipo === "L")  ? 'check' : 'close',
                reser = (node[i].Tipo === "R")  ? 'check' : 'close',
                cuaren = (node[i].Tipo === "Q")  ? 'check' : 'close',
                mixt = (node[i].AcomodoMixto === "S")  ? 'check' : 'close';

            var PickingC = (node[i].picking === "S")  ? 'success' : 'danger',
                PtlC = (node[i].Ptl === "S")  ? 'success' : 'danger',
                libreC = (node[i].Tipo === "L")  ? 'success' : 'danger',
                reserC = (node[i].Tipo === "R")  ? 'success' : 'danger',
                cuarenC = (node[i].Tipo === "Q")  ? 'success' : 'danger',
                mixtC = (node[i].AcomodoMixto === "S")  ? 'success' : 'danger';

            data.push([
                node[i].CodigoCSD,
                node[i].zona,
                node[i].cve_pasillo,
                node[i].cve_rack,
                node[i].cve_nivel,
                node[i].Seccion,
                node[i].Ubicacion,
                node[i].PesoMaximo,
                volumen,dimension,
                node[i].pes_porcentaje,
                node[i].vol_porcentaje,
                '<i class="text-'+PickingC+' fa fa-'+Picking+'"></i>',
                '<i class="text-'+PtlC+' fa fa-'+Ptl+'"></i>',
                '<i class="text-'+libreC+' fa fa-'+libre+'"></i>',
                '<i class="text-'+reserC+' fa fa-'+reser+'"></i>',
                '<i class="text-'+cuarenC+' fa fa-'+cuaren+'"></i>',
                '<i class="text-'+mixtC+' fa fa-'+mixt+'"></i>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-pencil btnEdit" onclick="editUbi('+i+')"></i>'+
                '</a>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-remove btnRemo" onclick="remove('+i+')"></i>'+
                '</a>',
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-table-'+i+'" class="glyphicon glyphicon-search btnSearch" onclick="seachTableZona('+i+')"></i>'+
                '</a>']);
        }

        tableDataInfo.init("table-info",false, true, data);
    }


    function seachTableRecu(){

        $("#modal-recu").modal();

        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            url:'/api/ubicacionalmacenaje/lista/index_i.php',
            datatype: "json",
            shrinkToFit: true,
            height:250,
            postData: {
                criterio: input_criterio.value,
                almacen: select_alma_re.value
            },
            mtype: 'POST',
            colNames:['','','BL','Zona de Almacenaje','Pasillo','Rack','Nivel','Sección','Posición','Peso Máx.','Dimensiones (Lar. X Anc. X Alt. )','Picking', 'Acciones'],
            colModel:[
                {name:'cve_almac',index:'cve_almac', width:80, editable:false, sortable:false, hidden:true},
                {name:'idy_ubica',index:'idy_ubica', width:150, editable:false, sortable:false, hidden:true},
                {name:'CodigoCSD',index:'CodigoCSD', width:100, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'des_almac',index:'des_almac',width:250, editable:false, align:"justify", sorttype: "text"},
                {name:'cve_pasillo',index:'cve_pasillo', width:110, editable:false, align:"center", sorttype: "text"},
                {name:'cve_rack',index:'cve_rack', width:110, editable:false, align:"center"},
                {name:'cve_nivel',index:'cve_nivel',width:110, editable:false, align:"center", sorttype: "text"},
                {name:'Seccion',index:'Seccion', width:110, editable:false, align:"center", sorttype: "int"},
                {name:'Ubicacion',index:'Ubicacion', width:110, editable:false, align:"center", sorttype: "int"},
                {name:'PesoMaximo',index:'PesoMaximo', width:110, editable:false, sortable:false, align:"center"},
                {name:'dim',index:'dim',width:330, editable:false, sortable:false, align:"center"},
                {name:'picking',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center",hidden:true},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageFormat},
            ],
            loadonce: false,
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_almac',
            sortorder: "desc",
            viewrecords: true,
            loadComplete: function(data){
              console.log("-----------------------");
              console.log(data);
            }
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
                                 {edit: false, add: false, del: false, search: false},
                                 {height: 200, reloadAfterSubmit: true}
                                );


        $(window).triggerHandler('resize.jqGrid');

        function imageFormat( cellvalue, options, rowObject ){

            var serie = rowObject[1];

            var html = '';

            html += '<a href="#" onclick="ubiRecu(\''+serie+'\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            return html;
        }


    }
  
   /* $(function($) {//EDG
        var grid_selector_detalles = "#grid-table_detalles";
        var pager_selector_detalles = "#grid-pager_detalles";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_detalles).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_detalles).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector_detalles).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector_detalles).jqGrid({
            datatype: "local",
            mtype: 'GET',
            shrinkToFit: false,
            autowidth: true,
            height:'auto',
            mtype: 'GET',
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
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector_detalles,
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
                  }
                  $("#pes_total").val(peso_total.toFixed(4));
                  $("#pes_dispo").val(($("#pes_max").val() - peso_total).toFixed(4));
                  var porcentaje_peso = (100*$("#pes_total").val())/$("#pes_max").val();
                  $("#pes_porcentaje").val(porcentaje_peso.toFixed(4));

                  var volumen_total = "0";
                  var pesos = data.rows;
                  for(var i=0; i<pesos.length; i++)
                  {
                    volumen_total = parseFloat(volumen_total) + (parseFloat(pesos[i].cell[8])*pesos[i].cell[6]);
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
        $(grid_selector_detalles).jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector_detalles).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });*/

    function fillTableRecu(node){

        tableDataRecu.destroy();

        var data = [];

        DATA_R = node;

        for(var i = 0; i < node.length ; i++){

            data.push([
                node[i].CodigoCSD,
                node[i].zona,
                node[i].cve_pasillo,
                node[i].cve_rack,
                '<a href="##" class="pointer-events: none;cursor: default;">'+
                  '<i id="button-tableR-'+i+'" class="glyphicon glyphicon-plus btnRecu" ></i>'+
                '</a>']);
        }

        tableDataRecu.init("table-recu",false, true, data);
        $(".btnRecu").click(function(){
            var array = this.id.split("-"),
                id = parseInt(array[2]);
            ubiRecu(DATA_R[id]);             
        });
    }

    function seachTableZona(id){

        var node = DATA[id];

        $("#modal-zona").modal();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : 'search-table-zona',
                id : node.idy_ubica
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/index.php',
            success: function(data) {
                setTimeout(function() {
                    fillTableZona(data.table);
                }, 1000);
            },
            error:function(res){
                window.console.log(res);
            }
        });
    }

    function fillTableZona(node){

        tableDataZone.destroy();

        var data = [];

        for(var i = 0; i < node.length; i++){

            data.push([
                node[i].clave,
                node[i].arti,
                node[i].ubica,
                node[i].cve_lote,
                node[i].Existencia
                ]);
        }

        tableDataZone.init("table-zona",false, true, data);
    }

    function ubiRecu(idy_ubica){

        $.ajax({
            url: "/api/ubicacionalmacenaje/index.php",
            type: "POST",
            data: {
                "action" : "recuperar",
                "id": idy_ubica
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
                var data = {almacen : select_alma_re.value };

                $("#grid-table2").jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
                .trigger('reloadGrid',[{current:true}]);
            },
            error : function(res){
                window.console.log(res);
            }
        });
    }

    function save(){
      console.log("funcion save");
        if(ID_MODI)
            saveEdit();
        else
            saveAdd();
    }

    function saveEdit(){
      console.log("funcion save edite");
        var alto = input_alto.value,
            ancho = input_ancho.value,
            fondo = input_fondo.value,
            pesoM = input_pesoM.value,
            tipo = 'L',
            pick = 'N',
            ptl = 'N',
            acoMixt = 'N',
            arePro = 'N',
            tecno = 'EDA';

        if(check_pick.checked)
            pick = 'S';
        console.log(check_ubi.checked);
        if(check_ubi.checked){
            ptl = 'S';
            tecno = 'PTL';
        }

        if(check_acoM.checked)
            acoMixt = 'S';

        if(check_area.checked)
            arePro = 'S';

        if(radio_status_res.checked)
            tipo = 'R';
        else if(radio_status_cua.checked)
            tipo = 'Q';

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'edit',
                id : ID_MODI,
                alto : alto,
                ancho : ancho,
                fondo : fondo,
                pesoM : pesoM,
                tipo : tipo,
                pick : pick,
                ptl : ptl,
                acoMixt : acoMixt,
                arePro : arePro,
                tecno : tecno
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicacionalmacenaje/index.php',
            success: function(data) { console.log(data);
                if(data.msj === "success"){
                    swal({
                        title: "¡Excelente!",
                        text: "Ubicación Modificada.",
                        type: "warning",
                        showCancelButton: false
                    });
                    cerrarAgregar();
                    serchTableInfo();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }

    function saveAdd(){
      console.log("funcion save add");

        if( $('#radio-ubi-rack').prop('checked') && $('#input-rack').val() == '' ){
            swal({
                title: "Advertencia!",
                text: "Debe ingresar el Rack.",
                type: "warning",
                showCancelButton: false
            });
            return false
        }

        var almacen = select_almacen_2.value,
            zone = select_zonaA_2.value,
            pasillo = input_pasillo.value,
            rack = input_rack.value,
            alto = input_alto.value,
            ancho = input_ancho.value,
            fondo = input_fondo.value,
            pesoM = input_pesoM.value,
            ubSeccion = input_seccU.value,
            niveles = "",
            seccion = "",
            tipo = 'L',
            pick = 'N',
            ptl = 'N',
            acoMixt = 'N',
            arePro = 'N',
            tecno = 'EDA',
            code = '',
            msj = false;

        if(check_pick.checked)
            pick = 'S';
        
        if(check_ubi.checked){
            ptl = 'S';
            tecno = 'PTL';
        }

        if(check_acoM.checked)
            acoMixt = 'S';

        if(check_area.checked)
            arePro = 'S';

        if(radio_status_res.checked)
            tipo = 'R';
        else if(radio_status_cua.checked)
            tipo = 'Q';

        if(radio_ubi_rack.checked){
            if(radio_nivels.checked){
                if(input_nivel.value === "")
                    msj = true;
                else
                    niveles = input_nivel.value;
            }
            else{
                if(input_nivelsR_D.value === "" || input_nivelsR_A.value === "")
                    msj = true;
                else
                    niveles = input_nivelsR_A.value;
            }
        }
        else{
            niveles = 0;
        }

        if(radio_secc.checked){
            if(input_secc.value === "")
                msj = true;
            else
                seccion = input_secc.value;
        }
        else{
            if(input_seccR_D.value === "" || input_seccR_A.value === "")
                msj = true;
            else
                seccion = input_seccR_A.value;
        }

        code = fillCodigo();

        if(!msj){
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action: 'save',
                    zone : zone,
                    pasillo : pasillo,
                    rack : rack,
                    niveles : niveles,
                    alto : alto,
                    ancho : ancho,
                    fondo : fondo,
                    pesoM : pesoM,
                    ubSeccion : ubSeccion,
                    seccion : seccion,
                    tipo : tipo,
                    pick : pick,
                    ptl : ptl,
                    acoMixt : acoMixt,
                    arePro : arePro,
                    tecno : tecno,
                    code : code
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/ubicacionalmacenaje/index.php',
                success: function(data) {
                    if(data.msj === "success"){
                        swal({
                            title: "¡Excelente!",
                            text: "Ubicación guardada.",
                            type: "warning",
                            showCancelButton: false
                        });
                        cerrarAgregar();
                        serchTableInfo();
                    }
                    else{
                        swal({
                            title: "¡Alerta!",
                            text: "Ubicación Existente",
                            type: "warning",
                            showCancelButton: false
                        });
                    }

                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        else{
            swal({
                title: "¡Alerta!",
                text: "Faltan campos por llenar",
                type: "warning",
                showCancelButton: false
            });
        }

        function fillCodigo(){

            var code = "",
                array = input_codigo_bl.value.split("-"),
                newArray = [],
                i = 0;

            for(i = 0; i < array.length; i++){

                var value = array[i];

                if(value === "Pasillo")
                    newArray.push(pasillo);
                else if(value === "Rack")
                    newArray.push(rack);
                else if(value === "Nivel")
                    newArray.push(niveles);
                else if(value === "Seccion")
                    newArray.push(seccion);
                else if(value === "Posicion")
                    newArray.push(ubSeccion);
            }

            code = newArray.toString().replace(",", "-");

            for(i = 0; i < 3; i++)
                code = code.toString().replace(",", "-");

            return code;
        }
    }

    function remove(id){

        swal({
            title: "¿Está seguro que desea borrar esta ubicacion?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar"
        },
        function(){
            $.ajax({
                url: "/api/ubicacionalmacenaje/index.php",
                type: "POST",
                data: {
                    "action" : "remove",
                    "id": id
                },
                beforeSend: function(x){
                    if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                    /*swal({
                        title: "¡Alerta!",
                        text: "Ubicacion Borrada.",
                        type: "warning",
                        showCancelButton: false
                    });*/
                    serchTableInfo();
                },
                error : function(res){
                    window.console.log(res);
                }
            });
        });
    }

    function getAllUbi(){

        var JSON = {};

        $.ajax({
            url: "/api/ubicacionalmacenaje/index.php",
            type: "POST",
            data: {
                "action" : "search-all-ubica"
            },
            beforeSend: function(x){
                if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
            },
            success: function(res){
               JSON = res.res;
               console.log("Iniciando Registro");
               execute(0);
            },
            error : function(res){
                window.console.log(res);
            }
        });

        function execute(count){

            var next = count + 1;

            if(count < JSON.length){

                var node = JSON[count];
                
                var id = node["idy_ubica"],
                    cve_pasillo = node["cve_pasillo"],
                    cve_rack = node["cve_rack"],
                    cve_nivel = node["cve_nivel"],
                    Seccion = node["Seccion"],
                    Ubicacion = node["Ubicacion"];

                var code = cve_pasillo+'-'+cve_rack+'-'+cve_nivel+'-'+Seccion+'-'+Ubicacion;
                    
                $.ajax({
                    url: "/api/ubicacionalmacenaje/index.php",
                    type: "POST",
                    dataType: "json",
                     data: {
                        action: 'change-all-ubica',
                        id : id,  
                        code : code
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(res) {
                        var msj = "Registro (" + next +") de " + JSON.length;
                        window.console.log(msj);
                        execute(next);
                    },
                    error: function(res) {
                        window.console.log(res);
                    }
                });
            }
            else{
                console.log("Se guardaron todos los registros");
            }
        }
    }

    

    function validateNumber(e){

        var key = window.event ? e.which : e.keyCode;

        if (key < 48 || key > 57) {
            e.preventDefault();
        }
    }

    function setGridWidth(grid_selector){
        
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
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
        });
    }
  
    function traer_totales(id)
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action: "traer_totales",//EDG
          id: id,
          almacen: $("#select-almacen-1").val(),
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        url: '/api/ubicacionalmacenaje/lista/index.php',
        success: function(data) {
          console.log(data);
          $("#pes_"+id).val();
          $("#vol_"+id).val();
        },
      });
    }

    function serchTableInfo(){ 
        
        var nombre_BL = "BL";
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";
        var criterio = "",
            almacen = select_almacen_1.value; 

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            
            url:'/api/palletsycontenedores/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                criterio: criterio,
                almacen: almacen
            },
            mtype: 'POST',
            colNames:['','','Acciones',nombre_BL,'Zona','Productos Ubicados','Pasillo','Rack','Nivel','Sección','Posición','Peso Máx.','Volumen (m3)','Peso %','Volumen %','Dimensiones (Alt. X Anc. X Lar. )','Picking', 'PTL',/*'Maximo','Minimo',*/'Libre', 'Reservada', 'Cuarentena', 'Acomodo Mixto' ],
            colModel:[
                {name:'cve_almac',index:'cve_almac', width:110, editable:false, sortable:false, hidden:true},
                {name:'idy_ubica',index:'idy_ubica', width:110, editable:false, sortable:false, hidden:true},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageFormat},
                {name:'CodigoCSD',index:'CodigoCSD', width:110, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'des_almac',index:'des_almac',width:150, editable:false, align:"justify", sorttype: "text"},
                {name:'ubicados',index:'ubicados',width:150,editable:false,align:"right"},
                {name:'cve_pasillo',index:'cve_pasillo', width:110, editable:false, align:"center", sorttype: "text", hidden:true},
                {name:'cve_rack',index:'cve_rack', width:110, editable:false, align:"center", hidden:true},
                {name:'cve_nivel',index:'cve_nivel',width:110, editable:false, align:"center", sorttype: "text", hidden:true},
                {name:'Seccion',index:'Seccion', width:110, editable:false, align:"center", sorttype: "int", hidden:true},
                {name:'Ubicacion',index:'Ubicacion', width:110, editable:false, align:"center", sorttype: "int", hidden:true},
                {name:'PesoMaximo',index:'PesoMaximo', width:110, editable:false, sortable:false, align:"right"},
                {name:'volumen',index:'volumen', width:110, editable:false, sortable:false, align:"right"},
                {name:'pes_porcentaje',index:'pes_porcentaje', width:110, editable:false, sortable:false, align:"center"},
                {name:'vol_porcentaje',index:'vol_porcentaje', width:110, editable:false, sortable:false, align:"center"},
                {name:'dim',index:'dim',width:250, editable:false, sortable:false, align:"right"},
                {name:'picking',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck},
                {name:'ptl',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck2},
                {name:'li',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck3},
                {name:'re',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck4},
                {name:'cu',index:'', width:80, fixed:true, sortable:false, resize:false, align:"center", formatter:imageCheck5},
                {name:'acomodomixto',index:'acomodomixto', width:110, fixed:true, sortable:false, resize:false, align:"center", formatter:acomodoMixto},
                
            ],
            loadonce: false,
            rowNum:30,
            rowList:[30,40,50],
            pager: "#grid-pager",
            sortname: 'cve_almac',
            sortorder: "desc",
            viewrecords: true,
            gridComplete: function(){
                //$("#grid-table").setGridParam({datatype: 'local'});
            },
        /*    loadComplete:function(data){
              var datos = data.rows;
              for(var i=0; i<datos.length; i++)
              {
                console.log("DEmo",datos[i].cell[1]);
                var id_ubicacion = datos[i].cell[1];
                traer_totales(id_ubicacion);
              }
              console.log("*********************");
              console.log(data);
              $("#codigo_BL_name").html("BL: "+data.bl);
            }*/
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false,reloadGridOptions: { fromServer: true }},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function imageCheck(cellvalue, options, rowObject){
            var picking = rowObject[16];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }
      
        function pes_total(cellvalue, options, rowObject){
          var id = rowObject[1];
          return '<div><input id="pes_'+id+'" disabled></div>';
        }
      
        function vol_total(cellvalue, options, rowObject){
          var id = rowObject[1];
          return '<div><input id="vol_'+id+'" disabled></div>';
        }

        function imageCheck2(cellvalue, options, rowObject){
            var picking = rowObject[17];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck3(cellvalue, options, rowObject){
            var picking = rowObject[18];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }

        function imageCheck4(cellvalue, options, rowObject){
            var picking = rowObject[19];
            if (picking === "S"){
                return '<div class="text-success"><i class="fa fa-check"></i></div>';
                //return '<input type="checkbox" checked="checked" disabled="disabled" />';
            }else{
                return '<div class="text-danger"><i class="fa fa-close"></div</i>';
                //return '<input type="checkbox" disabled="disabled" />';
            }
        }


        function imageCheck5(cellvalue, options, rowObject){
            var picking = rowObject[20];
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
            if (rowObject[21] === "S"){
                html =  '<div class="text-success"><i class="fa fa-check"></i></div>';
            }else{
                html =  '<div class="text-danger"><i class="fa fa-close"></div</i>';
            }
            return html;
        }


        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var idy_ubica = rowObject[1];
            var correl = rowObject[5];
            var codigo_CSD = rowObject[3];
            var peso_max = rowObject[11];
            var vol_max = rowObject[12];
            var pes_porcentaje = rowObject[13];
            var vol_porcentaje = rowObject[14];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;

            var html = '';//EDG
            html += '<a href="#" onclick="ver(\'' + idy_ubica + '\', \'' + codigo_CSD + '\', \'' + peso_max + '\', \'' + vol_max + '\', \'' + pes_porcentaje + '\', \'' + vol_porcentaje + '\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editUbi(\''+idy_ubica+'\')" alt="Editar" title="Editar"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="remove(\''+idy_ubica+'\')" alt="Eliminar" title="Eliminar"><i class="fa fa-eraser"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
          
            /*html += '<a href="#" onclick="search_alm_produ('+serie+','+idy_ubica+')" alt="Buscar" title="Buscar"><i class="fa fa-search"></i></a>';*/

            return html;
        }
    }
       
</script>
