<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
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
<div class="modal fade" id="selectOption">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Imprimir etiquetas</h2>
            </div>
            <div class="modal-body">
                <form target="_blank" id="imprimir_etiqueta" method="post" action="/reportes/pdf/etiquetas">
                    <div class="form-group">
                        <label>Artículo</label>
                        <h3 style="font-weight: normal" id="producto_des"></h3>
                    </div>
                    <div class="form-group">
                        <label>Tipo de etiqueta</label>
                        <select class="form-control" name="etiqueta" required>
                            <option value="">Seleccione</option>
                            <option value="articulo">Artículo</option>
                            <option value="caja">Caja</option>
                            <option value="pallet">Pallet</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lotes</label>
                        <select class="form-control" name="lote" id="lotes" required></select>
                    </div>
                    <div class="form-group">
                        <label>Unidades por caja</label>
                        <input type="number" name="unidades_caja" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad de etiquetas</label>
                        <input type="number" name="numero_impresiones" class="form-control" value="1" min="1" required>
                    </div>
                    <input type="hidden" name="cve_articulo" value="0">
                    <input type="hidden" name="ordenp" value="0">
                    <input type="hidden" name="des_articulo" value="0">
                    <input type="hidden" name="barras2" value="0">
                    <input type="hidden" name="barras3" value="0">
                    <input type="hidden" name="consultar" value="2">
                    <input type="hidden" name="nofooternoheader" value="true">
                    <div style="text-align: right">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            Imprimir
                            <i class="fa fa-print-o"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Productos Compuestos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
				  <div class="row">
                    <div class="col-lg-4">
						<div class="form-group">
							<label for="email">Almacén</label>
							<select name="almacen" id="almacen" class="chosen-select form-control">
								<option value="">Seleccione Almacén</option>
								<?php foreach( $model_almacen AS $almacen ): ?>
									<?php if($almacen->Activo == 1):?>
									<option value="<?php echo $almacen->clave; ?>"><?php echo "($almacen->clave)"." ".$almacen->nombre; ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>		
                    </div>
					</div>					
                </div>
                <div class="ibox-content">
					<div class="row">
						<div class="col-lg-12">
							<div class="table-responsive">
								<table id="productos" class="table table-hover table-striped no-margin">
									<thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th>Código Pieza</th>
                                            <th>Código Caja</th>
                                            <th>Clave</th>
                                            <th>Imprimir</th> 
                                        </tr>
									</thead>
								</table>
							</div>
						</div>
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
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
   $('#almacen').on('change', function() {
        var tabla = $('#productos').DataTable( {
            "bPaginate": true,
            "bLengthChange": false,
            "iDisplayLength": 10,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "aaSorting": [],
            "bScrollAutoCss": true,
            "ajax": {
                "url": "/api/articulos/update/index.php",
                "data": {					
					"almacen": $("#almacen").val(),
					"action":"getArtCompuestos"
                }
            },
            "columns": [
                { "data": "articulo" }, 
                { "data": "barra_pieza" },
                { "data": "barra_caja" },
                { "data": "clave" }, 
                { "data": "print"} 
            ],
            "bDestroy": true,
            "bServerSide": true,
            "sScrollXInner": "110%",
            "bAutoWidth": true,
            "processing": true, //Feature control the processing indicator.
            'initComplete': function() {
                //Activando caja de búsqueda
                $('#productos_filter label input[type="search"]').addClass('form-control').unbind();
                $('#productos_filter label input[type="search"]').on('keyup', function(e) {
                    e.preventDefault();
                     if(e.keyCode == 13) {
                        tabla.search( this.value ).draw();
                    } 
                });
            },
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
            }
        } );
    });

    function selectOption(clave, articulo, num_multiplo, barra_pieza, barra_caja){
        $("#imprimir_etiqueta input[name='cve_articulo']").val(clave);
        $("#imprimir_etiqueta input[name='des_articulo']").val(articulo);
        $("#producto_des").html(`${articulo} ${clave}`);
        $("#imprimir_etiqueta input[name='unidades_caja']").val(num_multiplo);
        $("#imprimir_etiqueta input[name='barras2']").val(barra_pieza);
        $("#imprimir_etiqueta input[name='barras3']").val(barra_caja);
        $.ajax({
            url: '/api/lotes/lista/index.php',
            type: 'GET',
            dataType: 'json',
            data: `cve_articulo=${clave}`
        }).done(function(data){
            var options = '<option value="">Seleccione</option>'
            for(var i = 0; i < data.length; i++){
                options += `<option value="${data[i]}">${data[i]}</option>`;
            }
            $("#lotes").html(options);
            $('#selectOption').modal('toggle');
        })
    }	
    $('#lotes').on('change', function(e){
        var lote = e.target.value,
            clave = $("#imprimir_etiqueta input[name='cve_articulo']").val();
        $.ajax({
            url: '/api/ordenproduccion/lista/index.php',
            type: 'GET',
            dataType: 'json',
            data: `cve_articulo=${clave}&lote=${lote}`
        }).done(function(data){
            $("#imprimir_etiqueta input[name='ordenp']").val(data.ordenp)
        })
    });

</script>