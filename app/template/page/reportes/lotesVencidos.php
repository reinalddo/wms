<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
?>
<!--<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">-->
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
 <h3>Lotes Vencidos</h3>
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
                                        <option value="<?php echo $almacen->id; ?>"><?php echo"($almacen->clave) ". $almacen->nombre; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>                  
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="example"  class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>                              
                                    <th>CLAVE ARTICULO</th>
                                    <th>ARTÍCULO</th>
                                    <th>LOTE</th>
                                    <th>CADUCIDAD</th>
                                    <th>UBICACIÓN</th>
                                    <th>EXISTENCIA</th>
                                    <th>FECHA DE INGRESO</th>
                                    <th>PROVEEDOR</th>
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
<!--<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>-->
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
                    document.getElementById('almacen').value = data.codigo.id;
                    $('#almacen').trigger('change');
                    //search();
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });
    }
    almacenPrede();
    $("#basic").select2();          
    $('#example').DataTable({
        "processing": false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csvHtml5',
                title: 'Lotes Vencidos'
            },          
            {
                extend: 'excelHtml5',
                  title: 'Lotes Vencidos'
            },
             {
                extend: 'pdfHtml5',
                   title: 'Lotes Vencidos'
            }
        ],
        responsive: true, 
        serverSide: false,
        "pagingType": "full_numbers",
        "language": {
              "lengthMenu": "Mostrando _MENU_ registros",
              "zeroRecords": "Sin Registros - :(!!",
              "info": "Pagina _PAGE_ de _PAGES_",
              "infoEmpty": "No Existen Registros",
              "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
               "sSearch": "Filtrar:",
               "sProcessing":       "Cargando...",                 
              "oPaginate": {
                  "sNext": "Sig",
                  "sPrevious": "Ant",
                  "sLast": "Ultimo",
                  "sFirst":     "Primero",
              }
        }
    });
    $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
    $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
    $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
    $('input[type=search]').attr('class', 'form-control input-sm');
    $("#almacen").on("change", function(e){
        if(e.target.value.length > 0){
            $('#example').DataTable({
                "processing": true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: 'Lotes Vencidos'
                    },          
                    {
                        extend: 'excelHtml5',
                          title: 'Lotes Vencidos'
                    },
                     {
                        extend: 'pdfHtml5',
                           title: 'Lotes Vencidos'
                    }
                ],
                bDestroy: true,
                responsive: true, 
                serverSide: true,
                "pagingType": "full_numbers",
                "language": {
                      "lengthMenu": "Mostrando _MENU_ registros",
                      "zeroRecords": "Sin Registros - :(!!",
                      "info": "Pagina _PAGE_ de _PAGES_",
                      "infoEmpty": "No Existen Registros",
                      "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                       "sSearch": "Filtrar:",
                       "sProcessing":       "Cargando...",
                      "oPaginate": {
                          "sNext": "Sig",
                          "sPrevious": "Ant",
                          "sLast": "Ultimo",
                          "sFirst":     "Primero",
                      }
                },
                "columns": [
                    { "data": "cve_articulo" },
                    { "data": "articulo" },
                    { "data": "lote" },
                    { "data": "caducidad" },
                    { "data": "ubicacion" },
                    { "data": "existencia" },
                    { "data": "fecha_ingreso" },
                    { "data": "Proveedor" }
                ],
                "ajax": {
                    "url": "/api/reportes/lista/lotesVencidos.php",
                    "type": "GET",
                    "data": {
                        "almacen": $("#almacen").val()
                    }//, "success": function(data){console.log("SUCCESS = ", data);}
                    //, error: function(data){console.log("ERROR = ", data);}
                }
            });
            $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
            $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
            $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
            $('input[type=search]').attr('class', 'form-control input-sm');
            
            $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e){
                var title = 'Reporte de ' + $('#list h3:first-of-type').text();
                var cia = <?php echo $_SESSION['cve_cia'] ?>;
                var content = '';

                $.ajax({
                    url: "/api/reportes/update/index.php",
                    //url: "/api/reportes/lista/lotesVencidos.php",
                    //type: "GET",
                    type: "POST",
                    data: {
                        "almacen": $("#almacen").val(),
                        "action":"lotesvencidos"
                        
                    },
                    success: function(data, textStatus, xhr){
                        console.log("SUCCESS", data);
                        var content_wrapper = document.createElement('div');
                        var table = document.createElement('table');
                        table.style.width = "100%";
                        table.style.borderSpacing = "0";
                        table.style.borderCollapse = "collapse";
                        var thead = document.createElement('thead');
                        var tbody = document.createElement('tbody');
                        var head_content = `<tr>
                                                <th style="border: 1px solid #ccc">Lote</th>
                                                <th style="border: 1px solid #ccc">Clave del Articulo</th>
                                                <th style="border: 1px solid #ccc">Articulo</th>
                                                
                                                <th style="border: 1px solid #ccc">Dias a vencer</th>
                                                <th style="border: 1px solid #ccc">Ubicacion</th>
                                                <th style="border: 1px solid #ccc">Existencia</th>
                                                <th style="border: 1px solid #ccc">Proveedor</th>
                                            </tr>`;
                        var body_content = '';
                        var data = JSON.parse(data).data;
                        
                        data.forEach(function(item, index){
                            body_content += `
                                <tr>
                                    <td style="border: 1px solid #ccc">${item.lote}</td>
                                    <td style="border: 1px solid #ccc">${item.cve_articulo}</td>
                                    <td style="border: 1px solid #ccc">${item.articulo}</td>
                   
                                    <td style="border: 1px solid #ccc">${item.caducidad}</td>
                                    <td style="border: 1px solid #ccc">${item.ubicacion}</td>
                                    <td style="border: 1px solid #ccc">${item.existencia}</td>
                                    <td style="border: 1px solid #ccc">${item.Proveedor}</td>
                                </tr>
                            `;
                        });

                        tbody.innerHTML = body_content;
                        thead.innerHTML = head_content;
                        table.appendChild(thead);
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
                    }, error: function(data){
                        console.log("ERROR", data);
                    }
                });

            });
        }
    });
});
    
    
</script>