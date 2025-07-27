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
    <h3>Reporte Concentrado del Inventario Ciclico</h3>
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

                                    <th>Inventario</th>
                                    <th>Conteo</th>
                                    <th>Fecha de Inventario</th>
                                    <th>Clave</th>
                                    <th>Descripcion</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
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
                    title: 'Reporte Concentrado del Inventario Físico'
                },

                {
                    extend: 'excelHtml5',
                    title: 'Reporte Concentrado del Inventario Físico'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Reporte Concentrado del Inventario Físico',

                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (doc) {
                        doc.content.splice( 1, 0, {
                            margin: [ 0, 0, 0, 12 ],

                            alignment: 'left',
                            image: 'data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QOHaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjAtYzA2MCA2MS4xMzQ3NzcsIDIwMTAvMDIvMTItMTc6MzI6MDAgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iR29vZ2xlIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkExODk4RUE5NjEyMzExRTdBQTI2OUVDQUY5RTM1NDc2IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkExODk4RUFBNjEyMzExRTdBQTI2OUVDQUY5RTM1NDc2Ij4gPGRjOmNyZWF0b3I+IDxyZGY6U2VxPiA8cmRmOmxpPkFOR0lFPC9yZGY6bGk+IDwvcmRmOlNlcT4gPC9kYzpjcmVhdG9yPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpBMTg5OEVBNzYxMjMxMUU3QUEyNjlFQ0FGOUUzNTQ3NiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpBMTg5OEVBODYxMjMxMUU3QUEyNjlFQ0FGOUUzNTQ3NiIvPiA8L3JkZjpEZXNj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'
                        },
                                           {
                            margin: [ 0, -50, 0, 10 ],
                            text: "Agencia de Servicios Logísticos",
                            fontSize: 15,
                            alignment: 'center'
                        },
                                           {
                            margin: [ 0, 0, 0, 15 ],
                            text: "<?php echo date('l jS \of F Y h:i:s A'); ?>",
                            fontSize: 8,
                            alignment: 'right'
                        },);
                        //doc.pageMargins = [10,10,10,10];
                        // doc.defaultStyle.fontSize = 7;
                        // doc.styles.tableHeader.fontSize = 7;
                        //  doc.styles.title.fontSize = 9;
                        // Remove spaces around page title
                        doc.content[0].text = doc.content[0].text.trim();
                        // Create a footer
                        doc['footer']=(function(page, pages) {
                            return {
                                columns: [
                                    '<?php echo date('l jS \of F Y h:i:s A'); ?>',
                                    {
                                        // This is the right column
                                        alignment: 'right',
                                        text: ['page ', { text: page.toString() },  ' of ', { text: pages.toString() }]
                                    }
                                ],
                                margin: [10, 0]
                            }
                        });

                    }
                },


            ],
            responsive: true,
            serverSide:true,
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
                    "sFirst":"Primero",
                }
            },
            "ajax": {
                "url": "/api/reportes/lista/invcicl.php",
                "type": "GET",
                "columns": [
                    { "data.": "inventario" },
                    { "data": "conteo" },
                    { "data": "fecha" },
                    { "data": "clave" },
                    { "data": "descripcion" },
                    { "data": "cantidad" },
                    { "targets": -1, "data": null, "defaultContent": "<button class='btn btn-danger btn-sm bt pdf'>PDF</button>"}
                ],

            },

        } );

        $(".dt-button.buttons-csv").attr('class','btn btn-info btn-blue btn-sm bt');
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        //$(".paginate_button").attr('class', 'btn btn-primary btn-sm');
        $('input[type=search]').attr('class', 'form-control input-sm');


        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e){
            var title = '' + $('#list h3:first-of-type').text();
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';

            $.ajax({
                url: "/api/reportes/update/index.php",
                type: "POST",
                data: {
                    "action":"invcicl"

                },
                success: function(data, textStatus, xhr){
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '<tr><th style="border: 1px solid #ccc">Inventario</th>'+
                        '<th style="border: 1px solid #ccc">Conteo</th>'+
                        '<th style="border: 1px solid #ccc">Fecha de Inventario</th>    '+
                        '<th style="border: 1px solid #ccc">Proveedor</th>'+
                        '<th style="border: 1px solid #ccc">Clave</th>   '+
                        '<th style="border: 1px solid #ccc">Descripcion</th>  '+
                        '<th style="border: 1px solid #ccc">Cantidad</th>  '+
                        '</tr>';
                    var body_content = '';
                    var data = JSON.parse(data).data;

                    data.forEach(function(item, index){
                        body_content += '<tr>'+
                            '<td style="border: 1px solid #ccc">'+item.inventario+'</td> '+
                            '<td style="border: 1px solid #ccc">'+item.conteo+'</td>    '+
                            '<td style="border: 1px solid #ccc">'+item.fecha+'</td>        '+
                            '<td style="border: 1px solid #ccc">'+item.clave+'</td>'+
                            '<td style="border: 1px solid #ccc">'+item.descripcion+'</td>       '+
                            '<td style="border: 1px solid #ccc">'+item.cantidad+'</td>     '+
                            '</tr>  ';                                                         

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
                }
            });

        });



    });


</script>
<style>
    .dt-buttons{
        display: none;
    }
</style>