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

    .verde{

        background: #71ff33 ;
    }

    .verde > td{

        background: #71ff33;
    }

    .rojo > td{

        background: #FF5233;
    }

    .rojo {

        background: #FF5233;
    }


    .amarillo > td{

        background:  #fffc33 ;
    }

    .amarillo {

        background: #fffc33;
    }

    .aa{

        width: 30px;
        height: 30px;
        margin-left: auto;
        margin-right: auto;
        border-radius: 50%;

    }

    #example2
    {
        width: 100% !important;
        margin: 0 !important;
    }

</style>
<input type="hidden" name="id_almacen" id="id_almacen" value="<?php echo $_SESSION['id_almacen']; ?>">
<div class="wrapper wrapper-content  animated " id="list">
    <h3>Reporte de Máximos y Mínimos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tab-1"> Por Producto</a></li>
                    <li class=""><a data-toggle="tab" href="#tab-2" class="aaa">Por Almacén</a></li>
                </ul>
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="panel-body">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <div class="row">
                                        <div class="col-lg-2">
                                           <div class="form-group">
                                              <label>Tipo de ubicación</label>
                                              <select name="almacen" id="tipo" class="chosen-select form-control">
                                                  <option value="">Seleccione</option>
                                                  <option value="Picking">Picking</option>
                                                  <option value="PTL">PTL</option>
                                              </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                           <div class="form-group">
                                              <label>Buscar</label>
                                              <input type="text" class="form-control" name="buscador" id="buscador" placeholder="Buscar...">
                                            </div>
                                        </div>
                                        <div class="col-lg-2" style="padding-top: 27px;">
                                           <div class="form-group">
                                             <button class="btn btn-sm btn-primary" onclick="buscar()">Buscar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <button class="btn btn-sm btn-danger" onclick="printPDFProducto(0)">PDF</button>
                                            <button class="btn btn-sm btn-primary" onclick="printExcelProducto(0)">Excel</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="example"  class="table table-hover table-striped no-margin">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Descripcion</th>
                                                    <th>BL</th>
                                                    <th>Tipo de ubicación</th>
                                                    <th>Unidad</th>
                                                    <th>Maximo</th>
                                                    <th>Minimo</th>
                                                    <th>Existencia</th>
                                                    <th>Reabastecer</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-2" class="tab-pane">
                        <div class="panel-body">
                            <div class="ibox ">
                                <div class="ibox-title">
                                        <div class="col-lg-4">
                                           <div class="form-group">
                                              <label>Buscar</label>
                                              <input type="text" class="form-control" name="buscador2" id="buscador2" placeholder="Buscar...">
                                            </div>
                                        </div>
                                        <div class="col-lg-2" style="padding-top: 27px;">
                                           <div class="form-group">
                                             <button class="btn btn-sm btn-primary" onclick="buscar2()">Buscar</button>
                                            </div>
                                        </div>

                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <button class="btn btn-sm btn-danger" onclick="printPDFProducto(1)">PDF</button>
                                            <button class="btn btn-sm btn-primary" onclick="printExcelProducto(1)">Excel</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="example2"  class="table table-hover table-striped no-margin">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Descripcion</th>
                                                    <th>Unidad</th>
                                                    <th>Maximo</th>
                                                    <th>Minimo</th>
                                                    <th>Existencia</th>
                                                    <th>Reabastecer</th>
                                                    <th>Status</th>
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
    </div>
</div>
<input type="hidden" id="av" />

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
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>

<script type="text/javascript">

    function printExcelProducto(almacen){
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input"),
            input4 = document.createElement("input"),
            input5 = document.createElement("input");

        var tipo=$('#tipo').val();
        var id_almacen=$('#id_almacen').val();
console.log("id_almacen Excel = ", id_almacen);
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'action');
        input2.setAttribute('value', 'exportExcelProducto');
        input3.setAttribute('name', 'tipo_u');
        input3.setAttribute('value', tipo);
        input4.setAttribute('name', 'id_almacen');
        input4.setAttribute('value', id_almacen);
        input5.setAttribute('name', 'es_almacen');
        input5.setAttribute('value', almacen);
        form.setAttribute('action', '/api/reportes/lista/maxmin.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
        form.appendChild(input4);
        form.appendChild(input5);
        document.body.appendChild(form);
        form.submit();
    }
    function printExcelUbicacion(){
        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input");
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'action');
        input2.setAttribute('value', 'exportExcelUbicacion');
        form.setAttribute('action', '/api/reportes/lista/maxmin.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }

    function printPDFProducto(almacen){
        var title = "Reporte de máximos y mínimos por Ubicación";
        if(almacen == 1)
           title = "Reporte de máximos y mínimos por Almacén";

        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "tipo":$('#tipo').val(),
                "id_almacen":$('#id_almacen').val(),
                "es_almacen":almacen,
                "action":"maxmin1"
            },
            success: function(data, textStatus, xhr){
                var content_wrapper = document.createElement('div');
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr><th style="border: 1px solid #ccc">Producto</th>'+
                    '<th style="border: 1px solid #ccc">Descripcion</th>'+
                    '<th style="border: 1px solid #ccc">BL</th>'+
                    '<th style="border: 1px solid #ccc">Tipo de Ubicación</th>'+
                    '<th style="border: 1px solid #ccc">Unidad</th>    '+
                    '<th style="border: 1px solid #ccc">Máximo</th>'+
                    '<th style="border: 1px solid #ccc">Mínimo</th>   '+
                    '<th style="border: 1px solid #ccc">Existencia</th>  '+ 
                    '<th style="border: 1px solid #ccc">Reabastecer</th>  '+  
                    '</tr>';

                if(almacen == 1)
                {
                    head_content = '<tr><th style="border: 1px solid #ccc">Producto</th>'+
                    '<th style="border: 1px solid #ccc">Descripcion</th>'+
                    '<th style="border: 1px solid #ccc">Unidad</th>    '+
                    '<th style="border: 1px solid #ccc">Máximo</th>'+
                    '<th style="border: 1px solid #ccc">Mínimo</th>   '+
                    '<th style="border: 1px solid #ccc">Existencia</th>  '+ 
                    '<th style="border: 1px solid #ccc">Reabastecer</th>  '+  
                    '</tr>';
                }
                var body_content = '';
                var data = JSON.parse(data).data;
                console.log("1");

                data.forEach(function(item, index){
                    if(almacen == 1)
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc">'+item.clave_articulo+'</td> '+
                        '<td style="border: 1px solid #ccc">'+item.descripcion_articulo+'</td>    '+
                        '<td style="border: 1px solid #ccc">'+item.unidad+'</td>        '+           
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.maximo+'</td>       '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.minimo+'</td>     '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.existencia+'</td>      '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.pedir+'</td>      '+
                        '</tr>  ';                                                         
                    else
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc">'+item.clave_articulo+'</td> '+
                        '<td style="border: 1px solid #ccc">'+item.descripcion_articulo+'</td>    '+
                        '<td style="border: 1px solid #ccc">'+item.linea+'</td>        '+
                        '<td style="border: 1px solid #ccc">'+item.ubicacion+'</td>        '+
                        '<td style="border: 1px solid #ccc">'+item.unidad+'</td>        '+           
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.maximo+'</td>       '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.minimo+'</td>     '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.existencia+'</td>      '+
                        '<td style="border: 1px solid #ccc;text-align: right;">'+item.pedir+'</td>      '+
                        '</tr>  ';                                                         

                });
                console.log("2");
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
                console.log("3");
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
                console.log("4");
            }
        });
    }
    function printPDFUbicacion(){
        var title = "Reporte de máximos y mínimos por ubicación";
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/reportes/update/index.php",
            type: "POST",
            data: {
                "action":"maxmin2"
            },
            success: function(data, textStatus, xhr){
                var content_wrapper = document.createElement('div');
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr><th style="border: 1px solid #ccc">Clave de Producto</th>'+
                    '<th style="border: 1px solid #ccc">Descripcion</th>'+
                    '<th style="border: 1px solid #ccc">Zona</th>'+
                    '<th style="border: 1px solid #ccc">Pasillo</th>'+
                    '<th style="border: 1px solid #ccc">Rack</th>'+
                    '<th style="border: 1px solid #ccc">Nivel</th>'+
                    '<th style="border: 1px solid #ccc">Sección</th>'+
                    '<th style="border: 1px solid #ccc">Posición</th>'+
                    '<th style="border: 1px solid #ccc">Peso Máximo</th>'+
                    '<th style="border: 1px solid #ccc">Volumen (m3)</th>'+
                    '<th style="border: 1px solid #ccc">Dimensiones (Lar. X Anc. X Alt. )</th>    '+
                    '<th style="border: 1px solid #ccc">Picking</th>'+
                    '<th style="border: 1px solid #ccc">Ubicación PTL</th>'+
                    '<th style="border: 1px solid #ccc">Maximo</th>'+
                    '<th style="border: 1px solid #ccc">Minimo</th>   '+
                    '<th style="border: 1px solid #ccc">Existencia</th>  '+ 
                    '<th style="border: 1px solid #ccc">Reabastecer</th>  '+
                    '</tr>';
                var body_content = '';
                var data = JSON.parse(data).data;

                data.forEach(function(item, index){
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc">'+item.idy_ubica+'</td> '+
                        '<td style="border: 1px solid #ccc">'+item.des_articulo+'</td>    '+
                        '<td style="border: 1px solid #ccc">'+item.CodigoCSD+'</td>        '+
                        '<td style="border: 1px solid #ccc">'+item.cve_pasillo+'</td>        '+           
                        '<td style="border: 1px solid #ccc">'+item.cve_rack+'</td>       '+
                        '<td style="border: 1px solid #ccc">'+item.cve_nivel+'</td>     '+
                        '<td style="border: 1px solid #ccc">'+item.Seccion+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.Ubicacion+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.PesoMaximo+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.volumen+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.dim+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.picking+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.ptl+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.maximo+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.minimo+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.existencia+'</td>      '+
                        '<td style="border: 1px solid #ccc">'+item.reabastecer+'</td>      '+
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
    }
</script>

<script>
    $(document).ready(function(){

        function rowcolor(cellValue, options, rowObject) {
            if (cellValue == 0)
                rowsToColor[rowsToColor.length] = options.rowId;
            return cellValue;
        }

        $("#basic").select2();
        var tipo=$('#tipo').val();
        var id_almacen=$('#id_almacen').val();
        //var buscar=$('#buscador').val();

        console.log("id_almacen = ", id_almacen);

        $('#example').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            "bFilter": false,
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
                    "sFirst": "Primero",
                }
            },
            "columns": [ 
                { "data": "clave_articulo" },
                { "data": "descripcion_articulo" },
                { "data": "linea" },
                { "data": "ubicacion"},
                { "data": "unidad" },
                { "data": "maximo" },
                { "data": "minimo" },
                { "data": "existencia" },
                { "data": "pedir" },
                { "data": "sta",formatter: rowcolor },
            ],
            "pageLength": 30,
            "ajax": {
                "url": "/api/reportes/lista/maxmin.php?m=1&tipo_u="+tipo,
                "data": {
                    "tipo":$('#tipo').val(),
                    "id_almacen":$('#id_almacen').val(),
                    "buscar":$('#buscador').val()
                },

                "type": "GET"
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                // Bold the grade for all 'A' grade browsers

                $(nRow).children('td:eq(9)').html('');
                $(nRow).children('td:eq(9)').text('');

                $(nRow).children('td:eq(9)').append('<div class="'+aData[9]+' aa"></div>');
            }

        } );

        //$('.aaa').on('click',function(){
            //if($('#av').val()!='1'){
        $('#example2').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            "bFilter": false,
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
                "sProcessing":      "Cargando...",

                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst": "Primero",
                }
            },
            "columns": [ 
                { "data": "clave_articulo" },
                { "data": "descripcion_articulo" },
                { "data": "unidad" },
                { "data": "maximo" },
                { "data": "minimo" },
                { "data": "existencia" },
                { "data": "pedir" },
                { "data": "sta",formatter: rowcolor },
            ],
            "pageLength": 30,
            "ajax": {
                "url": "/api/reportes/lista/maxmin.php?m=2",
                "data": {
                    "id_almacen":$('#id_almacen').val(),
                    "buscar":$('#buscador2').val()
                },
                "type": "GET"
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                // Bold the grade for all 'A' grade browsers

                $(nRow).children('td:eq(7)').html('');
                $(nRow).children('td:eq(7)').text('');

                $(nRow).children('td:eq(7)').append('<div class="'+aData[7]+' aa"></div>');

            }

        } );
               // $('#av').val('1');
            //}
        //} );

    });
  
  function buscar()
  {
    $('#example').DataTable().destroy();
    function rowcolor(cellValue, options, rowObject) {
      if (cellValue == 0)
          rowsToColor[rowsToColor.length] = options.rowId;
      return cellValue;
    }
    var tipo=$('#tipo').val();
    var id_almacen=$('#id_almacen').val();
    var buscar=$('#buscador').val();
    $('#example').DataTable( {
        "processing": true,
        dom: 'Bfrtip',
        "bFilter": false,
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
                "sFirst":    	"Primero",
            }
        },
        "columns": [ 
            { "data": "clave_articulo" },
            { "data": "descripcion_articulo" },
            { "data": "linea" },
            { "data": "ubicacion"},
            { "data": "unidad" },
            { "data": "maximo" },
            { "data": "minimo" },
            { "data": "existencia" },
            { "data": "pedir" },
            { "data": "sta",formatter: rowcolor }
        ],
        "pageLength": 30,
        "ajax": {
            "url": "/api/reportes/lista/maxmin.php?m=1&tipo_u="+tipo,
                "data": {
                    "tipo":$('#tipo').val(),
                    "id_almacen":$('#id_almacen').val(),
                    "buscar":$('#buscador').val()
                },
            "type": "GET"
        },
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            // Bold the grade for all 'A' grade browsers
            $(nRow).children('td:eq(9)').html('');
            $(nRow).children('td:eq(9)').text('');
            $(nRow).children('td:eq(9)').append('<div class="'+aData[9]+' aa"></div>');
        }
    } );
  }


  function buscar2()
  {
    $('#example2').DataTable().destroy();
    function rowcolor(cellValue, options, rowObject) {
      if (cellValue == 0)
          rowsToColor[rowsToColor.length] = options.rowId;
      return cellValue;
    }
    var id_almacen=$('#id_almacen').val();
    var buscar=$('#buscador2').val();
    $('#example2').DataTable( {
        "processing": true,
        dom: 'Bfrtip',
        "bFilter": false,
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
            { "data": "descripcion_articulo" },
            { "data": "unidad" },
            { "data": "maximo" },
            { "data": "minimo" },
            { "data": "existencia" },
            { "data": "pedir" },
            { "data": "sta",formatter: rowcolor }
        ],
        "pageLength": 30,
        "ajax": {
            "url": "/api/reportes/lista/maxmin.php?m=2",
                "data": {
                    "id_almacen":$('#id_almacen').val(),
                    "buscar":$('#buscador2').val()
                },
            "type": "GET"
        },
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            // Bold the grade for all 'A' grade browsers
            $(nRow).children('td:eq(7)').html('');
            $(nRow).children('td:eq(7)').text('');
            $(nRow).children('td:eq(7)').append('<div class="'+aData[7]+' aa"></div>');
        }
    } );
  }

</script>

<style>
    .dt-buttons{
        display: none;
    }
</style>