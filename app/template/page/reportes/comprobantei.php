<?php


?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style>
.bt{
	
	    margin-right: 10px;
}

.btn-blue{
	
	background-color: blue !important;
    border-color: blue !important;
	color: white !important;
}

</style>
<div class="wrapper wrapper-content  animated " id="list">
 <h3>Comprobante de Ingreso</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Fecha de Entrada</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fecha" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <button style="margin-top: 25px;" class="btn btn-primary btn-sm" onclick="search()">Buscar</button>
                        </div>                  
                    </div>
                </div>
                <div class="ibox-content">
				    <div class="table-responsive">
				        <table id="example"  class="table table-hover table-striped no-margin">
            				<thead>
            				    <tr>
                                    <th>N° RECEPCION</th>
                                    <th>FECHA INGRESO</th>
                                    <th>PROTOCOLO</th>
                                    <th>N° PEDIMENTO</th>
                                    <th>MANIFIESTO</th>
                                    <th>N° DE PARTE</th>
                    				<th>DESCRIPCION</th>
                    				<th>CANTIDAD</th>
                    				<th>KG</th>
                    				<th>CLIENTE</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
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

<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script>
    var buttons = [{
            extend: 'excelHtml5',
            title: 'Comprobante de Ingreso',
            customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
            },
            {
            extend: 'pdfHtml5',
            title: 'Comprobante de Ingreso',
            orientation: 'landscape',
            download: 'open',
            customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
            }
        ];
$(document).ready(function(){
    $("#basic").select2();
    $('#data_1').datetimepicker({
        locale: 'es',
        format: 'DD-MM-YYYY',
        useCurrent: false
    });
	search();
});

function search(){
    $('#example').DataTable({
        "processing": true,
        dom: 'Bfrtip',
        buttons: buttons,
        "responsive": true,
        "serverSide": true,
        "bDestroy": true,
        "pageLength": 30,
        "pagingType": "full_numbers",
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros",
            "zeroRecords": "Sin Registros - :(!!",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No Existen Registros",
            "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
            "sSearch": "Filtrar:",
            "sProcessing":      "Cargando...",
            "oPaginate": {
              "sNext": "Sig",
              "sPrevious": "Ant",
              "sLast": "Ultimo",
              "sFirst":     "Primero",
            }
        },
        "columns": [
            { "data.": "numero_recepcion", "className": "text-right" },
            { "data": "fecha_ingreso" },
            { "data": "protocolo" },
            { "data": "numero_pedimiento"},
            { "data": "manifiesto" },
            { "data": "numero_parte" },
            { "data": "descripcion" },
            { "data": "cantidad", "className": "text-right" },
            { "data": "peso", "className": "text-right" },
            { "data": "empresa" }
        ],
        "ajax": {
            "url": "/api/reportes/lista/comprobantei.php",
            "type": "GET",
            "data": {
                "almacen": "<?php echo $_SESSION['cve_almacen']; ?>"
            }
        }
    });
    //"fecha": $("#fecha").val().length > 0 ? moment($("#fecha").val(), "DD-MM-YYYY").format("YYYY-MM-DD") : ""
    $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
    $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
    $('input[type=search]').attr('class', 'form-control input-sm');
}
	
</script>