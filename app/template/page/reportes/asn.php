<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$invSql = \db()->prepare("SELECT *  FROM th_inventario ");
$invSql->execute();
$niventario = $invSql->fetchAll(PDO::FETCH_ASSOC);

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/dataTables1.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
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
<input type="hidden" id="instancia" name="instancia" value="<?php echo $instancia; ?>">
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reporte ASN</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div id="for" style="display: none;"></div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table-info"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Folio de Embarque </th>
                                    <th>Cliente</th>
                                    <th>Pedido | OC Cliente</th>
                                    <th>Pedido</th>
                                    <!--<th>Cantidad</th>
                                    <th>Total Cajas | Pallet</th>-->
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
                    <h4>Detalle de ASN #<span id="n_inventario">0</span></h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="table-deta"  class="table table-hover table-striped no-margin display nowrap" style="width:100%">
                            <thead style="width:100%">
                                    <th>No. Embarque</th>
                                    <th>Folio</th>
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Cantidad</th>
                                    <th>Clave Caja</th>
                                    <th>Guia</th>
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
<script src="/js/plugins/dataTables/dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables_bootstrap.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script> 
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>    

<script>
    var tableDataInfo = new TableDataRest(),
        tableDataDeta = new TableDataRest(),
        TABLE = null,
        buttons = [{
                    extend: 'excelHtml5',
                    title: 'Reabastecer Picking',
                    customize: function() { swal("Descargando Excel", "Su descarga empezara en breve", "success");}
                    },
                    {
                    extend: 'pdfHtml5',
                    title: 'Reabastecer Picking',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    download: 'open',
                    customize: function() { swal("Descargando PDF", "Su descarga empezara en breve", "success");},
                    }
                ];

    searchData();

    function searchData(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_almacen: <?php echo $_SESSION['id_almacen'] ?>,
                action: "init"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/lista/asn.php',
            success: function(data) {
                window.console.log(data.res);
                fillTableInfo(data.res);
            },
            error:function(res){
                window.console.log(res);
            }
        });

    }

    function fillTableInfo(node){

        var data = [];

        var chofer = '', transporte = '', id_transporte = '', sello = '';
        DATA = node;

        for(var i = 0; i < node.length ; i++){

            chofer = node[i].chofer;
            transporte = node[i].transporte;
            id_transporte = node[i].id_transporte;
            sello = node[i].sello;
            
            if(chofer != '')
                chofer = 'Chofer: '+chofer;
            else 
                chofer = '';

            if(id_transporte != '')
                transporte = 'Transporte: '+transporte;
            else
                transporte = '';

            var button = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='searchDataDeta("+node[i].folio+")'><i class='fa fa-search'></i></button>"+
                        "&nbsp;";
                button += '<button class="btn btn-sm btn-danger no" title="Reporte PDF" onclick="printPDF(\'' + node[i].folio + '\', \'' + chofer + '\', \'' + transporte + '\', \'' + sello + '\')"> <i class="fa fa-print"></i><sup> PDF</sup></button>&nbsp;';
                button +=  "<button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel("+node[i].folio+")'> <i class='fa fa-print'></i><sup> Excel</sup></button>"+
                "&nbsp;";
                //button += "<button class='btn btn-sm btn-success no' title='Reporte txt' onclick='test("+node[i].folio+")'> <i class='fa fa-print'></i><sup> TXT</sup></button>";

            data.push([
                button,
                node[i].folio,
                node[i].cliente,
                node[i].factura,
                node[i].pedido,
                //node[i].cantidad,
                //node[i].total_cajas,
                ]);
        }
        tableDataInfo.destroy();
        tableDataInfo.init("table-info", buttons, true, data);
    }

    function searchDataDeta(id){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: "detalle",
                inventario : id
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/reportes/lista/asn.php',
            success: function(data) {
                document.getElementById("n_inventario").textContent = id;
                $("#detalleModal").modal('show');
                window.console.log(data.res);
                fillTableDeta(data.res);
            },
            error:function(res){
                window.console.log(res);
            }
        });
    }

    function fillTableDeta(node){

        var data = [];

        DATA = node;

        for(var i = 0; i < node.length ; i++){

            data.push([
                node[i].embarque,
                node[i].folio,
                node[i].clave,
                node[i].articulo,
                node[i].cantidad,
                node[i].caja,
                node[i].guia,
                ]);
        }
        tableDataDeta.destroy();
        tableDataDeta.init("table-deta", buttons, true, data);
    }
/*
    $(document).ready(function(){

        var table = $('#example').DataTable({
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
                { "width": "10%", "data": "num_orden" },
                { "width": "10%", "data": "Empresa" },
                { "width": "15%", "data": "factura" },
                { "width": "10%", "data": "cantidad" },
                { "width": "10%", "data": "cantidad" },
                { "width": "10%", "data": "cantidad" },
                { "width": "10%", "data": "cantidad" },
                { "width": "25%", "data": "acciones"}
            ],
            "ajax": {
                "url": "/api/reportes/lista/asn.php",
                "type": "GET"
            },
        });
    });
*/
   /* function see(id){
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
                { "data": "nro_cliente" }, 
                { "data": "cliente" }, 
                { "data": "clave" }, 
                { "data": "descripcion" },
                { "data": "cantidad" },
                { "data": "lote" },
                { "data": "caducidad" },
                { "data": "serie" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/asn.php",
                "type": "POST",
                "data": {
                    "inventario": id
                }

            },
        } );
    }
*/
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
        form.setAttribute('action', '/api/reportes/lista/asn.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        document.body.appendChild(form);
        form.submit();
    }

    function printPDF(consecutivo, chofer, transporte, sello){

        if (typeof(consecutivo) === "number") consecutivo = consecutivo.toString();
        var id = "00".substring(0, 2 - consecutivo.length) + consecutivo;
        var title = $('#list h3:first-of-type').text().replace(/\s/g,'') + "_" + id;
        var title_con_chofer = '';

        console.log("consecutivo = ", consecutivo);
        console.log("id = ", id);
        console.log("title = ", title);

        if($("#instancia").val() == 'asl')
        {
            title_con_chofer = title;
            title = "Remisión de Despacho<br><br>"+chofer+"<br><br>"+transporte;
        }
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"asn",
                "idss":consecutivo
            },
            success: function(data, textStatus, xhr){
                console.log("RES = ", data);
                var data = JSON.parse(data).data;
                var content_wrapper = document.createElement('div');
                console.log("RES JSON = ", data);
                /*Encabezado*/
                var table_header = document.createElement('table');
                table_header.style.width = "100%";
                table_header.style.borderSpacing = "0";
                table_header.style.borderCollapse = "collapse";
                var thead_header = document.createElement('thead');
                var tbody_header = document.createElement('tbody');
                var cantidad_pallets_cajas = (data.suma[0].cantidad_pallets==0)?(data.header[0].total_cajas):(data.suma[0].cantidad_pallets);
                var head_content_header = '<tr><th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Folio de Embarque</th>'+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Fecha Embarque</th>'+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Cliente</th>'+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Pedido | OC Cliente</th>    '+
                    //'<th style="border: 1px solid #ccc;text-align:center;width:14.28% !important">Pedido</th>'+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Cantidad</th>   '+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Peso</th>   '+
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Total Cajas | Pallet</th>  '+                           
                    '<th style="border: 1px solid #ccc;text-align:center;width:12.5% !important">Sello</th>  '+                           
                    '</tr>';
                var body_content_header = '<tr>'+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].folio+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].FechaEmbarque+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].cliente+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].factura+'</td> '+
                    //'<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].pedido+'</td> '+
                    //'<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].cantidad+'</td> '+
                    //'<td style="border: 1px solid #ccc;text-align:center;">'+data.header[0].peso+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.suma[0].cantidad+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+data.suma[0].peso+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+cantidad_pallets_cajas+'</td> '+
                    '<td style="border: 1px solid #ccc;text-align:center;">'+sello+'</td> '+
                    '</tr>  ';    
                /*Detalle*/

                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr>'+
                    '<th style="border: 1px solid #ccc; text-align:center;width:10%;">Pedido</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:10%;">Clave</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:20%;">Articulo</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:15%;">Lote/Serie</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:5%;">Cantidad</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:5%;">Peso</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:10%;">Tipo Caja</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:15%;">Guia</th>     '+
                    '<th style="border: 1px solid #ccc; text-align:center;width:10%;">LP</th>     '+
                    '</tr>';
                var body_content = '';
                data.body.forEach(function(item, index){
                    if(item.cantidad > 0)
                    {
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc; text-align:left;width:10%;">'+item.folio+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:10%;">'+item.clave+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:15%;">'+item.articulo+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:15%;">'+item.Lote_Serie+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:right;width:10%;">'+item.cantidad+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:right;width:5%;">'+item.peso+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:10%;">'+item.caja+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:15%;">'+item.guia+'</td>         '+
                        '<td style="border: 1px solid #ccc; text-align:left;width:10%;">'+item.lp+'</td>         '+
                        '</tr>  ';                                                         
                    }

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
                form.setAttribute("method", "post");
                form.setAttribute("action", "/api/reportes/generar/pdf.php");
                form.setAttribute("target", "_blank");

                var input_content = document.createElement('input');
                var input_title = document.createElement('input');
                var input_title_con_chofer = document.createElement('input');
                var input_cia = document.createElement('input');
                input_content.setAttribute('type', 'hidden');
                input_title.setAttribute('type', 'hidden');
                input_cia.setAttribute('type', 'hidden');
                input_content.setAttribute('name', 'content');
                input_title.setAttribute('name', 'title');
                input_title_con_chofer.setAttribute('name', 'title_con_chofer');
                input_cia.setAttribute('name', 'cia');
                input_content.setAttribute('value', content);
                input_title.setAttribute('value', title);
                input_title_con_chofer.setAttribute('value', title_con_chofer);
                input_cia.setAttribute('value', cia);

                form.appendChild(input_content);
                form.appendChild(input_title);
                form.appendChild(input_title_con_chofer);
                form.appendChild(input_cia);

                document.body.appendChild(form);
                form.submit();


            }, error: function(data)
            {
                console.log("ERROR PDF ", data);
            }
        });
    }

    function test(id) {

        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input");
        input1.setAttribute('name', 'action');
        input1.setAttribute('value', 'txt');
        input2.setAttribute('name', 'id');
        input2.setAttribute('value', id);
        form.setAttribute('action', '/api/reportes/lista/asn.php');
        form.setAttribute('method', 'POST');
        form.setAttribute('target', '_blank');
        form.setAttribute("type", "hidden");
        form.appendChild(input1);
        form.appendChild(input2);
        //document.body.appendChild(form);
        document.getElementById("for").appendChild(form);
        form.submit();
     }

</script>

<style>

    .dt-buttons{
        display: none;
    }

</style>