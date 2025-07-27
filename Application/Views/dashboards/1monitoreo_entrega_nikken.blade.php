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
    
    #grid-table tbody tr td {
        /*text-align: center;*/
    }
</style>

<div class="wrapper wrapper-content" style="padding-bottom: 10px">
  <div id="alerta">
  </div>
  <div id="alerta_dias_transito">
  </div>
    <div class="wrapper wrapper-content">
        <h3>Monitoreo de Pedidos</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="chosen-select form-control" id="status" name="status">
                                        <option value="">Seleccione status</option>
                                        @foreach($status as $value)
                                            <option value="{{ $value->status }}" {{($value->status == 'A')?"selected":""}}>{{ $value->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group" style="margin-top: 23px">
                                    <input type="text" class="form-control" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">                                        
                                        <button onclick="filtrar()" type="submit" class="btn btn-primary">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                    @if(count($retrasados) > 0)
                                    <div class="input-group-btn "> 
                                            <button id="btn-mostrar-retrasados" onclick="retrasados()" class="btn btn-warning">
                                                <i class="fa fa-exclamation-circle"></i>
                                                Retrasados
                                            </button>
                                        </div>
                                    @endif
                                    <div class="input-group-btn text-right"> 
                                        <button id="exportExcel" class="btn btn-primary">
                                            <i class="fa fa-file-excel-o"></i>
                                            Exportar a Excel
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-3">
                                <label>Fecha de inicio</label>
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="txt-fecha-inicio" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha final</label>
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="txt-fecha-fin" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>Status de entrega</label>

                                <div class="input-group">
                                    <select class="chosen-select form-control" id="txt-status-entrega">
                                        <option value="">Seleccione status</option>
                                        <option value="1">Pedido en almacén</option>
                                        <option value="2">Pedido en transito</option>
                                        <option value="3">Pedido entregado</option>
                                        <option value="4">Pedido atrasado</option>
                                    </select>

                                    <div class="input-group-btn">                                        
                                        <button onclick="filtroAvanzado()" type="button" class="btn btn-primary">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>                                                                                
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
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-info"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="22">
                                                Página 
                                                <input class="page" type="text" value="1" style="text-align:center"/> 
                                                de <span class="total_pages">0</span>
                                                <div class="sep"></div>
                                                <select class="count">
                                                    <option value="10">10</option>
                                                    <option value="20">30</option>
                                                    <option value="30">30</option>
                                                </select>
                                                <div class="sep"></div>
                                                Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                      
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="modal-pedidos-retrasados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Pedidos retrasados</h4>
            </div>
            <div class="modal-body text-center"> 
                <span class="fa fa-bell" style="font-size: 100px; color:#f0ad4e"></span>              
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white"  data-dismiss="modal">Cerrar</button>
                <button id="btn-retrasados" onclick="retrasados()" type="button" class="btn btn-primary">Ver pedidos retrasados</button> 
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-12">
        <div class="footer">
            <div>
                <strong>Copyright </strong> AssistPro ADL ® Todos los derechos reservados. &copy; 2017
            </div>
        </div>
    </div>
</div>

<style>

    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        background-color: #f7f7f7;
    }
    .table > thead > tr > th {
        font-size: 12px;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }

</style>

<script type="text/javascript">

  function alertarefresh()
  {
    $(function($)
    {
      var html="x";
      $.ajax({
        url: '/api/correcciondir/lista/index.php',
        dataType: 'json',
        type: 'POST'
      }).done(function(data){
        console.log(data["rows"].length);
        if(data["rows"].length >= 1)
          html = "<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay "+data["rows"].length+" pedidos sin Guias generadas <a class='btn btn-primary btn-sm' href='http://wms.nikken.assistpro-adl.com/correcciondir/lists'>Ver</a></div>";
        else
          html="";
        $("#alerta").html(html);
      });
    });
  }
  
  alertarefresh();
  var i_alerta = setInterval(alertarefresh,20000);
  
    $('.date').datetimepicker({
       locale: 'es',
       format: 'DD-MM-YYYY',
       useCurrent: false
    });


    @if(count($retrasados) > 0)
        $('#modal-pedidos-retrasados').modal('show');

        function retrasados(){
            $('#modal-pedidos-retrasados').modal('hide');
            filtrar()
        }

    @endif
  


    $(function($) {
        filtrar();
        var grid_selector = "#grid-table";
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

        $(grid_selector).jqGrid({
            url: '/api/v2/monitoreo-de-entrega/paginate',
            datatype: 'json',
            mtype: 'GET',
            postData: {
                action: 'getDeliverys',
                search: $("#txtCriterio").val(),
                status: $("#status").val(),
            },
            shrinkToFit: false,
            height: 'auto',
            width: null,
            colModel: [
                {
                    label: 'Fecha de Factura / Entrega',
                    name: 'fecha_factura',
                    index: 'fecha_factura',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: '# Documento',
                    name: 'documento',
                    index: 'documento',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'C.P',
                    name: 'cp',
                    index: 'cp',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'Calle / Num Destino',
                    name: 'calle_destino',
                    index: 'calle_destino',
                    editable: false,
                    sortable: false,
                    width: 250,
                    align: 'left'
                }, {
                    label: 'Colonia Destino',
                    name: 'colonia_destino',
                    index: 'colonia_destino',
                    editable: false,
                    sortable: false,
                    width: 250,
                    align: 'center'
                }, {
                    label: 'Ciudad o Municipio de Envío',
                    name: 'ciudad_envio',
                    index: 'ciudad_envio',
                    editable: false,
                    sortable: false,
                    width: 250,
                    align: 'center'
                }, {
                    label: 'Estado de Envío',
                    name: 'estado_envio',
                    index: 'estado_envio',
                    editable: false,
                    sortable: false,
                    width: 250,
                    align: 'center'
                }, {
                    label: 'Status',
                    name: 'status',
                    index: 'status',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Asignado',
                    name: 'cancelado',
                    index: 'cancelado',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Cancelado',
                    name: 'cancelado',
                    index: 'cancelado',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'Surtido',
                    name: 'surtido',
                    index: 'surtido',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Validado',
                    name: 'validado',
                    index: 'validado',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Empacado',
                    name: 'empacado',
                    index: 'empacado',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Fecha y Hora de Surtido',
                    name: 'fecha_surtido',
                    index: 'fecha_surtido',
                    width: 200,
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Fecha y Hora de Validación',
                    name: 'fecha_validacion',
                    index: 'fecha_validacion',
                    width: 200,
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Fecha y Hora de Empaque',
                    name: 'fecha_empaque',
                    index: 'fecha_empaque',
                    width: 200,
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Guía',
                    name: 'guia',
                    index: 'guia',
                    editable: false,
                    sortable: false,
                    width: 210,
                    align: 'center'
                }, {
                    label: 'Almacén',
                    name: 'almacen',
                    index: 'almacen',
                    editable: false,
                    sortable: false,
                    width: 100,
                    align: 'center'
                }, {
                    label: 'Tránsito',
                    name: 'transito',
                    index: 'transito',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Entregado',
                    name: 'entregado',
                    index: 'entregado',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Días de Tránsito',
                    name: 'dias_transito',
                    index: 'dias_transito',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Fecha de Recepción',
                    name: 'fecha_recepcion',
                    index: 'fecha_recepcion',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }, {
                    label: 'Persona de Recepción',
                    name: 'persona_recepcion',
                    index: 'persona_recepcion',
                    editable: false,
                    sortable: false,
                    align: 'center'
                }
            ],
            rowNum: 10,
            loadonce: true, 
            viewrecords: true,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
            gridComplete: function() {
            }
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


        $(window).triggerHandler('resize.jqGrid');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
        //$('select').chosen();
    });



    $("#exportExcel").on("click", function(){
        var date = new Date();
        var hora = date.getHours();
        var minutos = date.getMinutes();
        if (hora>12){
            hora = hora-12;
        }
        if (hora==0)
            hora = 12;
        if (minutos<=9)
            minutos="0"+minutos;

        var Fecha = date.getDate() +'-'+ date.getMonth() +'-'+ date.getFullYear() +' '+hora+':'+minutos;
                    
        $("#grid-table").jqGrid("exportToExcel",{
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : "Monitoreo de Entregas "+Fecha+".xls",
            maxlength : 40 // maxlength for visible string data
        })
    })

    function filtroAvanzado(){
        $.ajax({
            type: "GET",
            dataType: "JSON",
            url: '/api/v2/monitoreo-de-entrega/paginate',
            data: {
                search: $("#txtCriterio").val(),
                status: $("#status").val(),
                fecha_inicio: '',
                fecha_fin: '',
                status_entrega: '',
                avanzado : '0',
                action: 'getDeliverys',
                page : $('#dt-detalles .page').val(),
                rows : $('#dt-detalles .count').val(),
                fecha_inicio: $("#txt-fecha-inicio").val(),
                fecha_fin: $("#txt-fecha-fin").val(),
                status_entrega: $("#txt-status-entrega").val(),
                avanzado : '1',
            },
            success: function(data) {
                if (data.status == 200) {
                    $('#dt-detalles .total_pages').text(data.total_pages);
                    $('#dt-detalles .page').text(data.page);

                    $('#dt-detalles .from').text(data.from);
                    $('#dt-detalles .to').text(data.to);
                    $('#dt-detalles .total').text(data.total);

                    var row = '', i = 0;
                    var cont_pedidos = 0;
                    var html = "";
                   
                    $.each(data.data, function(index, item){
                        i++;
                        row += '<tr>'+
                                    '<td align="center">'+item.fecha_factura+'</td>'+
                                    '<td align="center">'+item.n_documento+'</td>'+
                                    '<td align="center">'+item.codigo_postal+'</td>'+
                                    '<td align="left">'+item.calle+'</td>'+
                                    '<td align="center">'+item.colonia+'</td>'+
                                    '<td align="center">'+item.ciudad+'</td>'+                                            
                                    '<td align="center">'+item.estado+'</td>'+
                                    '<td align="center">'+item.estatus+'</td>'+
                                    '<td align="center">'+item.asignado+'</td>'+
                                    '<td align="center">'+item.cancelado+'</td>'+
                                    '<td align="center">'+item.surtido+'</td>'+
                                    '<td align="center">'+item.reviso+'</td>'+
                                    '<td align="center">'+item.empacado+'</td>'+
                                    '<td align="center">'+item.fecha_surtido+'</td>'+
                                    '<td align="center">'+item.fecha_validacion+'</td>'+
                                    '<td align="center">'+item.fecha_empaque+'</td>'+
                                    '<td align="center">'+item.guia+'</td>'+
                                    '<td align="center">'+item.almacen+'</td>'+
                                    '<td align="center">'+item.transito+'</td>'+
                                    '<td align="center">'+item.entregado+'</td>'+
                                    '<td align="center">'+item.dias_en_transito+'</td>'+
                                    '<td align="center">'+item.fecha_recepcion+'</td>'+
                                    '<td align="center">'+item.persona_recepcion+'</td>'+
                                '</tr>';
                      if((item.dias_en_transito).split(" ")[0] >= 4 && item.entregado == '--'){
                        cont_pedidos += 1;
                      }
                      
                    });
                 
                    $('#dt-detalles tbody').html(row);
                     html= (cont_pedidos >= 1)?"<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay "+cont_pedidos+" pedido(s) retrasado(s) </div>":" ";
                    $('#alerta_dias_transito').html(html);
                } else {
                    swal({
                        title: "Error",
                        text: "No se pudo planificar el inventario",
                        type: "error"
                    });
                }
            }
        });
    }

    $('#dt-detalles .page').keypress(function(e) {
        if(e.which == 13) {
            filtrar()
        }
    });

    $('#dt-detalles .count').change(function() {       
        filtrar()        
    });

    function filtrar() {

        $.ajax({
            type: "GET",
            dataType: "JSON",
            url: '/api/v2/monitoreo-de-entrega/paginate',
            data: {
                search: $("#txtCriterio").val(),
                status: $("#status").val(),
                fecha_inicio: '',
                fecha_fin: '',
                status_entrega: '',
                avanzado : '0',
                action: 'getDeliverys',
                page : $('#dt-detalles .page').val(),
                rows : $('#dt-detalles .count').val(),
                fecha_inicio: $("#txt-fecha-inicio").val(),
                fecha_fin: $("#txt-fecha-fin").val(),
                status_entrega: $("#txt-status-entrega").val()
            },
            success: function(data) {
                if (data.status == 200) {
                    $('#dt-detalles .total_pages').text(data.total_pages);
                    $('#dt-detalles .page').text(data.page);

                    $('#dt-detalles .from').text(data.from);
                    $('#dt-detalles .to').text(data.to);
                    $('#dt-detalles .total').text(data.total);

                    var row = '', i = 0;
                    var cont_pedidos = 0;
                    var html = "";
                    var html2 = "";
                    $.each(data.data, function(index, item){
                        i++;
                        row += '<tr>'+
                                    '<td align="center">'+item.fecha_factura+'</td>'+
                                    '<td align="center">'+item.n_documento+'</td>'+
                                    '<td align="center">'+item.codigo_postal+'</td>'+
                                    '<td align="left">'+item.calle+'</td>'+
                                    '<td align="center">'+item.colonia+'</td>'+
                                    '<td align="center">'+item.ciudad+'</td>'+                                            
                                    '<td align="center">'+item.estado+'</td>'+
                                    '<td align="center">'+item.estatus+'</td>'+
                                    '<td align="center">'+item.asignado+'</td>'+
                                    '<td align="center">'+item.cancelado+'</td>'+
                                    '<td align="center">'+item.surtido+'</td>'+
                                    '<td align="center">'+item.reviso+'</td>'+
                                    '<td align="center">'+item.empacado+'</td>'+
                                    '<td align="center">'+item.fecha_surtido+'</td>'+
                                    '<td align="center">'+item.fecha_validacion+'</td>'+
                                    '<td align="center">'+item.fecha_empaque+'</td>'+
                                    '<td align="center">'+item.guia+'</td>'+
                                    '<td align="center">'+item.almacen+'</td>'+
                                    '<td align="center">'+item.transito+'</td>'+
                                    '<td align="center">'+item.entregado+'</td>'+
                                    '<td align="center">'+item.dias_en_transito+'</td>'+
                                    '<td align="center">'+item.fecha_recepcion+'</td>'+
                                    '<td align="center">'+item.persona_recepcion+'</td>'+
                                '</tr>';  
                      if((item.dias_en_transito).split(" ")[0] >= 4 && item.entregado == '--'){
                        cont_pedidos +=1;
                      }
                    });
                  
                    $('#dt-detalles tbody').html(row);
                    html= (cont_pedidos >= 1)?"<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay "+cont_pedidos+" pedido(s) retrasado(s) </div>":" ";
                    $('#alerta_dias_transito').html(html);
                } else {
                    swal({
                        title: "Error",
                        text: "No se pudo planificar el inventario",
                        type: "error"
                    });
                }
            }
        });

    }
    

</script>