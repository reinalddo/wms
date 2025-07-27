<?php
$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();
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
 <h3>Reporte de Existencia Concentrado</h3>
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
                    </div>
                    <div class="row" style="margin-top:15px">
                    <div class="col-md-6">
                        <label>Total de Productos</label>
                        <input id="totalproductos" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-6">
                        <label>Total de Unidades</label>
                        <input id="totalunidades" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                    </div>
                </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="concentrado"  class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Existencia</th>
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
    
    
    function buscar() 
    {
        console.log("buscar");
        table();
        console.log("buscar2");
    }
  
    function totales()
    {
        $.ajax({
            url:"/api/reportes/lista/concentradoexistencia.php",
            type: "POST",
            data: {
                almacen: $("#almacen").val(),
                action:"totales"
            },
            success: function(data){
                console.log(data.data[0]);
                $("#totalproductos").val(data.data[0].productos);
                $("#totalunidades").val(data.data[0].unidades);
            }
        })
    }
  
    function table()
    {
        $('#myTable').DataTable().destroy();
        $('#concentrado').DataTable({
            "processing": true,
            "dom": 'Bfrtip',
            buttons: [
                {extend: 'csvHtml5',title: 'Entradas'},
                {extend: 'excelHtml5',title: 'Entradas'},
    			      {extend: 'pdfHtml5',title: 'Entradas'}
			      ],
            "responsive": true, 
            "serverSide": true,
            "pagingType": "full_numbers",
		        "language": 
            {
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
            "bDestroy": true,
            "columns": [
                { "data": "proveedor" },
                { "data": "articulo" },
                { "data": "nombre" },
                { "data": "existencia" }
            ],
            "columnDefs": [
                {"className": "dt-right", "targets": [3]}
            ],
            "ajax": {
                "url": "/api/reportes/lista/concentradoexistencia.php",
                "type": "GET",
                "data": {
                    "almacen" : $("#almacen").val(),
                }
            }
        });
        totales();
    }
  
    $(document).ready(function(){
       function almacenPrede()
       { 
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
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
       
        table();
	
        $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $('input[type=search]').attr('class', 'form-control input-sm');
	
	
	$('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e){
        var title = $('#list h3:first-of-type').text();
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"concentradoexistencia"
                
            },
            success: function(data, textStatus, xhr){
                var content_wrapper = document.createElement('div');
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var tfoot = document.createElement('tfoot');
                var head_content = '<tr><th style="border: 1px solid #ccc">Proveedor</th>'+
                                       '<th style="border: 1px solid #ccc">Artículo</th>'+
                                       '<th style="border: 1px solid #ccc">Nombre</th>    '+
                                       '<th style="border: 1px solid #ccc">Existencia</th>'+
									'</tr>';
                var body_content = '';
                var data = JSON.parse(data);
                console.log(data);
                data.articulos.forEach(function(item, index){
                    body_content += '<tr>'+
                                '<td style="border: 1px solid #ccc">'+item.proveedor+'</td> '+
                                '<td style="border: 1px solid #ccc">'+item.articulo+'</td>    '+
                                '<td style="border: 1px solid #ccc">'+item.nombre+'</td>        '+
                                '<td style="border: 1px solid #ccc">'+item.existencia+'</td>'+
							'</tr>  ';                                                         

                });

                var foot_content = '<tr><td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td><td>'+data.cantidad+'</td></tr>';

                tbody.innerHTML = body_content;
                thead.innerHTML = head_content;
                tfoot.innerHTML = foot_content;
                table.appendChild(thead);
                table.appendChild(tfoot);
                table.appendChild(tbody);
                content_wrapper.appendChild(table);
                content = content_wrapper.innerHTML;

                /*Creando formulario para ser enviado*/

                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", "/api/reportes/generar/pdf.php");
                form.setAttribute("target", "_blank");

                var input_content = document.createElement('input');
                var input_title = document.createElement('input');
                var input_cia = document.createElement('input');
                input_content.setAttribute('type', 'hidden');
                input_title.setAttribute('type', 'hidden');
                input_cia.setAttribute('type', 'hidden');
                input_content.setAttribute('name', 'content');
                input_title.setAttribute('name', 'title');
                input_cia.setAttribute('name', 'cia');
                input_content.setAttribute('value', content);
                input_title.setAttribute('value', title);
                input_cia.setAttribute('value', cia);

                form.appendChild(input_content);
                form.appendChild(input_title);
                form.appendChild(input_cia);

                document.body.appendChild(form);
                form.submit();
            }
        });

    });
	
	
	
    });
	
	
</script>