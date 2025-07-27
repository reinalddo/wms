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

if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

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

<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reporte Entradas</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Almacen</label>
                                <div class="input-group"> 
                                    <select class="form-control" id="almacen">
                                        <option value="">Seleccione un almacen</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Numero de Entrada</label>
                                <div class="input-group">
                                    <select class="form-control" id="entrada">
                                        <option value="">Seleccione una Entrada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Fecha de Entrada</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fecha" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <button style="margin-top: 25px;" class="btn btn-primary btn-sm" onclick="search()">Buscar</button>
                        </div>                  
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="example"  class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Entrada</th>
                                    <th>Almacén</th>
                                    <th>Orden de Entrada</th>
                                    <th>Proveedor</th>
                                    <th>Fecha Entrada</th>
                                    <th>Recibió</th>
                                    <th>Autorizo</th>
                                    
                                    <!--<th>Clave Artículo</th>
<th>Descripcion Artículo</th>
<th>Cantidad Recibida</th>-->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="detalleModal" role="dialog" style="overflow-x: scroll;">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;overflow-x: scroll; ">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Entradas <span id="n_inventario">0</span></h4>
                </div>
                <div class="modal-body" style="overflow-x: scroll;">
                    <div class="table-responsive">
                        <table id="detalleTabla" class="table table-hover table-striped no-margin">
                            <thead>
                                <tr>
                                    <th>Clave Artículo</th>
                                    <th>Descripción</th>
                                    <th>Lote</th>
                                    <th>Caducidad</th>
                                    <th>Numero de Serie</th>
                                    <th>Total Pedido</th>
                                    <th>Cantidad Recibida</th>
                                    <th>Diferencia</th>
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
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
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
                        search();
                    }
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        almacenPrede();
        
        $("#almacen").on("change", function(e){
            $.ajax({
                url: '/api/reportes/lista/entrada.php',
                method: 'GET',
                data: {
                    action: 'entrada',
                    almacen: e.target.value
                },
                dataType: 'json'
            }).done(function(data){
                var select = document.getElementById("entrada"),
                    def = document.createElement("option");
                select.innerHTML = "";
                def.value = "";
                def.text = "Seleccione una Entrada";
                select.appendChild(def);
                if(data.length > 0){
                    data.forEach(function(e){
                        var option = document.createElement("option");
                        option.value = e.n;
                        option.text = e.n;
                        select.appendChild(option);
                    });
                }
            });
        });
        
        $("#basic").select2();
        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        var table=$('#example').DataTable({
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
                }           
            ],
            "pageLength": 30,
            "responsive": true, 
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
            }
        });

        $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $('input[type=search]').attr('class', 'form-control input-sm');




    });

    function search(){
        var table=$('#example').DataTable({
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
                }           
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
                { "width": "25%", "data": "acciones"},
                { "width": "10%", "data": "entrada" },
                { "width": "10%", "data": "almacen" },
                { "width": "15%", "data": "orden_entrada" },
                { "width": "10%", "data": "proveedor" },
                { "width": "10%", "data": "fecha_entrada" },
                { "width": "10%", "data": "dio_entrada" },
                { "width": "10%", "data": "autorizo" }
                
                /*{ "data": "clave_articulo" },
            { "data": "descripcion_articulo" },
            { "data": "cantidad_recibida" }*/
            ],
            "ajax": {
                "url": "/api/reportes/lista/entrada.php",
                "type": "GET",
                "data": {
                    "fecha": $("#fecha").val().length > 0 ? moment($("#fecha").val(), "DD-MM-YYYY").format("YYYY-MM-DD") : "",
                    "almacen":$("#almacen").val(),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "entrada":$("#entrada").val()
                }/*,"success": function(data){
                    console.log("SUCCESS = ", data);
                } */
            },
        });



        /*$('#example tbody').on( 'click', 'button', function () {
            var data = table.row( $(this).parents('tr') ).data();

            var title = '' + $('#list h3:first-of-type').text();
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';

            $.ajax({
                url: "/api/reportes/update/index.php",
                type: "POST",
                data: {
                    "action":"entrada",
                    "idss":data[0]
                },
                success: function(data, textStatus, xhr){
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    var table2 = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');

                    table2.style.width = "100%";
                    table2.style.borderSpacing = "0";
                    table2.style.borderCollapse = "collapse";
                    var thead2 = document.createElement('thead');
                    var tbody2 = document.createElement('tbody');

                    var head_content = '<tr><th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Entrada</th>'+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Almacen</th>'+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Orden de Entrada</th>    '+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Proveedor</th>'+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Fecha Entrada</th>   '+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Recibió</th>  '+
                        '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Autorizo</th>  '+                              
                        '</tr>';


                    var head_content2 = '<tr><th style="border: 1px solid #ccc">Clave Artículo</th>'+
                        '<th style="border: 1px solid #ccc">Articulo</th>     '+
                        '<th style="border: 1px solid #ccc">Lote</th>     '+
                        '<th style="border: 1px solid #ccc">Serie</th>     '+
                        '<th style="border: 1px solid #ccc">Caducidad</th>     '+
                        '<th style="border: 1px solid #ccc">Cantidad Recibida</th>           '+                                  
                        '</tr>';

                    var body_content = '';
                    var body_content2 = '';
                    var data = JSON.parse(data).data;

                    data.forEach(function(item, index){
                        body_content += '<tr>'+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.entrada+'</td> '+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.almacen+'</td>    '+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.orden_entrada+'</td>        '+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.proveedor+'</td>'+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.fecha_entrada+'</td>       '+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.dio_entrada+'</td>     '+
                            '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+item.autorizo+'</td>      '+
                            '</tr>  ';              

                        body_content2 += '<tr>'+
                            '<td style="border: 1px solid #ccc">'+item.clave_articulo+'</td>      '+
                            '<td style="border: 1px solid #ccc">'+item.articulo+'</td>         '+
                            '<td style="border: 1px solid #ccc">'+item.lote+'</td>         '+
                            '<td style="border: 1px solid #ccc">'+item.serie+'</td>         '+
                            '<td style="border: 1px solid #ccc">'+item.caducidad+'</td>         '+
                            '<td style="border: 1px solid #ccc">'+item.cantidad_recibida+'</td>             '+
                            '</tr>  ';                 

                    });

                    tbody.innerHTML = body_content;
                    thead.innerHTML = head_content;
                    table.appendChild(thead);
                    table.appendChild(tbody);
                    content_wrapper.appendChild(table);


                    tbody2.innerHTML = body_content2;
                    thead2.innerHTML = head_content2;
                    table2.appendChild(thead2);
                    table2.appendChild(tbody2);
                    content_wrapper.appendChild(table2);
                    content += content_wrapper.innerHTML;



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

        } );*/



    }


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
                { "data": "clave_articulo" }, 
                { "data": "articulo" },
                { "data": "lote" },
                { "data": "caducidad" },
                { "data": "serie" },
                { "data": "total_pedido", "className": "text-right" },
                { "data": "cantidad_recibida", "className": "text-right" },
                { "data": "faltante", "className": "text-right" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/entrada.php",
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
        form.setAttribute('action', '/api/reportes/lista/entrada.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }

    function printPDF(consecutivo){

        if (typeof(consecutivo) === "number") consecutivo = consecutivo.toString();
        console.log("OK");
        var id = "00".substring(0, 2 - consecutivo.length) + consecutivo;
        var title = $('#list h3:first-of-type').text().replace(/\s/g,'') + "_" + id;
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"entrada",
                "idss":consecutivo
            },
            success: function(data, textStatus, xhr){
                  
                var data = JSON.parse(data).data;
                var content_wrapper = document.createElement('div');
                /*Encabezado*/
                var table_header = document.createElement('table');
                table_header.style.width = "100%";
                table_header.style.borderSpacing = "0";
                table_header.style.borderCollapse = "collapse";
                var thead_header = document.createElement('thead');
                var tbody_header = document.createElement('tbody');
                var head_content_header = '<tr><th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Entrada</th>'+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Almacen</th>'+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Orden de Entrada</th>    '+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Proveedor</th>'+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Fecha Entrada</th>   '+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Recibió</th>  '+
                    '<th style="border: 0px solid #ccc;text-align:center;width:16.6% !important">Autorizo</th>  '+                              
                    '</tr>';
                var body_content_header = '<tr>'+
                    '<td style="border: 0px solid #ccc;text-align:center;width:16.6% !important">'+data.header[0].entrada+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].almacen+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].orden_entrada+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].proveedor+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].fecha_entrada+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].dio_entrada+'</td> '+
                    '<td style="border: 0px solid #ccc;text-align:center;">'+data.header[0].autorizo+'</td> '+
                    '</tr>  ';    
                /*Detalle*/


                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr><th style="border: 1px solid #ccc">Clave Artículo</th>'+
                    '<th style="border: 1px solid #ccc">Articulo</th>     '+
                    '<th style="border: 1px solid #ccc">Lote|Serie</th>     '+
                    '<th style="border: 1px solid #ccc">Caducidad</th>     '+
                    '<th style="border: 1px solid #ccc">Total Pedido</th>           '+     
                    '<th style="border: 1px solid #ccc">Cantidad Recibida</th>           '+     
                    '<th style="border: 1px solid #ccc">Cantidad Faltante</th>           '+     
                    '</tr>';
                var body_content = '';

                data.body.forEach(function(item, index){
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc">'+item.clave_articulo+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.articulo+'</td>         '+
                        '<td style="border: 1px solid #ccc">'+item.lote_serie+'</td>         '+
                        '<td style="border: 1px solid #ccc">'+item.caducidad+'</td>         '+
                        '<td style="border: 1px solid #ccc;text-align:right;">'+item.total_pedido+'</td>             '+
                        '<td style="border: 1px solid #ccc;text-align:right;">'+item.cantidad_recibida+'</td>             '+
                        '<td style="border: 1px solid #ccc;text-align:right;">'+item.faltante+'</td>             '+
                        '</tr>  ';                                                         

                });

                tbody_header.innerHTML = body_content_header;
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
                form.setAttribute("method", "POST");
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

    }

</script>

<style>
    .dt-buttons{
        display: none;
    }
</style>