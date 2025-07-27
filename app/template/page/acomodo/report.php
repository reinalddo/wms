<?php


?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
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
 <h3>Reporte Entradas</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">

                        </div>
                  
                    </div>
                </div>
                <div class="ibox-content">
				<div class="table-responsive">
				<table id="example"  class="table table-hover table-striped no-margin">
				<thead>
				<tr>
                <th>ENTRADA</th>
                <th>ALMACEN</th>
                <th>ORDEN DE ENTRADA</th>
                <th>PROVEEDOR</th>
                <th>FECHA ENTRADA</th>
                <th>DIO ENTRADA</th>
				<th>AUTORIZO</th>
				<th>ARTICULO</th>
				<th>CANTIDAD RECIBIDA</th>
            </tr>
        </thead>
       
    </table>
			</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Entrada</h4>
                </div>
                <div class="modal-body">
                   				<div class="tabbable" id="tabs-131708">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#panel-928563"  data-toggle="tab">Resumen</a>
					</li>
					<li >
						<a href="#panel-594076" id="avanzada" data-toggle="tab">Detalle</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="panel-928563">
						 <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
                        </div>
                    </div>
					</div>
					<div class="tab-pane" id="panel-594076">
						  <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table3"></table>
                            <div id="grid-pager3"></div>
                        </div>
                    </div>       	
					</div>
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

<script>
    $(document).ready(function(){
        $("#basic").select2();
			
            $('#example').DataTable( {
				    "processing": true,
					dom: 'Bfrtip',
         buttons: [
		  {
                extend: 'csvHtml5',
                title: 'Entradas'
            },
			
            {
                extend: 'excelHtml5',
                title: 'Entradas'
            },
			 {
                extend: 'pdfHtml5',
                title: 'Entradas'
            },
			
			
			],
		 responsive: true,
		 "pagingType": "full_numbers",
		    "language": {
                  "lengthMenu": "Mostrando _MENU_ registros",
                  "zeroRecords": "Sin Registros - :(!!",
                  "info": "Pagina _PAGE_ de _PAGES_",
                  "infoEmpty": "No Existen Registros",
                  "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                   "sSearch": "Filtrar:",
				   "sProcessing":   	"Cargando...",
				   
                  "oPaginate": {
                      "sNext": "Sig",
                      "sPrevious": "Ant",
					  "sLast": "Ultimo",
					  "sFirst":    	"Primero",
                  }
              },
        "ajax": {
            "url": "/api/adminentrada/update/index.php",
            "type": "POST",
			"data": {
				"action":"reporte"
				
			},
			 "columns": [
            { "data.": "entrada" },
            { "data": "almacen" },
            { "data": "orden_entrada" },
            { "data": "proveedor" },
            { "data": "fecha_entrada" },
			{ "data": "usuario_activo" },
			{ "data": "autorizado" },
            { "data": "articulo" },
			{ "data": "cantidad_recibida" }
        ],
		
		
        },
	
    } );
	
	$(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
	$(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
	$(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
	//$(".paginate_button").attr('class', 'btn btn-primary btn-sm');
	$('input[type=search]').attr('class', 'form-control input-sm');
	
	
	
    });
	
	
</script>