<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$invSql = \db()->prepare("SELECT *  FROM th_inventario ");
$invSql->execute();
$niventario = $invSql->fetchAll(PDO::FETCH_ASSOC);
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
    <h3>Reporte Ajustes de Existencia</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                      <!--  <div class="col-lg-3">
                            <div class="form-group">
                                <label>Almacen</label>
                                <div class="input-group"> 
                                    <select class="form-control" id="almacen">
                                        <option value="">Seleccione un almacen</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo $a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Numero de Inventario</label>
                                <div class="input-group">
                                    <select class="form-control" id="inventario">
                                        <option value="">Seleccione un inventario</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Fecha de Inventario</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fecha" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <button style="margin-top: 25px;" class="btn btn-primary btn-sm" onclick="search()">Buscar</button>
                        </div>      -->            
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="example"  class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>N° Ajuste de Inventario</th>
                                    <th>N° Inventario</th>
                                    <th>Fecha</th>
                                    <th>Responsable</th>
                                    <th>Diferencias</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="detalleModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Ajuste de Existencia <span id="n_inventario">0</span></h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="detalleTabla" class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th>Conteo</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Ubicación</th>
                                    <th>Stock Teórico</th>
                                    <th>Stock Físico</th>
                                    <th>Diferencia</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                        </table>
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
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
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
    $(document).ready(function(){
        $("#basic").select2();

       /* $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });
*/
        var table_detalle=$('#detalleTabla').DataTable({
            "processing": true,
            "pageLength": 30,
            "bDestroy": true,
            "bLengthChange": false,
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
                    "sFirst":       "Primero",
                }
            }
        });
        
        
        var table=$('#example').DataTable( {
                "processing": true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: 'Comprobante de Ingreso'
                    },

                    {
                        extend: 'excelHtml5',
                        title: 'Comprobante de Ingreso'
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Comprobante de Ingreso'
                    },


                ],
                "pageLength": 30,
                "responsive": true, 
                "serverSide": true,
                "bDestroy": true,
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
                        "sFirst":       "Primero",
                    }
                },
                "columns": [
                    { "width": "20%","data": "acciones" },
                    { "width": "15%","data": "ajuste" },
                    { "width": "15%","data": "inventario" },
                    { "width": "20%","data": "fecha" },  
                    { "width": "20%","data": "usuario" },
                    { "width": "10%","data": "diferencia" },
                     
                ],
                "pageLength": 30,
                "ajax": {
                    "url": "/api/reportes/lista/ajustesexistencia.php",
                    "type": "GET"/*,
                    "data": {
                        "fecha": $("#fecha").val().length > 0 ? moment($("#fecha").val(), "DD-MM-YYYY").format("YYYY-MM-DD") : "",
                        "almacen":$("#almacen").val(),
                        "inventario":$("#inventario").val()
                    }*/

                },

            } );

    });

    function see(id){
        document.getElementById("n_inventario").textContent = id;
        $("#detalleModal").modal('show');

        var table_detalle=$('#detalleTabla').DataTable( {
            "processing": true,
            "responsive": true, 
            "serverSide": true,
            "bDestroy": true,
            "bLengthChange": false,
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
                    "sFirst":       "Primero",
                }
            },
            "columns": [
                { "data": "conteo" }, 
                { "data": "clave" }, 
                { "data": "descripcion" },
                { "data": "ubicacion" },  
                { "data": "stockTeorico" }, 
                { "data": "stockFisico" }, 
                { "data": "diferencia" }, 
                { "data": "usuario" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/invfidet.php",
                "type": "POST",
                "data": {
                    "inventario": id
                }

            },

        } );
    }



    function printExcel(consecutivo){
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input");
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'id');
        input2.setAttribute('value', consecutivo);
        input3.setAttribute('name', 'action');
        input3.setAttribute('value', 'exportExcelinvfidet');
        form.setAttribute('action', '/api/reportes/lista/invfidet.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }

    function printPDF(consecutivo){

        /*
            { "data": "conteo" }, 
                { "data": "clave" }, 
                { "data": "descripcion" },
                { "data": "ubicacion" },  
                { "data": "stockTeorico" }, 
                { "data": "stockFisico" }, 
                { "data": "diferencia" }, 
                { "data": "usuario" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/invfidet.php",
                "type": "POST",
                "data": {
                    "inventario": id
                }
        */

        if (typeof(consecutivo) === "number") consecutivo = consecutivo.toString();
        var id = "00".substring(0, 2 - consecutivo.length) + consecutivo;
        var title = $('#list h3:first-of-type').text().replace(/\s/g,'') + "_" + id;
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = consecutivo;
        $.ajax({
            url: "/api/reportes/lista/ajustesexistencia.php",
            type: "POST",
            data: {
                "inventario": consecutivo,
                "action": 'exportPDFinvfidet'
            },
            success: function(_data){
                var data = JSON.parse(_data);
                console.log(data);
                var content_wrapper = document.createElement('div');
                /*Encabezado*/
                var table_header = document.createElement('table');
                table_header.style.width = "100%";
                table_header.style.borderSpacing = "0";
                table_header.style.borderCollapse = "collapse";
                var thead_header = document.createElement('thead');
                var tbody_header = document.createElement('tbody');
                var head_content_header = '<tr>'+
                    '<th style="border: 1px solid #ccc">Inventario Físico</th>'+
                    '<th style="border: 1px solid #ccc">Almacen</th>'+
                    '<th style="border: 1px solid #ccc">Supervisor</th>'+
                    '</tr>';                  
                var body_contenido = '<tr>'+
                        '<td style="border: 1px solid #ccc; text-align: center;">'+data[0].folio+'</td> '+
                        '<td style="border: 1px solid #ccc; text-align: center;">'+data[0].almacen+'</td>    '+
                        '<td style="border: 1px solid #ccc; text-align: center;">'+data[0].usuario+'</td>        '+
                        '</tr>  ';                                                         
                

                /*Detalle*/
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr>'+
                    '<th style="border: 1px solid #ccc">Clave</th>'+
                    '<th style="border: 1px solid #ccc">Descripción</th>'+
                    '<th style="border: 1px solid #ccc">Ubicación</th>    '+
                    '<th style="border: 1px solid #ccc">Stock Teórico</th>'+
                    '<th style="border: 1px solid #ccc">Stock Físico</th>   '+
                    '<th style="border: 1px solid #ccc">Diferencia</th>  '+
                    '<th style="border: 1px solid #ccc">Stock Final</th>  '+                                      
                    '</tr>';
                var body_content = '';

                for(var i = 0; i < data.length; i++){
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc">'+data[i].clave+'</td> '+
                        '<td style="border: 1px solid #ccc">'+data[i].descripcion+'</td>    '+
                        '<td style="border: 1px solid #ccc">'+data[i].ubicacion+'</td>        '+
                        '<td style="border: 1px solid #ccc">'+data[i].stockTeorico+'</td>        '+           
                        '<td style="border: 1px solid #ccc">'+data[i].stockFisico+'</td>       '+
                        '<td style="border: 1px solid #ccc">'+data[i].diferencia+'</td>     '+
                        '<td style="border: 1px solid #ccc">'+data[i].final+'</td>     '+
                        '</tr>  ';                                                         
                }

                tbody_header.innerHTML = body_contenido;
                thead_header.innerHTML = head_content_header;
                table_header.appendChild(thead_header);
                table_header.appendChild(tbody_header);

                tbody.innerHTML = body_content;
                thead.innerHTML = head_content;
                table.appendChild(thead);
                table.appendChild(tbody);

                content_wrapper.appendChild(table_header);
                content_wrapper.appendChild(document.createElement('br'));
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
            },
            error:function(res){
                window.console.log(res);
            }
        });
    }

</script>

<style>
    .dt-buttons, .dataTables_filter{
        display: none;
    }
</style>