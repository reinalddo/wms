
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<script src="/js/jszip.min.js"></script>
<script src="/js/pdfmake.min.js"></script>
<script src="/js/vfs_fonts.js"></script>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>


<style type="text/css">
  .text-green {
    color: greenyellow;
  }

  .text-blue {
    color: deepskyblue;
  }

  .text-yellow {
    color: yellow;
  }

  .text-red {
    color: red;
  }
</style>
<style>
  .table {
    margin-top: 2px;
    border: 1px solid #dddddd;
  }

  .table>thead>tr>th,
  .table>tbody>tr>th,
  .table>tfoot>tr>th,
  .table>thead>tr>td,
  .table>tbody>tr>td,
  .table>tfoot>tr>td {
    border: 1px solid #dddddd;
    line-height: 1.42857;
    padding: 4px 8px;
    vertical-align: top;
  }

  .table>thead>tr {
    background-color: #f7f7f7;
  }

  .table>thead>tr>th {
    font-size: 12px;
  }

  .table>tfoot>tr {
    background-color: #f7f7f7;
  }

  .table>tfoot>tr>th {
    font-weight: 400;
    font-size: 11px;
    font-family: Verdana, Arial, sans-serif;
  }

  .table>tfoot .sep {
    width: 1px;
    height: 15px;
    background-color: #dddddd;
    display: inline-block;
    margin: 0px 5px;
    position: relative;
    top: 3px
  }

  .table .page {
    width: 50px;
    height: 20px;
    border: 1px solid #dddddd;
  }
</style>
<div class="wrapper wrapper-content" style="padding-bottom: 10px">

    <?php
    $cliente_almacen_style = ""; $cve_cliente = "";
    if($_SESSION['es_cliente'] == 1) 
    {
      $cliente_almacen_style = "style='display: none;'";
      $cve_cliente = $_SESSION['cve_cliente'];
    }

    $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }
    ?>
  <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
  <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

  <div class="row" <?php echo $cliente_almacen_style; ?>>
    <div id="alerta_retrasados" class="col-md-6" >
      <?php /* ?> @if(count($retrasados) > 0) <?php */ ?>
      <div style='margin-bottom:0;' class='alert alert-warning' role='alert' >Hay pedidos retrasados <a class='btn btn-primary btn-sm' onclick="retrasados()">Ver</a></div>
      <?php /* ?> @endif <?php */ ?>
    </div>
    <div id="alerta" class="col-md-6">
    </div>
  </div>
  <div class="wrapper wrapper-content">
    <h3>Monitoreo de Pedidos | Entregas</h3>
    <div class="row">
      <div class="col-md-12">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
            <div class="row">
              <div class="col-md-12" style="padding-left: 20px;  padding-right: 20px;">
                <div class="form-group col-md-3" style="padding-right: 15px;">
                  <label>Pedido | Cliente | Folio Embarque</label>
                  <div class="form-group" >
                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                  </div>
                </div>

                <div class="form-group col-md-2">
                  <label>Fecha inicial</label>
                  <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input id="txt-fecha-inicio"  class="form-control" style="width:100%">
                  </div>
                </div>
                <div class="form-group col-md-2">
                  <label>Fecha final</label>
                  <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input id="txt-fecha-fin" type="text" class="form-control" style="width:100%">
                  </div>
                </div>

                <div class="form-group col-md-3" style="padding-right: 15px;">
                  <label>Status de entrega</label>
                  <div class="form-group">
                    <select class="chosen-select form-control" id="txt-status-entrega">
                      <option value="">Seleccione status</option>
                      <option value="A">Listo por asignar</option>
                      <option value="S">Surtiendo</option>
                      <option value="L">Pendiente de auditar</option>
                      <option value="R">Auditando</option>
                      <option value="P">Pendiente de empaque</option>
                      <option value="M">Empacando</option>
                      <option value="C">Pendiente de embarque</option>
                      <option value="E">Embarcando</option>
                      <option value="T">Enviado</option>
                      <option value="F" selected>Entregado</option>
                      <option value="K">Cancelado</option>
                    </select>
                  </div>
                </div>

                <div class="form-group col-md-3" style="padding-right: 15px;">
                  <label>Mesa de Revisión</label>
                  <div class="form-group">
                    <select class="chosen-select form-control" id="mesas-revision">
                      <option value="">Seleccione Mesa</option>
                      <option value="todas">Todas las Mesas</option>
<?php 
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$cve_almacen = $_SESSION['cve_almacen'];
$mesas_revision_sql = mysqli_query($conn, "SELECT * FROM t_ubicaciones_revision WHERE cve_almac = '$cve_almacen'");

?>                        <?php while($mesa = mysqli_fetch_assoc($mesas_revision_sql)){ ?>
                            <option value="<?php echo $mesa['cve_ubicacion']; ?>"><?php echo "(".$mesa['cve_ubicacion'].") ".$mesa['descripcion']; ?></option>
                        <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group col-md-3" style="padding-right: 15px; margin-top: 23px;">
                  <div class="col-md-3">
                    <div class="input-group-btn">
                      <button onclick="filtroAvanzado()" type="button" class="btn btn-primary" style="width: 120%;">
                          <span class="fa fa-search"></span> Buscar     
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              <div class="row">
                <div class="col-md-3" id="form-almacen" style="padding-left: 30px;">
                  <div class="row">
                    <div class="form-group col-md-12">
                      <label for="status">Status:</label>
                      <select class="chosen-select form-control" id="status" name="status">
                        <option value="0" selected>Seleccione status</option>
                        @foreach($status as $value)
                          <option value="{{ $value->status }}">{{ $value->descripcion }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
          </div>
        </div>
        <div class="ibox-content">
          <div class="table-responsive">
            <table id="dt-detalles" class="table" style="table-layout: auto;width: 100%;">
              <thead>
                <tr>
<?php 
/*
?>
                  <th scope="col" style="text-align: center; width: 200px !important; min-width: 200px !important;">Fecha de Factura / Entrega</th>
                  <th scope="col" style="text-align: center; width: 100px !important; min-width: 100px !important;"># Documento</th>
                  <th scope="col" style="text-align: center; width: 100px !important; min-width: 100px !important;">C.P</th>
                  <th scope="col" style="text-align: center; width: 300px !important; min-width: 300px !important;">Calle / Num Destino</th>
                  <th scope="col" style="text-align: center; width: 250px !important; min-width: 250px !important;">Colonia Destino</th>
                  <th scope="col" style="text-align: center; width: 250px !important; min-width: 250px !important;">Ciudad o Municipio de Envío</th>
                  <th scope="col" style="text-align: center; width: 200px !important; min-width: 200px !important;">Estado de Envío</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 180px !important;">Status</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Asignado</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 180px !important;">Cancelado</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 180px !important;">Surtido</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Validado</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Empacado</th>
                  <th scope="col" style="text-align: center; width: 250px !important; min-width: 250px !important;">Fecha y Hora de Surtido</th>
                  <th scope="col" style="text-align: center; width: 250px !important; min-width: 250px !important;">Fecha y Hora de Validación</th>
                  <th scope="col" style="text-align: center; width: 250px !important; min-width: 250px !important;">Fecha y Hora de Empaque</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Guía</th>
                  <th scope="col" style="text-align: center; width: 100px !important; min-width: 100px !important;">Almacén</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Tránsito</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Entregado</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Días de Tránsito</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Fecha de Recepción</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 150px !important;">Persona de Recepción</th>
<?php 
*/
?>
                  <th scope="col" style="text-align: center; width: 200px !important; min-width: 150px !important;">Acciones</th>
                  <th scope="col" style="text-align: center; width: 200px !important; min-width: 150px !important;">Folio</th>
                  <th scope="col" style="text-align: center; width: 200px !important; min-width: 150px !important;">OC Cliente</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 180px !important;">Cliente</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 180px !important;">Clave Destinatario</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 250px !important;">Destinatario</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 100px !important;">Fecha Pedido</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 100px !important;">Fecha/Hora Surtido</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 100px !important;">Mesa Revision</th>
                  <th scope="col" style="text-align: center; width: 180px !important; min-width: 100px !important;">Fecha Compromiso</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Fecha Entrega</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Estado</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Cumplimiento</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">OTIF</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 250px !important;">Empresa</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Transporte</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Folio Embarque</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Guia Embarque</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Fecha Envío | Entrega</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Firma Recepción</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Dias Transito</th>
                  <th scope="col" style="text-align: center; width: 150px !important; min-width: 100px !important;">Dias Retraso</th>
                </tr>
              </thead>
              <tbody id="tbody-info"></tbody>
              <tfoot>
                <tr>
                  <th colspan="22">
                    Página
                    <input class="page" type="text" value="1" style="text-align:center" /> de <span class="total_pages">0</span>
                    <div class="sep"></div>
                      <select class="count">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="70">70</option>
                        <option value="100">100</option>
                      </select>
                    <div class="sep"></div>
                    Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                  </th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="col-md-3">
          <div class="input-group-btn" >
            <button id="exportExcel" class="btn btn-primary">
              <i class="fa fa-file-excel-o"></i>
              Exportar Visibles a Excel
            </button>
          </div>
        </div>
        <div class="col-md-3">
          <div class="input-group-btn" >
            <button id="exportAllExcel" class="btn btn-primary">
            <!--a href="javascript:exportar();" target="_blank" class="btn btn-primary"-->
              <i class="fa fa-file-excel-o"></i>
              Exportar Todos los Registros a Excel
            <!--/a-->
            </button>
          </div>
        </div>
        <?php 
        /*
        ?>
        <div class="col-md-3">
          <div class="input-group-btn" >
            <!--<button id="exportAllExcel" class="btn btn-primary">-->
            <a href="javascript:printPDFMonitoreo();" target="_blank" class="btn btn-primary">
              <i class="fa fa-file-pdf-o"></i>
              Exportar Reporte PDF
            </a>
            <!--</button>-->
          </div>
        </div>
        <?php 
        */
        ?>

        <div class="col-md-3">
            <a href="#" onclick="GenerarReporte()" id="reporte_monitoreo" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> Exportar Reporte PDF</a>
        </div>


      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="coModal" role="dialog" style="overflow-x: scroll;">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalle del Folio <span id="folio_mon"></span></h4>
                </div>
                <div class="modal-body">
                    <div><b>Dirección: </b><span id="direccion_mon"></span></div>
                    <br>
                    <div><b>Guías de Embarque: </b><span id="guias_mon"></span></div>
                    <br><br>
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




<div id="inner_download"></div>
</div>

<script type="text/javascript">


    $(function($) {
        var grid_selector2 = "#grid-table2";
        var pager_selector2 = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function () {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });


        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector2).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector2).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector2).jqGrid({
            url:'/api/administradorpedidos/lista/index.php',
            datatype: "local",
            shrinkToFit: false,
            width: 'auto',
            height: 250,
            postData: {
                criterio: ''
            },
            mtype: 'POST',
            colNames:['Folio', 'Clave de Producto','Nombre de Producto',"Lote|Serie", "Caducidad", "Cantidad Requerida"],
            colModel:[
                {name:'Folio_Pro',index:'Folio_Pro', width: 100, editable:false, sortable:false},
                {name:'Cve_Articulo',index:'Cve_Articulo', width: 150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion', width: 200, editable:false, sortable:false},
                {name:'Cve_Lote',index:'Cve_Lote', width: 100, editable:false, sortable:false},
                {name:'Caducidad',index:'Caducidad', width: 100, editable:false, sortable:false},
                {name:'Cantidad',index:'Cantidad', align: 'right',editable:false, sortable:false, resizable: false}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector2,
            sortname: 'Folio_Pro',
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

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector2).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });


    });


  function alertarefresh() {
    $(function($) {
      var html = "x";
      $.ajax({
        url: '/api/correcciondir/lista/index.php',
        dataType: 'json',
        type: 'POST'
      }).done(function(data) {
        //console.log(data["rows"].length);
        if (data["rows"].length >= 1)
          html = "<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay " + data["rows"].length + " pedidos sin Guias generadas <a class='btn btn-primary btn-sm' href='http://wms.nikken.assistpro-adl.com/correcciondir/lists'>Ver</a></div>";
        else
          html = "";
        $("#alerta").html(html);
      });
    });
  }

  function retrasados() {
    $("#txtCriterio").val("");
    $("#status").val("");
    $("#txt-fecha-inicio").val("");
    $("#txt-fecha-fin").val("");
    $("#txt-status-entrega").val("");
    $("#mesas-revision").val("");
    $("#form-almacen").hide();

    filtroAvanzado()
  }

  //alertarefresh();
  //var i_alerta = setInterval(alertarefresh, 20000);
  $("#form-almacen").hide();

  $('.date').datetimepicker({
    locale: 'es',
    format: 'DD-MM-YYYY',
    useCurrent: false
  });

  $(function($) {
    filtroAvanzado();
    var grid_selector = "#dt-detallres";
    var pager_selector = "#grid-pager";


    //resize to fit page size
    $(window).on('resize.jqGrid', function() {
      $(grid_selector).jqGrid('setGridWidth', $(".jqGrid_wrappert").width() - 60);
    })
    //resize on sidebar collapse/expand
    var parent_column = $(grid_selector).closest('[class*="col-"]');
    $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
      if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
        //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
        setTimeout(function() {
          $(grid_selector).jqGrid('setGridWidth', parent_column.width());
        }, 0);
      }
    })
    var lastsel;
/*
      colModel: [
        {label: 'Fecha de Factura / Entrega',name: 'fecha_factura',index: 'fecha_factura',editable: false,sortable: false,align: 'center'}, 
        {label: '# Documento',name: 'documento',index: 'documento',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'C.P',name: 'cp',index: 'cp',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Calle / Num Destino',name: 'calle_destino',index: 'calle_destino',editable: false,sortable: false,width: 250,align: 'left'}, 
        {label: 'Colonia Destino',name: 'colonia_destino',index: 'colonia_destino',editable: false,sortable: false,width: 250,align: 'center'}, 
        {label: 'Ciudad o Municipio de Envío',name: 'ciudad_envio',index: 'ciudad_envio',editable: false,sortable: false,width: 250,align: 'center'}, 
        {label: 'Estado de Envío',name: 'estado_envio',index: 'estado_envio',editable: false,sortable: false,width: 250,align: 'center'}, 
        {label: 'Status',name: 'status',index: 'status',editable: false,sortable: false,align: 'center'}, 
        {label: 'Asignado',name: 'cancelado',index: 'cancelado',editable: false,sortable: false,align: 'center'}, 
        {label: 'Cancelado',name: 'cancelado',index: 'cancelado',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Surtido',name: 'surtido',index: 'surtido',editable: false,sortable: false,align: 'center'}, 
        {label: 'Validado',name: 'validado',index: 'validado',editable: false,sortable: false,align: 'center'}, 
        {label: 'Empacado',name: 'empacado',index: 'empacado',editable: false,sortable: false,align: 'center'}, 
        {label: 'Fecha y Hora de Surtido',name: 'fecha_surtido',index: 'fecha_surtido',width: 200,editable: false,sortable: false,align: 'center'}, 
        {label: 'Fecha y Hora de Validación',name: 'fecha_validacion',index: 'fecha_validacion',width: 200,editable: false,sortable: false,align: 'center'}, 
        {label: 'Fecha y Hora de Empaque',name: 'fecha_empaque',index: 'fecha_empaque',width: 200,editable: false,sortable: false,align: 'center'}, 
        {label: 'Guía',name: 'guia',index: 'guia',editable: false,sortable: false,width: 210,align: 'center'}, 
        {label: 'Almacén',name: 'almacen',index: 'almacen',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Tránsito',name: 'transito',index: 'transito',editable: false,sortable: false,align: 'center'}, 
        {label: 'Entregado',name: 'entregado',index: 'entregado',editable: false,sortable: false,align: 'center'}, 
        {label: 'Días de Tránsito',name: 'dias_transito',index: 'dias_transito',editable: false,sortable: false,align: 'center'}, 
        {label: 'Fecha de Recepción',name: 'fecha_recepcion',index: 'fecha_recepcion',editable: false,sortable: false,align: 'center'}, 
        {label: 'Persona de Recepción',name: 'persona_recepcion',index: 'persona_recepcion',editable: false,sortable: false,align: 'center'}
      ],
*/
    $(grid_selector).jqGrid({
      url: '/api/v2/monitoreo-de-entrega/paginate',
      datatype: 'json',
      mtype: 'GET',
      postData: {
        //action: 'getDeliverys',
        search: $("#txtCriterio").val(),
        status: $("#status").val(),
      },
      shrinkToFit: true,
      height: 'auto',
      width: null,
      colModel: [
        {label: 'Acciones', name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
        {label: 'Folio',name: 'PO',index: 'PO',editable: false,sortable: false, width: 150}, 
        {label: 'Cliente',name: 'Clave_Cliente',index: 'Clave_Cliente',editable: false,sortable: false,width: 180,align: 'left'}, 
        {label: 'Clave Destinatario',name: 'Clave_Destinatario',index: 'Clave_Destinatario',editable: false,sortable: false,width: 180,align: 'left'}, 
        {label: 'Razon Social Destinatario',name: 'Razon_Social_Destinatario',index: 'Razon_Social_Destinatario',editable: false,sortable: false,width: 250,align: 'left'}, 
        {label: 'Fecha Pedido',name: 'Fecha_Pedido',index: 'Fecha_Pedido',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Fecha Compromiso',name: 'Fecha_Compromiso',index: 'Fecha_Compromiso',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Estado',name: 'Estado',index: 'Estado',editable: false,sortable: false,width: 100,align: 'left'}, 
        {label: 'Cumplimiento',name: 'Cumplimiento',index: 'Cumplimiento',editable: false,sortable: false,width: 100,align: 'center'}, 
        {label: 'Fecha Entrega',name: 'Fecha_Entrega',index: 'Fecha_Entrega',editable: false,sortable: false,align: 'center', width: 100}, 
        {label: 'Firma Recepción',name: 'cve_usuario',index: 'cve_usuario',editable: false,sortable: false,align: 'center', width: 100},
        {label: 'Destinatario',name: 'Destinatario',index: 'Destinatario',editable: false,sortable: false,align: 'center', width: 100, hidden: true},
        {label: 'Proveedor',name: 'cve_proveedor',index: 'cve_proveedor',editable: false,sortable: false,align: 'left', width: 250},
        {label: 'Transporte',name: 'Transporte',index: 'Transporte',editable: false,sortable: false,width: 100,align: 'left'}, 
        {label: 'Folio Embarque',name: 'Folio_Embarque',index: 'Folio_Embarque',editable: false,sortable: false,width: 100,align: 'left'}, 
        {label: 'Guia Embarque',name: 'Guia_Embarque',index: 'Guia_Embarque',editable: false,sortable: false,align: 'left', width:100}, 
        {label: 'Fecha Envío',name: 'Fecha_Envio',index: 'Fecha_Envio',editable: false,sortable: false,align: 'center', width:100}, 
        {label: 'Dias Transito',name: 'Dias_Transito',index: 'Dias_Transito',editable: false,sortable: false,align: 'center', width: 100}, 
        {label: 'Dias Retraso',name: 'Dias_Retraso',index: 'Dias_Retraso',editable: false,sortable: false,align: 'center', width: 100}, 
      ],
      rowNum: 10,
      loadonce: true,
      viewrecords: true,
      rowList: [10, 20, 30],
      pager: pager_selector,
      viewrecords: true,
      gridComplete: function() {}
    });

/*
    // Setup buttons
    $("#dt-detallres").jqGrid('navGrid', '#grid-pager', {
      edit: false,
      add: false,
      del: false,
      search: false
    }, {
      height: 200,
      reloadAfterSubmit: true
    });


    $(window).triggerHandler('resize.jqGrid');

*/
        function imageFormat( cellvalue, options, rowObject ){
/*
            var serie = rowObject[1];
            var correl = rowObject[5];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddencve_tipcia").val(serie);
*/
            var html = '';
            html += '<a href="#" onclick="detalle(\''+''+'\')"><i class="fa fa-search" title="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="reporte(\''+''+'\')"><i class="fa fa-file-pdf-o" title="Reporte"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;

        }


    $(document).one('ajaxloadstart.page', function(e) {
      $(grid_selector).jqGrid('GridUnload');
      $('.ui-jqdialog').remove();
    });
  });



  $("#exportExcel").on("click", function() {
    console.log("Exportando a excel");
    fnExcelReport();
  });
  
  $("#exportAllExcel").on("click", function() {
    console.log("Exportando todo a excel");
    exportar();
  });
  
  function fecha()
  {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();
    if(dd<10) { dd = '0'+dd } 
    if(mm<10) { mm = '0'+mm } 
    return dd + '-' + mm + '-' + yyyy;
  }
  
  "use strict";jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));
  
  function fnExcelReport()
  {
    var tab_text="<table id='download_table' border='2px'><tr bgcolor='#87AFC6'>";
    var textRange; var j=0;
    tab = document.getElementById('dt-detalles'); // id of table

    for(j = 0 ; j < tab.rows.length-1 ; j++) 
    {     
        tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
    }

    tab_text=tab_text+"</table>";
    tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
    tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
    tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

    $("#inner_download").html(tab_text);

    $("#download_table tr").each(function() {
    });

    tab_text = $("#inner_download").html();

    $("#inner_download").html('');

    var filename = "entregas-"+fecha()+ ".xls";

    uri = 'data:application/vnd.ms-excel;base64,' + $.base64.encode(tab_text);
    console.log(uri);
    var link = document.createElement("a");    
    link.href = uri;
    link.style = "visibility:hidden";
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
/*
  function detalle(titulo, destinatario, guias_embarque)
  {
      var guias = guias_embarque.replace(",", "\n");
      var datos = destinatario+"\n\n\n"+"Guías de Embarque"+"\n\n"+guias;

      swal({
            title: titulo,
            text: datos,
            type: "info"
      });
  }
*/

    function detalle(folio, destinatario, guias_embarque) {

        $("#folio_mon").text(folio);
        $("#direccion_mon").text(destinatario);
        $("#guias_mon").text(guias_embarque)

        loadDetalle(folio);
        $modal0 = $("#coModal");
        $modal0.modal('show');
    }

    function loadDetalle(folio) {

        $('#grid-table2').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                action: 'detalle_monitoreo',
                Folio_Pro: folio
            }, datatype: 'json'})
            .trigger('reloadGrid',[{current:true}]);
    }


  function filtroAvanzado() {
    $.ajax({
      type: "GET",
      dataType: "JSON",
      url: '/api/v2/monitoreo-de-entrega/paginate',
      data: {
        search: $("#txtCriterio").val(),
        status: $("#status").val(),
        action: 'getDeliverys',
        page: $('#dt-detalles .page').val(),
        rows: $('#dt-detalles .count').val(),
        cve_cliente: $("#cve_cliente").val(),
        cve_proveedor: $("#cve_proveedor").val(),
        fecha_inicio: $("#txt-fecha-inicio").val(),
        fecha_fin: $("#txt-fecha-fin").val(),
        mesas_revision: $("#mesas-revision").val(),
        id_almacen: <?php echo $_SESSION['id_almacen']; ?>,
        status_entrega: $("#txt-status-entrega").val()
      },
      success: function(data) {
        console.log("SUCCESS = ", data);
        if (data.status == 200) {

          console.log("data.total_pages = ", data.total_pages);
          console.log("data.page = ", data.page);
          console.log("data.from = ", data.from);
          console.log("data.to = ", data.to);
          console.log("data.total = ", data.total);

          $('#dt-detalles .total_pages').text(data.total_pages);
          $('#dt-detalles .page').text(data.page);

          $('#dt-detalles .from').text(data.from);
          $('#dt-detalles .to').text(data.to);
          $('#dt-detalles .total').text(data.total);

          var row = '',
            i = 0;

          var html = '';
          $.each(data.data, function(index, item) {
            i++;
            html = '';
            html += '<a href="#" onclick="detalle(\''+item.PO+'\', \''+item.Destinatario+'\', \''+item.Guias_Embarques+'\')"><i class="fa fa-search" title="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //html += '<a href="#" onclick="PDFMonitoreo(\''+''+'\')"><i class="fa fa-file-pdf-o" title="Reporte"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //if($("#cve_cliente").val() == "" || $("#cve_cliente").val() == item.Clave_Cliente)
            row += '<tr>' +
              '<td align="center">' + html + '</td>' +
              '<td align="left">' + item.PO + '</td>' +
              '<td align="left">' + item.oc_cliente + '</td>' +
              '<td align="left">' + item.Clave_Cliente + '</td>' +
              '<td align="left">' + item.Clave_Destinatario + '</td>' +
              '<td align="left">' + item.Razon_Social_Destinatario + '</td>' +
              '<td align="center">'   + item.Fecha_Pedido + '</td>' +
              '<td align="center">' + item.fecha_hora_surtido + '</td>' +
              '<td align="center">' + item.mesa_revision + '</td>' +
              '<td align="center">' + item.Fecha_Compromiso + '</td>' +
              '<td align="center">' + item.Fecha_Entrega + '</td>' +
              '<td align="left">' + item.Estado + '</td>' +
              '<td align="center">' + item.Cumplimiento + '</td>' +
              '<td align="center">' + item.OTIF + '</td>' +
              '<td align="left">' + item.cve_proveedor + '</td>' +
              '<td align="left">' + item.Transporte + '</td>' +
              '<td align="left">' + item.Folio_Embarque + '</td>' +
              '<td align="left">' + item.Guia_Embarque + '</td>' +
              '<td align="center">' + item.Fecha_Envio + '</td>' +
              '<td align="left">' + item.cve_usuario + '</td>' +
              '<td align="center">' + item.Dias_Transito + '</td>' +
              '<td align="center">' + item.Dias_Retraso + '</td>' +
              '</tr>';
          });

          $('#dt-detalles tbody').html(row);

        } else {
          swal({
            title: "Error",
            text: "No se pudo planificar el inventario",
            type: "error"
          });
        }
      }, error: function(data){ 
          console.log("ERROR = ", data);
      }
    });
  }

  $('#dt-detalles .page').keypress(function(e) {
    if (e.which == 13) {
      filtroAvanzado()
    }
  });

  $('#dt-detalles .count').change(function() {
    filtroAvanzado()
  });

  $('#txt-status-entrega').change(function() {
    if ($('#txt-status-entrega').val() == 1) {
      console.log($('#txt-status-entrega').val());
      $("#form-almacen").show();
    } else {
      $("#form-almacen").hide();
    }
  });
  
  
  function exportar()
  {
    var parametros = "";
    parametros += "search="  + $("#txtCriterio").val();
    parametros += "&status=" + $("#status").val();
    parametros += "&page=" + $("#dt-detalles .page").val();
    parametros += "&rows=" + $("#dt-detalles .count").val();
    parametros += "&fecha_inicio=" + $("#txt-fecha-inicio").val();
    parametros += "&fecha_fin=" + $("#txt-fecha-fin").val();
    parametros += "&status_entrega=" + $("#txt-status-entrega").val();
    parametros += "&mesas_revision=" + $("#mesas-revision").val();
    
    window.open("../api/v2/monitoreo/exportar?"+parametros);
  }


        function GenerarReporte()
        {
          var search = $("#txtCriterio").val();
          var status = $("#status").val();
          var cve_cliente = $("#cve_cliente").val();
          var cve_proveedor = $("#cve_proveedor").val();
          var fecha_inicio = $("#txt-fecha-inicio").val();
          var fecha_fin = $("#txt-fecha-fin").val();
          var status_entrega = $("#txt-status-entrega").val();
          var mesas_revision = $("#mesas-revision").val();

            if(search == '' && status == '' && fecha_inicio == '' && fecha_fin == '' && status_entrega == '' && mesa_revision == '')
            {
                swal("Filtros Vacíos", "Debe Seleccionar algún filtro para generar el Reporte", "error");
                return;
            }
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

            //$("#reporte_monitoreo").attr("href", "/api/koolreport/excel/kardex/export.php?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF);
            $("#reporte_monitoreo").attr("target","_blank");
            $("#reporte_monitoreo").attr("href", "/api/koolreport/export/reportes/pedidos/monitoreo?search="+search+"&status="+status+"&cve_cliente="+cve_cliente+"&cve_proveedor="+cve_proveedor+"&fecha_inicio="+fecha_inicio+"&fecha_fin="+fecha_fin+"&status_entrega="+status_entrega+"&cve_cia="+cve_cia+"&mesas_revision="+mesas_revision);

            setTimeout(function(){$("#reporte_monitoreo").attr("target","");}, 2000);

        }

    function printPDFMonitoreo()
    {
        var search         = $("#txtCriterio").val(),
            fecha_inicio   = $("#txt-fecha-inicio").val(),
            fecha_fin      = $("#txt-fecha-fin").val(),
            mesas_revision = $("#mesas-revision").val(),
            status_entrega = $("#txt-status-entrega").val();

        var folio = id,
        cia = <?php echo $_SESSION['cve_cia'] ?>,
        form = document.createElement('form'),
        input_nofooter = document.createElement('input'),
        input_folio = document.createElement('input'),
        input_pedidos = document.createElement('input'),
        input_cia = document.createElement('input');

        form.setAttribute('method', 'post');
        form.setAttribute('action', '/monitoreo/pdf/exportar');
        form.setAttribute('target', '_blank');

        input_nofooter.setAttribute('name', 'nofooternoheader');
        input_nofooter.setAttribute('value', '1');

        input_folio.setAttribute('name', 'folio');
        input_folio.setAttribute('value', folio);
        input_pedidos.setAttribute('name', 'folio_pedidos');
        input_pedidos.setAttribute('value', folio_pedidos);
        input_cia.setAttribute('name', 'cia');
        input_cia.setAttribute('value', <?php echo $_SESSION['cve_cia'] ?>);
        

        form.appendChild(input_nofooter);
        form.appendChild(input_folio);
        form.appendChild(input_pedidos);
        form.appendChild(input_cia);

        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }

</script>